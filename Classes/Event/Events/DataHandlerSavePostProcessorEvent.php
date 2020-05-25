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
 * Last modified: 2020.03.19 at 12:57
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\DataHandlerSaveFilterEventAdapter;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerSavePostProcessorEvent
 *
 * Is triggered when the Typo3 backend saves any kind of record to the database using the data handler.
 * Can be used to handle data after the record was stored in the database
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class DataHandlerSavePostProcessorEvent implements CoreHookEventInterface
{
    /**
     * The status of the record either "new" or "update"
     * @var string
     */
    protected $status;
    
    /**
     * The row that was given by to the data handler
     * @var array
     */
    protected $row;
    
    /**
     * The name of the table that is currently saved
     * @var string
     */
    protected $tableName;
    
    /**
     * The id of the entry that is saved.
     * May be the numeric id or a string with "NEW_..." at the beginning
     * @var string|int
     */
    protected $id;
    
    /**
     * The currently executed data handler instance
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return DataHandlerSaveFilterEventAdapter::class;
    }
    
    /**
     * DataHandlerSavePostProcessorEvent constructor.
     *
     * @param string                                   $status
     * @param string                                   $tableName
     * @param                                          $id
     * @param array                                    $row
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function __construct(string $status, string $tableName, $id, array $row, DataHandler $dataHandler)
    {
        $this->status = $status;
        $this->tableName = $tableName;
        $this->id = $id;
        $this->row = $row;
        $this->dataHandler = $dataHandler;
    }
    
    /**
     * Returns the status of the record either "new" or "update"
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }
    
    /**
     * Returns the row that was given by to the data handler
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Updates the row that was given by to the data handler
     *
     * @param array $row
     *
     * @return DataHandlerSavePostProcessorEvent
     */
    public function setRow(array $row): DataHandlerSavePostProcessorEvent
    {
        $this->row = $row;
        return $this;
    }
    
    /**
     * Returns the id of the entry that is saved
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Updates the id of the entry that is saved.
     *
     * @param int|string $id
     *
     * @return DataHandlerSavePostProcessorEvent
     */
    public function setId($id): DataHandlerSavePostProcessorEvent
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * Returns the name of the table that is currently saved
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the currently executed data handler instance
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
}
