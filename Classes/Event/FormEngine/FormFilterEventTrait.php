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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\FormEngine;

trait FormFilterEventTrait
{
    /**
     * The name of the table that the current form applies to
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The data to be filtered
     *
     * @var array
     */
    protected $data;
    
    /**
     * BackendFormFilterEvent constructor.
     *
     * @param   string  $tableName
     * @param   array   $data
     */
    public function __construct(string $tableName, array $data)
    {
        $this->tableName = $tableName;
        $this->data = $data;
    }
    
    /**
     * Returns the name of the table that the current form applies to
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    
    /**
     * Returns the data to be filtered
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Updates the data to be filtered
     *
     * @param   array  $data
     *
     * @return object
     */
    public function setData(array $data)
    {
        $this->data = $data;
        
        return $this;
    }
}
