<?php
/*
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\FormEngine;

/**
 * Class BackendFormNodeDataFilterEvent
 *
 * This event is emitted once for every backend form node, before it is created.
 * This is a last minute hook to modify the field configuration before the node factory
 * creates the renderer object
 *
 * @package LaborDigital\T3BA\Event\FormEngine
 */
class BackendFormNodeDataFilterEvent
{
    /**
     * The configuration data for the filtered form node
     *
     * @var array
     */
    protected $data;
    
    /**
     * BackendFormNodeDataFilterEvent constructor.
     *
     * @param   array  $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    
    /**
     * Returns the configuration data for the filtered form node
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Sets the configuration data for the filtered form node
     *
     * @param   array  $data
     *
     * @return BackendFormNodeDataFilterEvent
     */
    public function setData(array $data): BackendFormNodeDataFilterEvent
    {
        $this->data = $data;
        
        return $this;
    }
    
    /**
     * Returns the name of the node type we should filter
     *
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->data['parameterArray']['fieldConf']['config']['type'] ?? null;
    }
    
    /**
     * Returns the render type of the node we should filter
     *
     * @return string|null
     */
    public function getRenderType(): ?string
    {
        return $this->data['renderType'];
    }
}
