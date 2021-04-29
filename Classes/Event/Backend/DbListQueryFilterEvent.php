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

namespace LaborDigital\T3BA\Event\Backend;

use LaborDigital\T3BA\Event\Backend\Adapter\DbListQueryFilterEventAdapter;
use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;
use TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList;

/**
 * Class DbListQueryFilterEvent
 *
 * This filter is used when the backend renders a list of database entries.
 * It can be used to append additional where clauses to the current request in the BackendRenderingService class
 *
 * @see     \LaborDigital\T3BA\Tool\Rendering\BackendRenderingService
 *
 * @package LaborDigital\T3BA\Event\Backend
 */
class DbListQueryFilterEvent implements CoreHookEventInterface
{
    /**
     * The name of the table to query the records from
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The current page id to query the records from
     *
     * @var int
     */
    protected $pid;
    
    /**
     * An additional where clause to narrow down the selected rows
     *
     * @var string
     */
    protected $additionalWhereClause;
    
    /**
     * The list of all database fields that should be queried for the table
     *
     * @var string
     */
    protected $selectedFieldList;
    
    /**
     * The list that is currently rendered
     *
     * @var \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
     */
    protected $listRenderer;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return DbListQueryFilterEventAdapter::class;
    }
    
    /**
     * BackendDbListQueryFilterEvent constructor.
     *
     * @param   string  $tableName
     * @param   int     $pid
     * @param   string  $additionalWhereClause
     * @param   string  $selectedFieldList
     * @param           $listRenderer
     */
    public function __construct(
        string $tableName,
        int $pid,
        string $additionalWhereClause,
        string $selectedFieldList,
        DatabaseRecordList $listRenderer
    )
    {
        $this->tableName = $tableName;
        $this->pid = $pid;
        $this->additionalWhereClause = $additionalWhereClause;
        $this->selectedFieldList = $selectedFieldList;
        $this->listRenderer = $listRenderer;
    }
    
    /**
     * Returns the name of the table to query the records from
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the current page id to query the records from
     *
     * @return int
     */
    public function getPid(): int
    {
        return $this->pid;
    }
    
    /**
     * Returns tn additional where clause to narrow down the selected rows
     *
     * @return string
     */
    public function getAdditionalWhereClause(): string
    {
        return $this->additionalWhereClause;
    }
    
    /**
     * Updates tn additional where clause to narrow down the selected rows
     *
     * @param   string  $additionalWhereClause
     *
     * @return $this
     */
    public function setAdditionalWhereClause(string $additionalWhereClause): self
    {
        $this->additionalWhereClause = $additionalWhereClause;
        
        return $this;
    }
    
    /**
     * Returns the list of all database fields that should be queried for the table
     *
     * @return string
     */
    public function getSelectedFieldList(): string
    {
        return $this->selectedFieldList;
    }
    
    /**
     * Updates the list of all database fields that should be queried for the table
     *
     * @param   string  $selectedFieldList
     *
     * @return $this
     */
    public function setSelectedFieldList(string $selectedFieldList): self
    {
        $this->selectedFieldList = $selectedFieldList;
        
        return $this;
    }
    
    /**
     * Returns the list that is currently rendered
     *
     * @return \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
     */
    public function getListRenderer(): DatabaseRecordList
    {
        return $this->listRenderer;
    }
    
    /**
     * Updates the list that is currently rendered
     *
     * @param   \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList  $listRenderer
     *
     * @return $this
     */
    public function setListRenderer(DatabaseRecordList $listRenderer): self
    {
        $this->listRenderer = $listRenderer;
        
        return $this;
    }
}
