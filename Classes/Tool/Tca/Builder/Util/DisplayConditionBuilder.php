<?php
/*
 * Copyright 2021 LABOR.digital
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Last modified: 2021.10.26 at 15:53
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Tool\Tca\Builder\Util;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\FormEngine\Custom\DisplayCondition\CustomDisplayConditionInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractContainer;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\SingletonInterface;

class DisplayConditionBuilder implements NoDiInterface, SingletonInterface
{
    protected const TYPES = ['FIELD', 'HIDE_FOR_NON_ADMINS', 'REC', 'USER', 'VERSION'];
    protected const EVAL_TYPES = ['REQ', '>', '<', '>=', '<=', '-', '!-', '=', '!=', 'IN', '!IN', 'BIT', '!BIT'];
    
    /**
     * Receives the element and the user provided simple definition array.
     * It will parse the definition and convert it into a valid TYPO3 display condition array
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement  $el
     * @param   array                                                      $definition
     *
     * @return array|string
     */
    public function build(AbstractElement $el, array $definition)
    {
        $out = [];
        
        $_v = $this->buildSimpleSyntax($definition, $el);
        if ($_v !== null) {
            return implode(':', $_v);
        }
        
        foreach ($definition as $k => $v) {
            if (is_string($v)) {
                $out[$k] = $this->buildFromString($el, $v);
                continue;
            }
            
            if (is_array($v)) {
                if ($k === 'AND' || $k === 'OR') {
                    $out[$k] = $this->build($el, $v, true);
                    continue;
                }
                
                if (is_numeric($k) && Arrays::isArrayList($v)) {
                    $out[$k] = $this->build($el, $v);
                    continue;
                }
                
                if (Arrays::isAssociative($v)) {
                    $this->throwException($el, 'Nested display conditions can\'t be associative arrays!');
                }
                
                $_v = $this->buildSimpleSyntax($v, $el);
                if ($_v !== null) {
                    $out[$k] = implode(':', $_v);
                    continue;
                }
                
                $this->throwException($el, 'Invalid type in rule: "' . implode(':', $v) . '"');
            }
        }
        
        $outCount = count($out);
        // func_get_arg(2) if the parent list contains an "OR"/"AND", we don't need to wrap them in an "AND"
        if ($outCount > 1 && ! Arrays::isAssociative($out) && (func_num_args() !== 3 || ! func_get_arg(2))) {
            $out = ['AND' => $out];
        } elseif ($outCount === 1 && is_string(reset($out))) {
            return reset($out);
        }
        
        return $out;
    }
    
    /**
     * Helper to build a user condition from a class that uses the CustomDisplayConditionInterface
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement  $el
     * @param   string                                                     $condition
     *
     * @return string
     * @see CustomDisplayConditionInterface
     */
    public function buildFromString(AbstractElement $el, string $condition): string
    {
        return $this->buildCustomString($condition);
    }
    
    /**
     * Checks if the given condition is the class name of a CustomDisplayConditionInterface
     * implementation and automatically expands the syntax if required.
     *
     * @param   string  $condition
     *
     * @return string
     */
    protected function buildCustomString(string $condition): string
    {
        if (! str_contains($condition, '\\') || str_contains($condition, ':')) {
            return $condition;
        }
        if (! class_exists($condition)) {
            return $condition;
        }
        if (! in_array(CustomDisplayConditionInterface::class, class_implements($condition), true)) {
            return $condition;
        }
        
        return 'USER:' . $condition . '->evaluate';
    }
    
    /**
     * Internal helper to handle the "simple" syntax like ['field', '=', 'value'], or ['FIELD', 'field', '=', 'value']
     * and convert them to their string representation. This section can occur directly at root level,
     * or any other nested level when "OR" or "AND" are used
     *
     * @param   array                                                      $value
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement  $el
     *
     * @return array
     */
    protected function buildSimpleSyntax(array $value, AbstractElement $el): ?array
    {
        if (Arrays::isAssociative($value) || is_array(reset($value))) {
            return null;
        }
        
        $firstVal = strtoupper(trim((string)reset($value)));
        
        if (! in_array($firstVal, static::TYPES, true)) {
            $vCount = count($value);
            if (($vCount === 3 || $vCount === 2) && in_array(strtoupper(trim($value[1] ?? '')), static::EVAL_TYPES, true)) {
                $firstVal = 'FIELD';
                array_unshift($value, $firstVal);
            } else {
                return null;
            }
        }
        
        switch ($firstVal) {
            case 'FIELD':
                $evalType = $value[2] = strtoupper(trim($value[2] ?? ''));
                $evalValue = $value[3] ?? null;
                switch ($evalType) {
                    case 'IN':
                    case '!IN':
                        $value[3] = is_array($evalValue) ? implode(',', $evalValue) : ($evalValue ?? '');
                        break;
                    case '-':
                    case '!-':
                        $value[3] = is_array($evalValue) ? implode('-', array_slice($evalValue, 0, 2)) : ($value[3] ?? '');
                        break;
                    case 'REQ':
                        $value[3] = $evalValue ?? 'true';
                        break;
                }
                
                if (count($value) !== 4) {
                    $this->throwException($el,
                        'Invalid display condition: "' . implode(':', $value) .
                        '", an array for a "FIELD" type can have exactly 4 elements ONLY');
                }
                
                break;
            case 'REC':
                if (count($value) === 1) {
                    $value[] = 'new';
                }
                $value[1] = strtoupper((string)($value[1] ?? ''));
                break;
            case 'VERSION':
                $value[1] = strtoupper((string)($value[1] ?? ''));
                break;
        }
        
        return array_map(static function ($v) {
            if ($v === true) {
                return 'true';
            }
            if ($v === false) {
                return 'false';
            }
            
            return $v;
        }, $value);
    }
    
    /**
     * Internal helper to throw a somewhat descriptive exception
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement  $el
     * @param   string                                                     $message
     *
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     */
    protected function throwException(AbstractElement $el, string $message): void
    {
        $msg = 'Failed to build display condition for';
        if ($el instanceof AbstractField) {
            $msg .= ' field "' . $el->getId() . '"';
        } elseif ($el instanceof AbstractContainer) {
            $msg .= ' in section "' . $el->getId() . '"';
        }
        
        $msg .= ' on table "' . $el->getRoot()->getTableName() . '", because: ';
        
        throw new TcaBuilderException($msg . $message);
    }
}