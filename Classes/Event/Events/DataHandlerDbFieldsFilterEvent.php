<?php
/**
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
 * Last modified: 2020.03.19 at 13:00
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerDbFieldsFilterEvent
 *
 * Emitted when the data handler writes or updates a row into the database
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class DataHandlerDbFieldsFilterEvent
{
    
    /**
     * The method that is used to write into the database. Can be "insert" or "update".
     *
     * @var string
     */
    protected $method;
    
    /**
     * The name of the table that is currently saved
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The prepared database row to write into the database
     *
     * @var array
     */
    protected $row;
    
    /**
     * The id of the entry that is saved.
     * May be the numeric id or a string with "NEW_..." at the beginning
     *
     * @var string|int
     */
    protected $id;
    
    /**
     * The currently executed data handler instance
     *
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * Can contain additional information depending on the given method
     *
     * @var array
     */
    protected $additionalData;
    
    /**
     * DataHandlerDbFieldsFilterEvent constructor.
     *
     * @param   string                                    $method
     * @param   string                                    $tableName
     * @param   array                                     $row
     * @param   string|int                                $id
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     * @param   array                                     $additionalData
     */
    public function __construct(
        string $method,
        string $tableName,
        array $row,
        $id,
        DataHandler $dataHandler,
        array $additionalData = []
    ) {
        $this->method         = $method;
        $this->tableName      = $tableName;
        $this->row            = $row;
        $this->id             = $id;
        $this->dataHandler    = $dataHandler;
        $this->additionalData = $additionalData;
    }
    
    /**
     * Returns the method that is used to write into the database. Can be "insert" or "update".
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
    
    /**
     * Returns additional information depending on the given method
     *
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }
    
    /**
     * Returns the name of the table that is currently saved
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
     * Returns the row that was given by to the data handler
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Updates the row that was given by to the data handler
     *
     * @param   array  $row
     *
     * @return DataHandlerDbFieldsFilterEvent
     */
    public function setRow(array $row): DataHandlerDbFieldsFilterEvent
    {
        $this->row = $row;
        
        return $this;
    }
    
    /**
     * Returns the id of the entry that is saved
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Updates the id of the entry that is saved.
     *
     * @param   int|string  $id
     *
     * @return DataHandlerDbFieldsFilterEvent
     */
    public function setId($id): DataHandlerDbFieldsFilterEvent
    {
        $this->id = $id;
        
        return $this;
    }
}
