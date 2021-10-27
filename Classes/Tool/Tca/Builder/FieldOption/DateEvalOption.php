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
 * Last modified: 2021.10.26 at 09:33
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Special variant of the eval option for the "date" input type
 */
class DateEvalOption extends EvalOption
{
    public function __construct()
    {
        parent::__construct([], []);
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $this->allowList = ['required', 'trim'];
        
        parent::addDefinition($definition);
        
        $definition['withTime'] = [
            'type' => 'bool',
            'default' => false,
        ];
        $definition['asInt'] = [
            'type' => 'bool',
            'default' => $this->context->getConfigFacet()->getConfigValue('tca.fieldPresetOptions.dateAsInt', true),
        ];
        
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        array_push($this->allowList, 'datetime', 'date', 'int');
        
        if ($options['asInt']) {
            $options['int'] = true;
        }
        
        $options[$options['withTime'] ? 'datetime' : 'date'] = true;
        
        parent::applyConfig($config, $options);
    }
    
}