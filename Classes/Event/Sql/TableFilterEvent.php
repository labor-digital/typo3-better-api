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
 * Last modified: 2021.02.08 at 19:51
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\Sql;


use Doctrine\DBAL\Schema\Table;

/**
 * Class TableFilterEvent
 *
 * Emitted when the SQL definition for the tables.
 * It allows you to perform last-minute changes to the table object before
 * it is converted into an SQL string.
 *
 * The event is emitted once per table.
 *
 * The diff of $tableOld and $tableNew will be dumped as SQL string.
 * In a normal world you would only want to edit $tableNew, but I give you $tableOld as well,
 * so you can use it if required.
 *
 * @package LaborDigital\T3BA\Event\Sql
 */
class TableFilterEvent
{
    /**
     * The name of the table that should be filtered
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The table schema before the changes were applied
     *
     * @var \Doctrine\DBAL\Schema\Table
     */
    protected $initialTable;
    
    /**
     * The table schema that will be dumped into an SQL string
     *
     * @var \Doctrine\DBAL\Schema\Table|null
     */
    protected $tableToDump;
    
    /**
     * TableFilterEvent constructor.
     *
     * @param   string                       $tableName
     * @param   \Doctrine\DBAL\Schema\Table  $initialTable
     * @param   \Doctrine\DBAL\Schema\Table  $tableToDump
     */
    public function __construct(string $tableName, Table $initialTable, ?Table $tableToDump)
    {
        $this->tableName = $tableName;
        $this->initialTable = $initialTable;
        $this->tableToDump = $tableToDump;
    }
    
    /**
     * Returns the name of the table the definition is generated for
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the table schema before the changes were applied
     * You should normally not edit this.
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getInitialTable(): Table
    {
        return $this->initialTable;
    }
    
    /**
     * Returns the table schema that will be dumped into an SQL string
     * You should edit this
     *
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    public function getTableToDump(): ?Table
    {
        return $this->tableToDump;
    }
    
    /**
     * Allows you to completely override the table schema that will be dumped into an SQL string
     *
     * @param   \Doctrine\DBAL\Schema\Table|null  $tableToDump
     *
     * @return TableFilterEvent
     */
    public function setTableToDump(?Table $tableToDump): TableFilterEvent
    {
        $this->tableToDump = $tableToDump;
        
        return $this;
    }
}
