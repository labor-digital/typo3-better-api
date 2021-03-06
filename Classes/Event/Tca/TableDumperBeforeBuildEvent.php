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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Event\Tca;

use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

/**
 * Class ExtConfigTableBeforeBuildEvent
 *
 * Emitted before the a table instance is converted into the tca array
 *
 * @package LaborDigital\T3ba\Event\Tca
 */
class TableDumperBeforeBuildEvent
{
    
    /**
     * The instance of the table that is being build
     *
     * @var TcaTable
     */
    protected $table;
    
    public function __construct(TcaTable $table)
    {
        $this->table = $table;
    }
    
    /**
     * Returns the name of the database table that is currently being build
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table->getTableName();
    }
    
    /**
     * Return the instance of the table that is being build
     *
     * @return TcaTable
     */
    public function getTable(): TcaTable
    {
        return $this->table;
    }
}
