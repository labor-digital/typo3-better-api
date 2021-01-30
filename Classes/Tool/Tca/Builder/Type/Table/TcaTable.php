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
 * Last modified: 2021.01.13 at 19:16
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table;


use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\TcaTableConfigTrait;

class TcaTable extends AbstractTcaTable
{
    use TcaTableConfigTrait;

    /**
     * Contains the list of all instantiated tca types of this table
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     *
     * @var TcaTableType[]
     */
    protected $types = [];

    /**
     * Holds the type key this instance represents (Default Type)
     *
     * @var string|int
     */
    protected $typeName;

    /**
     * True if this table should have sortable entries in the backend
     *
     * @var bool
     */
    protected $sortable = false;

    /**
     * Holds the initial state of the TCA when this table was initialized.
     * This allows children to access the raw data directly
     *
     * @var array
     */
    protected $initialConfig = [];

    /**
     * @inheritDoc
     */
    public function __construct(
        string $tableName,
        TcaBuilderContext $context,
        TableFactory $tableFactory,
        TypeFactory $typeFactory
    ) {
        parent::__construct($tableName, $context, $tableFactory, $typeFactory, $this);
        $this->initializer = $tableFactory;
    }

    /**
     * Returns the instance of a certain tca type.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     *
     * @param   string|int  $typeName
     *
     * @return TcaTableType
     */
    public function getType($typeName): TcaTableType
    {
        if (isset($this->types[$typeName]) && $this->types[$typeName] instanceof TcaTableType) {
            return $this->types[$typeName];
        }

        $type    = $this->typeFactory->create($typeName, $this);
        $typeTca = $this->typeFactory->getTypeTca($typeName, $this);
        $this->typeFactory->initialize($typeTca, $type);

        return $this->types[$typeName] = $type;
    }

    /**
     * Returns the list of all type names that are currently registered (both loaded and defined)
     *
     * @return array
     */
    public function getTypeNames(): array
    {
        $typeKeys = array_keys($this->getInitialConfig()['types'] ?? []);
        $typeKeys = array_merge($typeKeys, array_keys($this->types));

        return array_unique($typeKeys);
    }

    /**
     * Returns true if the given type name is currently registered (either loaded, or defined)
     *
     * @param $typeName
     *
     * @return bool
     */
    public function hasType($typeName): bool
    {
        return $this->isTypeLoaded($typeName) || in_array($typeName, $this->getTypeNames(), false);
    }

    /**
     * Returns true if a certain type is currently loaded as object representation
     *
     * @param $typeName
     *
     * @return bool
     */
    public function isTypeLoaded($typeName): bool
    {
        return isset($this->types[$typeName]);
    }

    /**
     * Allows you to completely replace all type instances for this table.
     *
     * @param   TcaTableType[]  $types
     *
     * @return $this
     */
    public function setLoadedTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Returns the list of all types inside this table
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType[]
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     */
    public function getLoadedTypes(): array
    {
        return $this->types;
    }

    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        parent::clear();
        $this->context->cs()->sqlBuilder->removeTableDefinitions($this->getTableName());
        $this->types = [];
    }

    /**
     * Returns the initial state of the TCA when this table was initialized
     *
     * @return array
     */
    public function getInitialConfig(): array
    {
        return $this->initialConfig;
    }

    /**
     * Allows to update the initial state of the TCA when this table was initialized
     *
     * @param   array  $initialConfig
     *
     * @return TcaTable
     */
    public function setInitialConfig(array $initialConfig): TcaTable
    {
        $this->initialConfig = $initialConfig;

        return $this;
    }

}
