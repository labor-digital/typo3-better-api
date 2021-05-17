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
 * Last modified: 2021.05.17 at 16:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Backend;


class LowLevelControllerConfigFilterEvent
{
    /**
     * A list of the collected data
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * The list of translation labels that should be overwritten with the given translation labels
     *
     * @var array
     */
    protected $labels = [];
    
    /**
     * Registers a new entry to the low level configuration controller
     *
     * @param   string  $id                A unique id to identify this data with
     * @param   array   $data              The data to be displayed when it was selected
     * @param   string  $translationLabel  A translation label to add to the dropdown list
     *
     * @return $this
     */
    public function addData(string $id, array $data, string $translationLabel): self
    {
        $uniqueId = 't3ba.lowLevel.' . $id;
        
        $this->labels[$uniqueId] = $translationLabel;
        
        $this->data[$id] = [
            'config' => [
                'label' => $uniqueId,
                'type' => 'global',
                'globalKey' => $uniqueId,
            ],
            'data' => $data,
        ];
        
        return $this;
    }
    
    /**
     * Returns the registered data to inject into the controller tree
     *
     * @return array
     * @internal This may change with an update of the lowLevel extension!
     */
    public function getRegisteredData(): array
    {
        return $this->data;
    }
    
    /**
     * Returns the generated language label overrides to be registered
     *
     * @return array
     * @internal This may change with an update of the lowLevel extension!
     */
    public function getRegisteredLabels(): array
    {
        return $this->labels;
    }
}