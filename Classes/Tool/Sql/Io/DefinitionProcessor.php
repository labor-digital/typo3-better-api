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


namespace LaborDigital\T3ba\Tool\Sql\Io;


use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\Sql\TableFilterEvent;
use LaborDigital\T3ba\Tool\Sql\Definition;
use LaborDigital\T3ba\Tool\Sql\FallbackType;
use LaborDigital\T3ba\Tool\Sql\SqlFieldLength;
use LaborDigital\T3ba\Tool\Sql\SqlRegistry;
use LaborDigital\T3ba\Tool\Sql\TableOverride;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Database\Schema\DefaultTcaSchema;

class DefinitionProcessor
{
    use ContainerAwareTrait;
    
    /**
     * DefinitionProcessor constructor.
     *
     * @param   \Doctrine\DBAL\Schema\Comparator|null  $comparator
     */
    public function __construct(?Comparator $comparator = null)
    {
        $this->setService(Comparator::class, $comparator ?? new Comparator());
    }
    
    /**
     * Receives the sql registry definition object and generates a list with tables
     * containing differences to be dumped into an SQL string
     *
     * @param   \LaborDigital\T3ba\Tool\Sql\Definition  $definition
     *
     * @return array
     */
    public function findTableDiff(Definition $definition): array
    {
        $newTables = [];
        $tables = [];
        
        foreach ($definition->tables as $table) {
            if ($definition->isNew($table)) {
                $newTables[] = $table;
            }
            if ($definition->isDumpable($table)) {
                $tables[] = $table;
            }
        }
        
        $this->applyDefaultSchema($newTables);
        
        return $this->processTables($tables, $definition);
    }
    
    /**
     * Applies the default TCA schema to the list of new tables that have been added
     *
     * @param   array  $newTables
     */
    protected function applyDefaultSchema(array $newTables): void
    {
        if (! empty($newTables)) {
            $defaultSchema = $this->makeInstance(DefaultTcaSchema::class);
            $defaultSchema->enrich($newTables);
        }
    }
    
    /**
     * Iterates the given tables and merges all possible subtypes, as well as the table override
     * into a single table definition. After that a diff is calculated for each table.
     *
     * The result is a list of pseudo tables that are used to build the extension SQL
     *
     * @param   array                                   $tables
     * @param   \LaborDigital\T3ba\Tool\Sql\Definition  $definition
     *
     * @return array
     */
    protected function processTables(array $tables, Definition $definition): array
    {
        $result = [];
        
        foreach ($tables as $table) {
            /** @noinspection ProperNullCoalescingOperatorUsageInspection */
            $types = $definition->types[$table->getName()] ?? [];
            
            $tableToDump = null;
            if (empty($types)) {
                // Special fallback if we have a new table that has no types configured
                // I think we want to add it to the database anyway.
                if ($definition->isNew($table)) {
                    $tableToDump = $table;
                } else {
                    continue;
                }
            }
            
            // Default handling if we have no "new" table override
            if ($tableToDump === null) {
                $combined = $this->mergeTypes($table, $types);
                
                if ($types[SqlRegistry::TABLE_OVERRIDE_TYPE_NAME]) {
                    $this->mergeOverride($combined, $types[SqlRegistry::TABLE_OVERRIDE_TYPE_NAME]);
                }
                
                $tableToDump = $this->makeDumpableTable($combined, $table, $definition);
            }
    
    
            $tableToDump = $this->cs()->eventBus
                ->dispatch(new TableFilterEvent($table->getName(), $table, $tableToDump))
                ->getTableToDump();
            
            if ($tableToDump !== null) {
                $result[] = $tableToDump;
            }
        }
        
        return $result;
    }
    
    /**
     * Merges the given $initial table definition with all types that are provided
     * into a single, new table object
     *
     * @param   \Doctrine\DBAL\Schema\Table  $initial
     * @param   Table[]                      $types
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    protected function mergeTypes(Table $initial, array $types): Table
    {
        $combined = clone $initial;
        
        foreach ($types as $typeName => $type) {
            if ($typeName === SqlRegistry::TABLE_OVERRIDE_TYPE_NAME) {
                continue;
            }
    
            // Ensure that all columns of $combined exist on $type
            // This fixes false "renaming" detections of the comparator
            $typeClone = clone $type;
            foreach ($combined->getColumns() as $column) {
                if (! $typeClone->hasColumn($column->getName())) {
                    TableAdapter::attachColumn($typeClone, $column);
                }
            }
    
            $diff = $this->getService(Comparator::class)->diffTable($combined, $typeClone);
    
            if (! $diff) {
                continue;
            }
    
            // Add new column
            foreach ($diff->addedColumns as $column) {
                TableAdapter::attachColumn($combined, $column);
            }
    
            // Modify columns
            foreach ($diff->changedColumns as $columnDiff) {
                TableAdapter::replaceColumn($combined, $this->processColumnDiff($columnDiff));
            }
            
            // Theoretically there could be other actions to be applied
            // but those should never occur in our TCA builder use case.
        }
        
        return $combined;
    }
    
    /**
     * Merges the registered table overrides into the $combined table object
     *
     * @param   \Doctrine\DBAL\Schema\Table                $combined
     * @param   \LaborDigital\T3ba\Tool\Sql\TableOverride  $override
     */
    protected function mergeOverride(Table $combined, TableOverride $override): void
    {
        $override = clone $override;
        $override->unlock();
        
        // Drop all fallback columns -> we don't need those
        $this->dropFallbackColumns($override);
        
        TableAdapter::mergeTables($combined, $override);
    }
    
    /**
     * Calculates a table diff between $initial and $combined. The diff will be returned as new
     * pseudo table object
     *
     * @param   \Doctrine\DBAL\Schema\Table             $combined
     * @param   \Doctrine\DBAL\Schema\Table             $initial
     * @param   \LaborDigital\T3ba\Tool\Sql\Definition  $definition
     *
     * @return \Doctrine\DBAL\Schema\Table|null
     */
    protected function makeDumpableTable(Table $combined, Table $initial, Definition $definition): ?Table
    {
        // Drop all fallback columns -> TYPO3 should handle this or it is not correctly configured
        $this->dropFallbackColumns($combined);
        
        // Build a merge sum based on the combined types and their diff to the currently configured table
        $diff = $this->getService(Comparator::class)->diffTable($initial, $combined);
        
        // Check if there is nothing to do...
        if (! $diff) {
            // Make sure new tables get added to the db even if there are no columns registered yet.
            if (! $definition->isNew($initial)) {
                return null;
            }
            
            // Create a fallback diff with a single column
            $diff = new TableDiff($initial->getName(), [
                'uid' => new Column('uid', new IntegerType()),
            ]);
        }
        
        // Build the final table definition based on the calculated diff
        return $this->makeTableFromDiff($diff);
    }
    
    /**
     * Internal helper that drops all columns that have our internal "fallback" type from the given table
     *
     * @param   \Doctrine\DBAL\Schema\Table  $table
     */
    protected function dropFallbackColumns(Table $table): void
    {
        TableAdapter::dropPrimaryKeyNameIfNoIndexExists($table);
        
        foreach ($table->getColumns() as $column) {
            if ($column->getType() instanceof FallbackType) {
                $table->dropColumn($column->getName());
            }
        }
    }
    
    /**
     * Creates a new table instance with only either added or changed columns of the provided diff
     *
     * @param   \Doctrine\DBAL\Schema\TableDiff  $diff
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    protected function makeTableFromDiff(TableDiff $diff): Table
    {
        return new Table(
            $diff->name,
            array_merge(
                $diff->addedColumns,
                array_map(static function (ColumnDiff $diff): Column {
                    return $diff->column;
                }, $diff->changedColumns)
            ),
            array_merge(
                $diff->addedIndexes,
                $diff->changedIndexes
            ),
            array_merge(
                $diff->addedForeignKeys,
                $diff->changedForeignKeys
            )
        );
    }
    
    /**
     * Processes the diff of a single column by merging the changes "intelligently" into the
     * existing column (hopefully) without breaking something important on the way.
     *
     * @param   \Doctrine\DBAL\Schema\ColumnDiff  $diff
     *
     * @return \Doctrine\DBAL\Schema\Column
     */
    protected function processColumnDiff(ColumnDiff $diff): Column
    {
        $target = clone $diff->fromColumn;
        $new = $diff->column;
        
        if ($new->getType() instanceof FallbackType) {
            return $target;
        }
        if ($target->getType() instanceof FallbackType) {
            return $new;
        }
        
        // Inherit dynamic properties
        $ignoredProps = ['length', 'type'];
        foreach ($diff->changedProperties as $property) {
            if (in_array($property, $ignoredProps, true)) {
                continue;
            }
            
            $getter = Inflector::toGetter($property);
            $setter = Inflector::toSetter($property);
            $target->$setter($new->$getter());
        }
        
        // Always use the bigger length
        /** @noinspection InsufficientTypesControlInspection */
        if ($diff->hasChanged('length') && ($target->getLength() === null ||
                                            ($new->getLength() !== null && $target->getLength() < $new->getLength()))) {
            $target->setLength($new->getLength());
        }
        
        // Merge type changes
        if ($diff->hasChanged('type')) {
            $this->processColumnTypeOverride($target, $new);
        }
        
        return $target;
    }
    
    /**
     * Internal helper that tries to intelligently resolve the type changes in a column
     * by avoiding making the override more restrictive than the previous type
     *
     * @param   \Doctrine\DBAL\Schema\Column  $target
     * @param   \Doctrine\DBAL\Schema\Column  $new
     */
    protected function processColumnTypeOverride(Column $target, Column $new): void
    {
        // The default type configuration to be applied to the given column
        $defaultTypeConfig = static function (Column $column): void {
            $column->setType(new TextType())
                   ->setLength(SqlFieldLength::MEDIUM_TEXT)
                   ->setNotnull(false)
                   ->setDefault(null);
        };
        
        // Fallback types are ignored -> we always keep the target
        if ($new->getType() instanceof FallbackType) {
            // The only exception would be, if the target has also a fallback type
            // In that case -> fall back to the default configuration
            if ($target->getType() instanceof FallbackType) {
                $defaultTypeConfig($target);
            }
            
            return;
        }
        
        $targetBType = $target->getType()->getBindingType();
        $newBType = $new->getType()->getBindingType();
        
        // Handle string -> string changes.
        if ($targetBType === $newBType && $targetBType === ParameterType::STRING) {
            // New type is text -> this overrules all other string types
            if ($new->getType() instanceof TextType) {
                $defaultTypeConfig($target);
            }
            
            return;
        }
        
        // Handle remapping based on priority
        $priorityCalculator = static function (int $bType): int {
            foreach (
                [
                    [ParameterType::NULL, 0],
                    [ParameterType::BOOLEAN, 1],
                    [ParameterType::INTEGER, 2],
                    [ParameterType::BINARY, 3],
                    [ParameterType::ASCII, 4],
                    [ParameterType::STRING, 5],
                    [ParameterType::LARGE_OBJECT, 6],
                ] as $test
            ) {
                if ($bType === $test[0]) {
                    return $test[1];
                }
            }
            
            return 0;
        };
        
        // If new priority > target priority -> override the type
        if ($priorityCalculator($newBType) > $priorityCalculator($targetBType)) {
            $target->setType($new->getType())
                   ->setLength($new->getLength());
            
            return;
        }
        
        // Could not resolve a type -> Fallback to text type
        $defaultTypeConfig($target);
    }
}
