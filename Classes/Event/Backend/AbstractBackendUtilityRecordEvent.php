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


abstract class AbstractBackendUtilityRecordEvent
{
    /**
     * Table name present in $GLOBALS['TCA']
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The unique id of the record which is retrieved
     *
     * @var int
     */
    protected $uid;
    
    /**
     * A comma separated list of fields/columns that should be resolved
     *
     * @var string
     */
    protected $fieldsList;
    
    /**
     * Additional WHERE clause, eg. ' AND some_field = 0'
     *
     * @var string
     */
    protected $where;
    
    /**
     * Use the deleteClause to check if a record is deleted (default TRUE)
     *
     * @var bool
     */
    protected $useDeleteClause;
    
    public function __construct(
        string $tableName,
        int $uid,
        string $fields,
        string $where,
        bool $useDeleteClause
    )
    {
        $this->tableName = $tableName;
        $this->uid = $uid;
        $this->fieldsList = $fields;
        $this->where = $where;
        $this->useDeleteClause = $useDeleteClause;
    }
    
    /**
     * Returns table name present in $GLOBALS['TCA']
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the unique id of the record which is retrieved
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->uid;
    }
    
    /**
     * Returns a comma separated list of fields/columns that should be resolved
     *
     * @return string
     */
    public function getFieldList(): string
    {
        return $this->fieldsList;
    }
    
    /**
     * Returns an optional additional WHERE clause, eg. ' AND some_field = 0'
     *
     * @return string
     */
    public function getWhere(): string
    {
        return $this->where;
    }
    
    /**
     * Returns the deleteClause state to check if a record is deleted (default TRUE)
     *
     * @return bool
     */
    public function isUseDeleteClause(): bool
    {
        return $this->useDeleteClause;
    }
}
