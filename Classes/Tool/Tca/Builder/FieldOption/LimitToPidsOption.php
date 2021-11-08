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

use Neunerlei\Arrays\Arrays;

/**
 * Applies an option called "limitToPids", which extends the "foreign_table_where" string with a list of configured pids
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
    
    public function __construct(string $foreignTableName, ?string $configName = null)
    {
        $this->foreignTableName = $foreignTableName;
        $this->configName = $configName ?? 'foreign_table_where';
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['limitToPids'] = [
            'type' => ['bool', 'int', 'string', 'array'],
            'default' => true,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options['limitToPids'])) {
            return;
        }
        
        $pids = $options['limitToPids'];
        if (is_string($pids)) {
            $pids = Arrays::makeFromStringList($pids);
        }
        
        $pidFacet = $this->context->getExtConfigContext()->getTypoContext()->pid();
        
        if (is_array($pids) && ! empty($pids)) {
            $pidSelector = ' IN(' . implode(',', $pidFacet->getMultiple($pids, 0)) . ')';
        } elseif (is_numeric($pids)) {
            $pidSelector = ' = ' . $pids;
        } elseif ($pids === true) {
            $pidSelector = ' = ###CURRENT_PID###';
        } else {
            return;
        }
        
        $config[$this->configName] .= ' AND ' . $this->foreignTableName . '.pid' . $pidSelector;
    }
    
}