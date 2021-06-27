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


namespace LaborDigital\T3ba\Event\DataHandler;


use LaborDigital\T3ba\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3ba\Event\DataHandler\Adapter\SaveEventAdapter;
use TYPO3\CMS\Core\DataHandling\DataHandler;

abstract class AbstractLateSaveEvent implements CoreHookEventInterface
{
    /**
     * The status of the record either "new" or "update"
     *
     * @var string
     */
    protected $status;
    
    /**
     * The row that was given by to the data handler
     *
     * @var array
     */
    protected $row;
    
    /**
     * The name of the table that is currently saved
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The currently executed data handler instance
     *
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return SaveEventAdapter::class;
    }
    
    /**
     * AbstractLateSaveEvent constructor.
     *
     * @param   string                                    $status
     * @param   string                                    $tableName
     * @param   string|int                                $id
     * @param   array                                     $row
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
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
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
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
}
