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
 * Last modified: 2021.10.25 at 13:30
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Adds an option to set if new records can be created with the new record wizard
 */
class WizardAllowNewOption extends AbstractOption
{
    /**
     * The default value to be set
     *
     * @var bool
     */
    protected $default;
    
    public function __construct(bool $default = false)
    {
        $this->default = $default;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['allowNew'] = [
            'type' => 'bool',
            'default' => $this->default,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (! $options['allowNew']) {
            return;
        }
        
        $config['fieldControl']['addRecord'] = [
            'disabled' => false,
            'options' => [
                'title' => 't3ba.formPreset.newRecord',
                'setValue' => 'append',
                'pid' => '###CURRENT_PID###',
            ],
        ];
        
    }
    
}