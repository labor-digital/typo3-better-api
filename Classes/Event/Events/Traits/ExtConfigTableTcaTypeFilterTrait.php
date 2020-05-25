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
 * Last modified: 2020.03.19 at 11:47
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\Traits;

use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;

trait ExtConfigTableTcaTypeFilterTrait
{
    /**
     * The array of the tca type that was build by the type itself
     * @var array
     */
    protected $typeTca;
    
    /**
     * The name of the tca type that is filtered
     * @var string
     */
    protected $type;
    
    /**
     * The name of the database table that has it's types filtered
     * @var string
     */
    protected $tableName;
    
    /**
     * The instance of the table that is currently build
     * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    protected $table;
    
    /**
     * ExtConfigTableRawTcaTypeFilterEvent constructor.
     *
     * @param array                                                       $typeTca
     * @param string                                                      $type
     * @param string                                                      $tableName
     * @param \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable $table
     */
    public function __construct(array $typeTca, string $type, string $tableName, TcaTable $table)
    {
        $this->typeTca = $typeTca;
        $this->type = $type;
        $this->tableName = $tableName;
        $this->table = $table;
    }
    
    /**
     * Returns the array of the tca type that was build by the type itself
     * @return array
     */
    public function getTypeTca(): array
    {
        return $this->typeTca;
    }
    
    /**
     * Updates the array of the tca type that was build by the type itself
     *
     * @param array $typeTca
     *
     * @return $this
     */
    public function setTypeTca(array $typeTca)
    {
        $this->typeTca = $typeTca;
        return $this;
    }
    
    /**
     * Returns the name of the tca type that is filtered
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * Returns the name of the database table that is currently being build
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Return the instance of the table that is being build
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    public function getTable(): TcaTable
    {
        return $this->table;
    }
}
