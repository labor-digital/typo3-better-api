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
 * Last modified: 2020.03.19 at 11:50
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;

/**
 * Class ExtConfigTableBeforeBuildEvent
 *
 * Emitted before the a table instance is converted into the tca array
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ExtConfigTableBeforeBuildEvent
{
    
    /**
     * The name of the database table that is currently being build
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The instance of the table that is being build
     *
     * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    protected $table;
    
    /**
     * ExtConfigTableBeforeBuildEvent constructor.
     *
     * @param   string                                                       $tableName
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable  $table
     */
    public function __construct(string $tableName, TcaTable $table)
    {
        $this->tableName = $tableName;
        $this->table     = $table;
    }
    
    /**
     * Returns the name of the database table that is currently being build
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Return the instance of the table that is being build
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    public function getTable(): TcaTable
    {
        return $this->table;
    }
}
