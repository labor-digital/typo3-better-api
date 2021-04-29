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
 * Class DataHandlerActionPostProcessorEvent
 *
 * Is triggered when the Typo3 backend performs any kind of record operation.
 * Can be used to handle data after the record action was performed
 *
 * >>This does NOT include the saving of entries!<<
 *
 * @package LaborDigital\T3BA\Event\Events
 * @see     \LaborDigital\T3BA\Event\DataHandler\SavePostProcessorEvent
 */
class ActionPostProcessorEvent extends AbstractActionEvent
{
    /**
     * The new id of the record that is currently processed if it was copied
     *
     * @var int|string
     */
    protected $newId;
    
    /**
     * Contains the table data map when a record is copied and pasted to a new position
     *
     * @var mixed
     */
    protected $pasteDataMap;
    
    /**
     * DataHandlerActionFilterEvent constructor.
     *
     * @param   string                                    $command
     * @param   string                                    $tableName
     * @param                                             $id
     * @param                                             $newId
     * @param                                             $value
     * @param                                             $pasteSpecialData
     * @param                                             $pasteDataMap
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     */
    public function __construct(
        string $command,
        string $tableName,
        $id,
        $newId,
        $value,
        $pasteSpecialData,
        $pasteDataMap,
        DataHandler $dataHandler
    )
    {
        $this->command = $command;
        $this->tableName = $tableName;
        $this->id = $id;
        $this->value = $value;
        $this->pasteSpecialData = $pasteSpecialData;
        $this->dataHandler = $dataHandler;
        $this->newId = $newId;
        $this->pasteDataMap = $pasteDataMap;
    }
    
    /**
     * Returns the new id of the record that is currently processed if it was copied
     *
     * @return int|string
     */
    public function getNewId()
    {
        return $this->newId;
    }
    
    /**
     * Returns the table data map when a record is copied and pasted to a new position
     *
     * @return array|mixed
     */
    public function getPasteDataMap()
    {
        return $this->pasteDataMap;
    }
    
    /**
     * Allows you to update the table data map when a record is copied and pasted to a new position
     *
     * @param   mixed  $pasteDataMap
     *
     * @return $this
     */
    public function setPasteDataMap($pasteDataMap): self
    {
        $this->pasteDataMap = $pasteDataMap;
        
        return $this;
    }
}
