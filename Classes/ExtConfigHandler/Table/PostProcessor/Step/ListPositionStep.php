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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\StateAwareTcaPostProcessorInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\StepTsConfigHelperTrait;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessorStepInterface;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;

/**
 * Class ListPositionStep
 *
 * Generates the ts config script for the backend list ordering
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step
 */
class ListPositionStep implements TcaPostProcessorStepInterface, StateAwareTcaPostProcessorInterface, NoDiInterface
{
    use StepTsConfigHelperTrait;
    
    public const CONFIG_KEY = 'listPosition';
    public const TS_IMPORT_PATH = 'tca.table.listPosition';
    
    /**
     * @inheritDoc
     */
    public function process(string $tableName, array &$config, array &$meta): void
    {
        if (! isset($config['ctrl'][static::CONFIG_KEY]) || ! is_array($config['ctrl'][static::CONFIG_KEY])) {
            return;
        }
        
        $this->ts[] = $this->buildOrderTsConfigString($tableName, $config['ctrl'][static::CONFIG_KEY]);
        
        // @todo remove this in the next major version
        $meta['tsConfig'] = $meta['tsConfig'] ?? '';
        
        unset($config['ctrl'][static::CONFIG_KEY]);
    }
    
    public function applyToConfigState(ConfigState $state): void
    {
        $this->addTsConfigToState($state, 'tca.listPosition');
    }
    
    
    /**
     * Internal helper to build the ts config for the table list order
     *
     * @param   string  $tableName
     * @param   array   $order
     *
     * @return string
     */
    protected function buildOrderTsConfigString(string $tableName, array $order): string
    {
        $c = Arrays::merge(['before' => [], 'after' => []], $order);
        
        $ts = [];
        $ts[] = 'mod.web_list.tableDisplayOrder.' . $tableName . ' {';
        
        foreach (['before', 'after'] as $key) {
            if (! empty($c[$key])) {
                $c[$key] = array_unique($c[$key]);
                $ts[] = $key . ' = ' . implode(', ', array_unique($c[$key]));
            }
        }
        
        $ts[] = '}';
        
        return implode(PHP_EOL, $ts);
    }
    
}
