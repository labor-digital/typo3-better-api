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
 * Last modified: 2020.04.14 at 13:02
 */

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class RefIndexRecordDataFilterEvent
 *
 * Emitted when the ref index class requests a record from the database to resolve the references for
 * This process is cached, so the event is only emitted once per call!
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class RefIndexRecordDataFilterEvent
{
    
    /**
     * The name of the table for which the data is required
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The unique id of the table the data is required
     *
     * @var int
     */
    protected $uid;
    
    /**
     * The raw data for the record to be filtered
     *
     * @var array
     */
    protected $row;
    
    /**
     * RefIndexRecordDataFilterEvent constructor.
     *
     * @param   string  $tableName
     * @param   int     $uid
     * @param   array   $row
     */
    public function __construct(string $tableName, int $uid, array $row)
    {
        $this->tableName = $tableName;
        $this->uid       = $uid;
        $this->row       = $row;
    }
    
    /**
     * Returns the name of the table for which the data is required
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the unique id of the table the data is required
     *
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }
    
    /**
     * Returns the raw data for the record to be filtered
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Updates the raw data for the record to be filtered
     *
     * @param   array  $row
     *
     * @return RefIndexRecordDataFilterEvent
     */
    public function setRow(array $row): RefIndexRecordDataFilterEvent
    {
        $this->row = $row;
        
        return $this;
    }
}
