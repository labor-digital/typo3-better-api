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


use Doctrine\DBAL\Schema\Table;
use LaborDigital\T3BA\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTypeList;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\Traits\ElementConfigTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\TcaTableConfigTrait;
use Neunerlei\Arrays\Arrays;

class TcaTable extends AbstractTypeList
{
    use ElementConfigTrait;
    use TcaTableConfigTrait;
    use DataHookCollectorTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory
     */
    protected $typeFactory;
    
    /**
     * Holds the name of the db table we work with
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        string $tableName,
        TcaBuilderContext $context,
        TableFactory $tableFactory,
        TypeFactory $typeFactory
    )
    {
        parent::__construct($context);
        $this->tableName = $tableName;
        $this->tableFactory = $tableFactory;
        $this->typeFactory = $typeFactory;
    }
    
    /**
     * Returns the name of the linked database table
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Used to provide the correct auto-complete information
     *
     * @inheritDoc
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType
     */
    public function getType($typeName = null): AbstractType
    {
        return parent::getType($typeName);
    }
    
    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        parent::clear();
        $this->config = [];
        $this->context->cs()->sqlRegistry->clearTable($this->tableName);
    }
    
    /**
     * Can be used to set raw config values, that are not implemented in the TCA builder facade.
     *
     * @param   array  $raw         The configuration to set
     * @param   bool   $repopulate  Not all parts of the tca are updated automatically after you changed them.
     *                              "types", "palettes" and "columns" are only stored in their initial state.
     *                              Those elements are represented by a tree of objects containing the actual
     *                              information. If you want to recreate this tree, set this property to true.
     *                              This forces the system to invalidate all existing objects and recreate
     *                              them based on your provided $data. All existing instances and their configuration
     *                              will be dropped and set to the provided configuration in data. Be careful with this!
     *
     * @return $this
     */
    public function setRaw(array $raw, bool $repopulate = false)
    {
        $this->loadDataHooks($raw);
        
        if ($repopulate) {
            $this->clear();
        }
        
        $this->config = $raw;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getRaw(bool $initial = false): array
    {
        if ($initial) {
            return $this->config;
        }
        
        $raw = Arrays::without($this->config, ['columns', 'types', 'palettes']);
        $this->dumpDataHooks($raw);
        
        return $raw;
    }
    
    /**
     * Returns the doctrine table that allows you to modify the table definition directly.
     *
     * Please note, that column SQL definitions are not part of the returned table,
     * as they are located in your fields under $this->getField()->getColumn().
     *
     * You can use this to create indexes or foreign key constraints on a global level, that will be
     * added to your sql when it is compiled.
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getSchema(): Table
    {
        return $this->context->cs()->sqlRegistry->getTableOverride($this->getTableName());
    }
    
    /**
     * @inheritDoc
     */
    protected function loadType($typeName): AbstractType
    {
        $type = $this->typeFactory->create($typeName, $this);
        $this->typeFactory->populate($type);
        
        return $type;
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        return [];
    }
}
