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


namespace LaborDigital\T3BA\Event\DataHandler;


use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerDefaultFilterEvent
 *
 * Allows you to filter the list of fields that have defaults applied to them
 * when a new record gets created.
 *
 * @package LaborDigital\T3BA\Event\DataHandler
 */
class DataHandlerDefaultFilterEvent
{
    
    /**
     * The name of the table that gets the defaults created
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The raw field array that contains the prepared defaults
     *
     * @var array
     */
    protected $row;
    
    /**
     * The currently executed data handler instance
     *
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * DataHandlerDefaultFilterEvent constructor.
     *
     * @param   string                                    $tableName
     * @param   array                                     $row
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     */
    public function __construct(string $tableName, array $row, DataHandler $dataHandler)
    {
        $this->tableName = $tableName;
        $this->row = $row;
        $this->dataHandler = $dataHandler;
    }
    
    /**
     * Returns the name of the table that gets the defaults created
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the currently executed data handler instance
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
    
    /**
     * Returns the raw field array that contains the prepared defaults
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Used to update the raw field array that contains the prepared defaults
     *
     * @param   array  $row
     *
     * @return DataHandlerDefaultFilterEvent
     */
    public function setRow(array $row): self
    {
        $this->row = $row;
        
        return $this;
    }
}
