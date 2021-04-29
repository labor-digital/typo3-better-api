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
 * Last modified: 2021.04.28 at 12:48
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\ExtConfig;

use LaborDigital\T3BA\ExtConfig\ExtConfigContext;

/**
 * Class FieldPresetFilterEvent
 *
 * Executed when the list of field presets is loaded in the ext config,
 * allows you to programmatically register or filter the list before it is persisted in the configuration.
 *
 * @package LaborDigital\T3BA\Event\ExtConfig
 */
class FieldPresetFilterEvent
{
    /**
     * The resolved list of field presets as an array like: ["presetName" => ["className", "methodName"]]
     *
     * @var array
     */
    protected $presets;
    
    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    public function __construct(array $presets, ExtConfigContext $context)
    {
        $this->presets = $presets;
        $this->context = $context;
    }
    
    /**
     * Returns the resolved list of field presets as an array like: ["presetName" => ["className", "methodName"]]
     *
     * @return array
     */
    public function getPresets(): array
    {
        return $this->presets;
    }
    
    /**
     * Allows you to override the resolved list of field presets as an array like: ["presetName" => ["className",
     * "methodName"]]
     *
     * @param   array  $presets
     *
     * @return FieldPresetFilterEvent
     */
    public function setPresets(array $presets): FieldPresetFilterEvent
    {
        $this->presets = $presets;
        
        return $this;
    }
    
    /**
     * Allows you to add a new preset to the list
     *
     * @param   string  $presetName
     * @param   string  $className
     * @param   string  $methodName
     *
     * @return $this
     */
    public function addPreset(string $presetName, string $className, string $methodName): self
    {
        $this->presets[$presetName] = [$className, $methodName];
        
        return $this;
    }
    
    /**
     * Returns the currently active ext config context instance
     *
     * @return \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        return $this->context;
    }
}
