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
 * Last modified: 2021.10.26 at 11:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Not really an option, but allows parent presets to pass along their definition to child presets.
 * Simply add this option to the outer prepareOptions() call, and the child will automatically inherit
 * the outer definition.
 *
 * NOTE: Keep in mind, to use the ['allowUnknown' => true] option in the parent preset, so the child
 * options will silently go through
 *
 * @see \LaborDigital\T3ba\FormEngine\FieldPreset\Relations::applyRelationImage()
 */
class InheritParentDefinitionOption extends AbstractOption
{
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition[Container::INHERIT_DEFINITION_KEY] = [
            'type' => 'object',
            'default' => new \stdClass(),
            'filter' => function () use (&$definition) {
                return new class($definition) {
                    protected $definition;
                    
                    public function __construct(array &$definition)
                    {
                        $this->definition = &$definition;
                    }
                    
                    public function getDefinition(): array
                    {
                        return $this->definition;
                    }
                };
            },
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void { }
    
}