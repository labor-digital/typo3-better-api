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


namespace LaborDigital\T3BA\Tool\Sql;


use Doctrine\DBAL\Schema\Table;

class Definition
{
    /**
     * All loaded tables
     *
     * @var \Doctrine\DBAL\Schema\Table[]
     */
    public $tables = [];
    
    /**
     * The list of tables that have been created by the sql registry
     *
     * @var \Doctrine\DBAL\Schema\Table[][]
     */
    public $newTableNames = [];
    
    /**
     * A list of loaded subtypes (table-clones) for all loaded types
     *
     * @var \Doctrine\DBAL\Schema\Table[]
     */
    public $types = [];
    
    public function __construct(array $tables)
    {
        $this->tables = $tables;
    }
    
    /**
     * Returns true if the given table is considered "new"
     *
     * @param   \Doctrine\DBAL\Schema\Table  $table
     *
     * @return bool
     */
    public function isNew(Table $table): bool
    {
        return in_array($table->getName(), $this->newTableNames, true);
    }
    
    /**
     * Returns true if the given table should be dumped into an sql string
     *
     * @param   \Doctrine\DBAL\Schema\Table  $table
     *
     * @return bool
     */
    public function isDumpable(Table $table): bool
    {
        return isset($this->types[$table->getName()]) || $this->isNew($table);
    }
}
