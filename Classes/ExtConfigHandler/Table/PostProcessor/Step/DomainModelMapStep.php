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
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\StateAwareTcaPostProcessorTrait;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessorStepInterface;
use Neunerlei\Configuration\State\ConfigState;

/**
 * Class DomainModelMapStep
 *
 * Generates the extbase persistence map based on the current TCA configuration
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step
 */
class DomainModelMapStep implements TcaPostProcessorStepInterface, StateAwareTcaPostProcessorInterface, NoDiInterface
{
    public const CONFIG_KEY = 'domainModelClasses';
    
    protected $classNameMap = [];
    protected $extBasePersistenceMap = [];
    
    /**
     * @inheritDoc
     */
    public function process(string $tableName, array &$config, array &$meta): void
    {
        if (! isset($config['ctrl'][static::CONFIG_KEY]) || ! is_array($config['ctrl'][static::CONFIG_KEY])) {
            return;
        }
        
        foreach ($config['ctrl'][static::CONFIG_KEY] as $className => $columns) {
            $definition = [
                'tableName' => $tableName,
                'properties' => [],
            ];
            
            foreach ($columns as $field => $property) {
                $definition['properties'][$property]['fieldName'] = $field;
            }
            
            $this->extBasePersistenceMap[$className] = $definition;
            $this->classNameMap[$className] = $tableName;
            
            // @todo remove this in the next major release
            $meta['extbase']['persistence'][$className] = $definition;
            $meta['classNameMap'][$className] = $tableName;
        }
        
        unset($config['ctrl'][static::CONFIG_KEY]);
    }
    
    public function applyToConfigState(ConfigState $state): void
    {
        $state->set('typo.extBase.persistence', $this->extBasePersistenceMap);
        $state->mergeIntoArray('tca.classNameMap', $this->classNameMap);
    }
    
    
}
