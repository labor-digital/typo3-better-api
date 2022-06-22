<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.06.22 at 09:15
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\DataHandler;


class DataHandlerCompareFieldArrayFilterEvent
{
    /**
     * The name of the table to compare the field array for
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
     * The provided list of fields to compare against the current values
     *
     * @var array
     */
    protected $fieldArray;
    
    /**
     * The actual comparator, used to generate the diff between the given and current state
     *
     * @var \Closure
     */
    protected $concreteComparator;
    
    public function __construct(string $tableName, $id, array $fieldArray, \Closure $concreteComparator)
    {
        $this->tableName = $tableName;
        $this->id = $id;
        $this->fieldArray = $fieldArray;
        $this->concreteComparator = $concreteComparator;
    }
    
    /**
     * Returns the name of the table to compare the field array for
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the UID of the record from $tableName or NEW... for a new record
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns the provided list of fields to compare against the current values
     *
     * @return array
     */
    public function getFieldArray(): array
    {
        return $this->fieldArray;
    }
    
    /**
     * Returns the actual comparator, used to generate the diff between the given and current state
     *
     * @return \Closure
     */
    public function getConcreteComparator(): \Closure
    {
        return $this->concreteComparator;
    }
    
    /**
     * Allows the outside world to update the actual comparator,
     * used to generate the diff between the given and current state
     *
     * @param   \Closure  $concreteComparator
     *
     * @return DataHandlerCompareFieldArrayFilterEvent
     * @see \TYPO3\CMS\Core\DataHandling\DataHandler::compareFieldArrayWithCurrentAndUnset for the implementation details
     */
    public function setConcreteComparator(\Closure $concreteComparator): DataHandlerCompareFieldArrayFilterEvent
    {
        $this->concreteComparator = $concreteComparator;
        
        return $this;
    }
}