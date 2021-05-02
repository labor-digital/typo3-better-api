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

namespace LaborDigital\T3ba\Event\Sql;

/**
 * Class CreateTableStatementFilterEvent
 *
 * Triggered when the TCA sql-table definition is generated
 *
 * @package LaborDigital\T3ba\Event\Tca
 */
class CreateTableStatementFilterEvent
{
    /**
     * The list of all table names that are included in the filtered statement
     *
     * @var array
     */
    protected $tableNames;
    
    /**
     * The definition that should be filtered
     *
     * @var string
     */
    protected $statement;
    
    /**
     * CreateTableStatementFilterEvent constructor.
     *
     * @param   array   $tableNames
     * @param   string  $statement
     */
    public function __construct(array $tableNames, string $statement)
    {
        $this->tableNames = $tableNames;
        $this->statement = $statement;
    }
    
    /**
     * Returns the list of all table names that are included in the filtered statement
     *
     * @return array
     */
    public function getTableNames(): array
    {
        return $this->tableNames;
    }
    
    /**
     * Returns the statement that should be filtered
     *
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }
    
    /**
     * Updates the statement that should be filtered
     *
     * @param   string  $statement
     *
     * @return $this
     */
    public function setStatement(string $statement): self
    {
        $this->statement = $statement;
        
        return $this;
    }
}
