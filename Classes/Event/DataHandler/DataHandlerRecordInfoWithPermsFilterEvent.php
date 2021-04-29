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
 * Last modified: 2021.04.26 at 19:09
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\DataHandler;

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerRecordInfoWithPermsFilterEvent
 *
 * Similar to DataHandlerRecordInfoFilterEvent, but works on the recordInfoWithPermissionCheck() method
 * instead of the recordInfo method. This is allows you to modify the data retrieved by the datahandler.
 *
 * @package LaborDigital\T3BA\Event\DataHandler
 */
class DataHandlerRecordInfoWithPermsFilterEvent
{
    /**
     * The name of the table to request the information from
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * UID of the record from $tableName or NEW... for a new record
     *
     * @var int|string
     */
    protected $id;
    
    /**
     * The field list for the SELECT query, eg. "*" or "uid,pid,...
     *
     * @var string
     */
    protected $fieldList;
    
    /**
     * The currently executed data handler instance
     *
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * The row that was resolved by the parent method, or false if it failed
     *
     * @var array|false
     */
    protected $result;
    
    /**
     * Permission restrictions to observe: Either an integer that will be bitwise AND'ed or a string, which points to a
     * key in the ->pMap array. With TYPO3 v11, only integers are allowed
     *
     * @var int|string
     */
    protected $perms;
    
    /**
     * DataHandlerRecordInfoFilterEvent constructor.
     *
     * @param   string                                    $tableName
     * @param   int|string                                $id
     * @param   string                                    $fieldList
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     * @param                                             $result
     * @param                                             $perms
     */
    public function __construct(
        string $tableName,
        $id,
        string $fieldList,
        DataHandler $dataHandler,
        $result,
        $perms
    )
    {
        $this->tableName = $tableName;
        $this->id = $id;
        $this->fieldList = $fieldList;
        $this->dataHandler = $dataHandler;
        $this->result = $result;
        $this->perms = $perms;
    }
    
    /**
     * Returns the id of the entry that is requested
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns the name of the table that is currently requested
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
     * Returns the field list for the SELECT query, eg. "*" or "uid,pid,...
     *
     * @return string
     */
    public function getFieldList(): string
    {
        return $this->fieldList;
    }
    
    /**
     * Returns the result that was resolved for the record info
     *
     * @return array|false
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * Allows you to update the result to return back to the datahandler
     *
     * @param   array|false  $result
     *
     * @return DataHandlerRecordInfoWithPermsFilterEvent
     */
    public function setResult($result)
    {
        $this->result = $result;
        
        return $this;
    }
    
}
