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
 * Last modified: 2021.10.25 at 12:31
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Adds the configuration for a "placeholder" value of the field
 */
class PlaceholderOption extends AbstractOption
{
    /**
     * Optional default placeholder value
     *
     * @var string|null
     */
    protected $defaultPlaceholder;
    
    public function __construct(?string $defaultPlaceholder = null)
    {
        $this->defaultPlaceholder = $defaultPlaceholder;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['placeholder'] = [
            'type' => ['string', 'null'],
            'default' => $this->defaultPlaceholder,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options['placeholder'])) {
            return;
        }
        
        $config['placeholder'] = $options['placeholder'];
    }
    
}