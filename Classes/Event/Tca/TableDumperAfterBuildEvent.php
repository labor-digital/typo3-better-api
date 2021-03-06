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
 * Class ExtConfigTableAfterBuildEvent
 *
 * Dispatched after a table instance was converted into it's array form.
 * Can be used to apply last-minute changes to the generated tca before it is cached
 *
 * @package LaborDigital\T3ba\Event\Tca
 */
class TableDumperAfterBuildEvent
{
    /**
     * The generated tca array for the table
     *
     * @var array
     */
    protected $tca;
    
    /**
     * The instance of the table that is being build
     *
     * @var TcaTable
     */
    protected $table;
    
    public function __construct(array $tca, TcaTable $table)
    {
        $this->tca = $tca;
        $this->table = $table;
    }
    
    /**
     * Returns the generated tca array for the table
     *
     * @return array
     */
    public function getTca(): array
    {
        return $this->tca;
    }
    
    /**
     * Updates the generated tca array for the table
     *
     * @param   array  $tca
     *
     * @return $this
     */
    public function setTca(array $tca): self
    {
        $this->tca = $tca;
        
        return $this;
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
