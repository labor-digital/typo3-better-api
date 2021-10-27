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
 * Last modified: 2021.10.25 at 14:03
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Provides the option definition for the minItems and maxItems options on fields
 */
class MinMaxItemOption extends AbstractOption
{
    /**
     * The default value of the maximal item count
     *
     * @var int
     */
    protected $defaultMax;
    
    /**
     * The default value for the minimal item count
     *
     * @var int
     */
    protected $defaultMin;
    
    public function __construct(int $defaultMax = 999, int $defaultMin = 0)
    {
        $this->defaultMax = $defaultMax;
        $this->defaultMin = $defaultMin;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['maxItems'] = [
            'type' => 'int',
            'default' => $this->defaultMax,
        ];
        
        $definition['minItems'] = [
            'type' => 'int',
            'default' => $this->defaultMin,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        // If the field is required -> minItems is 1
        if ($options['required'] === true) {
            $options['minItems'] = max($options['minItems'], 1);
        }
        
        $config['maxitems'] = $options['maxItems'];
        $config['minitems'] = $options['minItems'];
    }
    
}