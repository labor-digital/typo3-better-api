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
     * @return array
     */
    public function build(AbstractElement $el, array $definition): array
    {
        $out = [];
        
        foreach ($definition as $k => $v) {
            if (is_string($v)) {
                $out[$k] = $v;
                continue;
            }
            
            if (is_array($v)) {
                if ($k === 'AND' || $k === 'OR') {
                    $out[$k] = $this->build($el, $v);
                    continue;
                }
                
                if (is_numeric($k) && Arrays::isArrayList($v)) {
                    $out[$k] = ['AND' => $this->build($el, $v)];
                    continue;
                }
                
                if (Arrays::isAssociative($v)) {
                    $this->throwException($el, 'Nested display conditions can\'t be associative arrays!');
                }
                
                if (count($v) === 3 && in_array(strtoupper(trim($v[1])), static::EVAL_TYPES, true)) {
                    if ($v[0] === 'FIELD') {
                        $this->throwException($el, 'Invalid display condition, an array with three parts can\'t start with "FIELD"');
                    }
                    
                    array_unshift($v, 'FIELD');
                }
                
                if (! in_array(strtoupper(trim($v[0])), static::TYPES, true)) {
                    $this->throwException($el, 'Invalid type in rule: ' . implode(':', $v));
                }
                
                $out[$k] = implode(':', $v);
            }
        }
        
        return $out;
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