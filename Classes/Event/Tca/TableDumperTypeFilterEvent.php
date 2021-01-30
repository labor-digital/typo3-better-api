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

namespace LaborDigital\T3BA\Event\Tca;


use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

/**
 * Class ExtConfigTableRawTcaTypeFilterEvent
 *
 * Dispatched when a tca table instance is converted into it's array form
 * Can be used to filter the raw type tca before it is merged with the table defaults
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class TableDumperTypeFilterEvent
{
    /**
     * The array of the tca type that was build by the type itself
     *
     * @var array
     */
    protected $typeTca;

    /**
     * The type instance which was used to generate the tca
     *
     * @var TcaTableType
     */
    protected $type;

    /**
     * The instance of the table that is currently build
     *
     * @var TcaTable
     */
    protected $table;

    /**
     * ExtConfigTableRawTcaTypeFilterEvent constructor.
     *
     * @param   array                                                        $typeTca
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType  $type
     * @param   TcaTable                                                     $table
     */
    public function __construct(array $typeTca, TcaTableType $type, TcaTable $table)
    {
        $this->typeTca = $typeTca;
        $this->type    = $type;
        $this->table   = $table;
    }

    /**
     * Returns the array of the tca type that was build by the type itself
     *
     * @return array
     */
    public function getTypeTca(): array
    {
        return $this->typeTca;
    }

    /**
     * Updates the array of the tca type that was build by the type itself
     *
     * @param   array  $typeTca
     *
     * @return $this
     */
    public function setTypeTca(array $typeTca): self
    {
        $this->typeTca = $typeTca;

        return $this;
    }

    /**
     * Returns the instance of the tca type that is filtered
     *
     * @return TcaTableType
     */
    public function getType(): TcaTableType
    {
        return $this->type;
    }

    /**
     * Returns the name/identifier of the type that gets filtered
     *
     * @return string|int
     */
    public function getTypeName()
    {
        return $this->type->getTypeName();
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
