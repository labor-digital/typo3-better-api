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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table;


use Doctrine\DBAL\Schema\Table;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3ba\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractType;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractTypeList;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits\ElementConfigTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Traits\TcaTableConfigTrait;
use Neunerlei\Arrays\Arrays;

class TcaTable extends AbstractTypeList
{
    use ElementConfigTrait;
    use TcaTableConfigTrait;
    use DataHookCollectorTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TypeFactory
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
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType
     */
    public function getType($typeName = null): AbstractType
    {
        return parent::getType($typeName);
    }
    
    /**
     * @inheritDoc
     */
    public function getTypeNames(): array
    {
        // @todo this could theoretically lead to issues. The class should have a flag that tells it
        // that it should retrieve the types from the config (while it is still being populated)
        // and after it has been populated it should only be aware of the actually loaded types
        $tcaTypes = array_keys($this->config['types'] ?? []);
        $loadedTypes = array_diff(array_keys($this->types), $tcaTypes);
        
        return array_merge($tcaTypes, $loadedTypes);
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
     * @inheritDoc
     */
    public function removeType($typeName)
    {
        $this->context->cs()->sqlRegistry->removeType($this->tableName, $typeName);
        
        return parent::removeType($typeName);
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
    public function setRaw(array $raw, bool $repopulate = false): self
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
     * Sometimes you want to apply som raw adjustments on a per-table level after the table
     * class has been dumped back as an array.
     *
     * The processor callable receives the $config, $extractedMeta and $tableName as parameters.
     * It should create references to the given values in order to modify them.
     *
     * @param   callable  $callback  The post processor callable to execute, it receives the $config,
     *                               $extractedMeta and $tableName as parameters.
     *                               It should create references to the given values in order to modify them.
     *
     * @return $this
     */
    public function registerRawProcessor(callable $callback): self
    {
        TcaPostProcessor::registerAdditionalProcessor($this->getTableName(), $callback);
        
        return $this;
    }
    
    /**
     * All changes to table "palettes" will normally be done "per-type", meaning if a palette exists globally on a table,
     * and a single type modifies its content a new variant for this type is auto-created. This avoids unexpected side-effects
     * when working with palettes on multiple types.
     *
     * While this works in 90% of all use-cases this approach has some drawbacks,
     * when you want to modify existing tables, especially core tables like tt_content or sys_file_reference.
     *
     * For this reason you can use this method to modify the palettes on all types that have the palette in question.
     *
     * @param   string    $paletteName  The name of the palette you want to modify
     * @param   callable  $callback     A callback which receives the instance of the palette and the type that contains the palette.
     *                                  Something like: function (TcaPalette $palette) {$palette->getField('...');}
     *
     * @return $this
     */
    public function modifyPaletteGlobally(string $paletteName, callable $callback): self
    {
        foreach ($this->getTypeNames() as $typeName) {
            $type = $this->getType($typeName);
            
            if (! $type->hasPalette($paletteName) && $typeName !== $this->getDefaultTypeName()) {
                continue;
            }
            
            $callback($type->getPalette($paletteName), $type);
        }
        
        return $this;
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
