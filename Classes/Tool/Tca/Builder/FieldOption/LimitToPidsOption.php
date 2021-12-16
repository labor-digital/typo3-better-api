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
 * Last modified: 2021.10.26 at 09:56
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;

/**
 * Applies an option called "limitToPids", which extends the "foreign_table_where" string with a list of configured pids
 *
 * @see \LaborDigital\T3ba\FormEngine\Addon\PidInWhereClauseResolver
 */
class LimitToPidsOption extends AbstractOption
{
    /**
     * The absolute name of the foreign table to narrow down the pids for
     *
     * @var string
     */
    protected $foreignTableName;
    
    /**
     * The name of the config field that should be extended. Default: foreign_table_where
     *
     * @var string
     */
    protected $configName;
    
    /**
     * The name of the option to find the item definition on
     *
     * @var string
     */
    protected $optionName;
    
    /**
     * The default value for the main option
     *
     * @var mixed
     */
    protected $defaultValue;
    
    /**
     * The default value for the recursive option
     *
     * @var mixed
     */
    protected $defaultValueRecursive;
    
    /**
     * The default value for the recursive-depth option
     *
     * @var mixed
     */
    protected $defaultValueRecursiveDepth;
    
    public function __construct(string $foreignTableName, ?string $configName = null, array $options = [])
    {
        $this->foreignTableName = $foreignTableName;
        $this->configName = $configName ?? 'foreign_table_where';
        $this->optionName = $options['optionName'] ?? 'limitToPids';
        $this->defaultValue = $options['defaultValue'] ?? true;
        $this->defaultValueRecursive = $options['defaultValueRecursive'] ?? false;
        $this->defaultValueRecursiveDepth = $options['defaultValueRecursiveDepth'] ?? 10;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition[$this->optionName] = [
            'type' => ['bool', 'int', 'string', 'array'],
            'default' => $this->defaultValue,
        ];
        $definition[$this->optionName . 'Recursive'] = [
            'type' => 'bool',
            'default' => $this->defaultValueRecursive,
        ];
        $definition[$this->optionName . 'RecursiveDepth'] = [
            'type' => 'int',
            'default' => $this->defaultValueRecursiveDepth,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options[$this->optionName])) {
            return;
        }
        
        $pids = $options[$this->optionName];
        if (is_string($pids)) {
            $pids = Arrays::makeFromStringList($pids);
        }
        
        $operator = '= %s';
        
        if (is_array($pids) && ! empty($pids)) {
            $operator = 'IN (%s)';
            $selector = '###PID_LIST(' . implode(',', $pids) . ')###';
        } elseif (is_numeric($pids)) {
            $selector = (string)$pids;
        } elseif ($pids === true) {
            $selector = '###CURRENT_PID###';
        } else {
            return;
        }
        
        if ($options[$this->optionName . 'Recursive'] ?? false) {
            $depth = $options[$this->optionName . 'RecursiveDepth'];
            // The operator must always expect an array
            $operator = 'IN (%s)';
            $selector = '###PIDS_RECURSIVE(' . $depth . '|' . $selector . ')###';
        }
        
        $config[$this->configName] .= ' AND ' .
                                      NamingUtil::resolveTableName($this->foreignTableName) .
                                      '.pid ' . sprintf($operator, $selector);
    }
    
}