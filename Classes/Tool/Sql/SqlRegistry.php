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


namespace LaborDigital\T3ba\Tool\Sql;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\EventHandler\Sql;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\Sql\Io\DefinitionProcessor;
use LaborDigital\T3ba\Tool\Sql\Io\Dumper;
use LaborDigital\T3ba\Tool\Sql\Io\TableAdapter;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Cache\Backend\ApcuBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;
use TYPO3\CMS\Core\SingletonInterface;

class SqlRegistry implements SingletonInterface
{
    public const FALLBACK_TYPE_NAME = 'sql_registry_fallback';
    public const TABLE_OVERRIDE_TYPE_NAME = 'sql_registry_table_type';
    
    use ContainerAwareTrait;
    
    /**
     * @var \TYPO3\CMS\Core\Database\Schema\SqlReader
     */
    protected $reader;
    
    /**
     * @var \TYPO3\CMS\Core\Database\Schema\SchemaMigrator
     */
    protected $migrator;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Sql\Io\DefinitionProcessor
     */
    protected $processor;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Sql\Io\Dumper
     */
    protected $dumper;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Sql\Definition
     */
    protected $definition;
    
    /**
     * Registry constructor.
     *
     * @param   \TYPO3\CMS\Core\Database\Schema\SqlReader           $reader
     * @param   \TYPO3\CMS\Core\Database\Schema\SchemaMigrator      $migrator
     * @param   \LaborDigital\T3ba\Tool\Sql\Io\DefinitionProcessor  $processor
     * @param   \LaborDigital\T3ba\Tool\Sql\Io\Dumper               $dumper
     */
    public function __construct(
        SqlReader $reader,
        SchemaMigrator $migrator,
        DefinitionProcessor $processor,
        Dumper $dumper
    )
    {
        $this->reader = $reader;
        $this->migrator = $migrator;
        $this->processor = $processor;
        $this->dumper = $dumper;
        
        // Register fallback if required
        $reg = Type::getTypeRegistry();
        if (! $reg->has(self::FALLBACK_TYPE_NAME)) {
            $reg->register(self::FALLBACK_TYPE_NAME, new FallbackType());
        }
    }
    
    /**
     * Returns the doctrine schema object for a single database table
     *
     * @param   string  $tableName
     *
     * @return \Doctrine\DBAL\Schema\Table
     */
    public function getTable(string $tableName): Table
    {
        $this->loadDefinition();
        
        if (! isset($this->definition->tables[$tableName])) {
            $this->definition->newTableNames[] = $tableName;
            $this->definition->tables[$tableName] = $this->makeInstance(Table::class, [
                $tableName,
                [],
                [],
                [],
                0,
                [],
            ]);
        }
        
        return $this->definition->tables[$tableName];
    }
    
    /**
     * Returns the doctrine schema object for a single "type" of a table.
     * This is mostly used in the TCA builder.
     *
     * Each table type (in TYPO3 terms) has it's own SQL representation by a clone of the
     * default table definition that got loaded based on the ext_table.sql files of all loaded extensions.
     *
     * These types will then be merged (hopefully quite intelligently) back into a single table object
     * that is used for the SQL statement generation.
     *
     * @param   string      $tableName
     * @param   string|int  $typeName
     *
     * @return \Doctrine\DBAL\Schema\Table|mixed
     */
    public function getType(string $tableName, $typeName): Table
    {
        $this->loadDefinition();
        
        if (! isset($this->definition->types[$tableName][$typeName])) {
            if ($typeName === static::TABLE_OVERRIDE_TYPE_NAME) {
                $table = $this->getTable($tableName);
                $type = new TableOverride($tableName, [], $table->getIndexes(), $table->getForeignKeys());
            } else {
                $type = clone $this->getTable($tableName);
            }
            
            $this->definition->types[$tableName][$typeName] = $type;
        }
        
        return $this->definition->types[$tableName][$typeName];
    }
    
    /**
     * Removes the registered type configuration from the definition object
     *
     * @param   string  $tableName
     * @param           $typeName
     */
    public function removeType(string $tableName, $typeName): void
    {
        unset($this->definition->types[$tableName][$typeName]);
    }
    
    /**
     * This is a TCA builder special case. The table object itself provides the author with the option
     * to access the SQL table definition. This is useful to register indexes or foreign keys for the table,
     * which is done on a global scale and not on a per-field basis.
     *
     * @param   string  $tableName
     *
     * @return \LaborDigital\T3ba\Tool\Sql\TableOverride
     */
    public function getTableOverride(string $tableName): TableOverride
    {
        return $this->getType($tableName, static::TABLE_OVERRIDE_TYPE_NAME);
    }
    
    /**
     * Returns a single column object representing a unique column of a specified type
     *
     * @param   string  $tableName
     * @param           $typeName
     * @param   string  $fieldName
     *
     * @return \Doctrine\DBAL\Schema\Column
     */
    public function getColumn(string $tableName, $typeName, string $fieldName): Column
    {
        $type = $this->getType($tableName, $typeName);
        if (! $type->hasColumn($fieldName)) {
            $col = $type->addColumn($fieldName, static::FALLBACK_TYPE_NAME);
            
            // Try to inherit options from another type
            foreach (($this->definition->types[$tableName] ?? []) as $_typeName => $_type) {
                if ($_typeName === static::TABLE_OVERRIDE_TYPE_NAME) {
                    continue;
                }
                
                if ($typeName !== $_typeName && $_type->hasColumn($fieldName)) {
                    ColumnAdapter::inheritConfig($col, $_type->getColumn($fieldName));
                    break;
                }
            }
            
            return $col;
        }
        
        return $type->getColumn($fieldName);
    }
    
    /**
     * Helper to build the name of an mm table.
     *
     * @param   string       $tableName  The name of the table to create the mm table name for
     * @param   string|null  $fieldName  DEPRECATED: will be removed in v12, the name of the field the mm table should apply to.
     *
     * @return string
     */
    public function makeMmTableName(string $tableName, ?string $fieldName = null): string
    {
        if ($fieldName !== null) {
            $tableName = str_replace('_domain_model_', '_', $tableName) . '_' . Inflector::toUnderscore($fieldName);
        }
        
        return $this->prepareTableName($tableName, 'mm');
    }
    
    /**
     * Helper to prepare the name of a single table name to be at max 128 chars long.
     * Also allows to append a suffix to the table name for _mm tables and alike
     *
     * @param   string       $tableName  The name of the table to prepare
     * @param   string|null  $suffix     An optional suffix to append to the table name
     *
     * @return string
     */
    public function prepareTableName(string $tableName, ?string $suffix = null): string
    {
        $maxTableLength = 128;
        
        if (is_string($suffix)) {
            $suffix = '_' . ltrim($suffix, '_');
            $maxTableLength -= strlen($suffix);
        }
        
        if (strlen($tableName) > $maxTableLength) {
            $tableNameHash = md5($tableName);
            $tableName = substr($tableName, 0, $maxTableLength - 32 - 1); // max length - md5 length - 1 for "_"
            $tableName .= '_' . $tableNameHash;
        }
        
        return $tableName . $suffix;
    }
    
    /**
     * an be used to create a mm table definition in the sql file.
     *
     * @param   string       $tableName    Until v11: If $fieldName is set, the table name to create the mm table for
     *                                     After v11: The absolute name of the table to generate
     * @param   string|null  $fieldName    deprecated, will be removed in v11:
     *                                     The field name of the given table we create the mm table for
     * @param   string|null  $mmTableName  deprecated, will be removed in v11:
     *                                     Optionally the manually provided name of the mm table
     *
     * @return string
     * @todo clean up this method in the v11 update
     */
    public function registerMmTable(
        string $tableName,
        ?string $fieldName = null,
        ?string $mmTableName = null
    ): string
    {
        // @todo remove this in v11
        if ($fieldName === null && $mmTableName === null) {
            $mmTableName = $tableName;
        } else {
            $mmTableName = $this->makeMmTableName($tableName, $fieldName);
        }
        
        if (isset($this->definition->tables[$mmTableName])) {
            return $mmTableName;
        }
        
        $table = $this->getTable($mmTableName);
        $table->addColumn('uid', 'integer', ['length' => 11, 'notnull' => true, 'autoincrement' => true]);
        $table->addColumn('uid_local', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        $table->addColumn('uid_foreign', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        $table->addColumn('tablenames', 'string', ['length' => 128, 'notnull' => true, 'default' => '']);
        $table->addColumn('fieldname', 'string', ['length' => 128, 'notnull' => true, 'default' => '']);
        $table->addColumn('sorting', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        $table->addColumn('sorting_foreign', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        
        // @todo remove this in v12
        if (! $this->cs()->typoContext->config()->isFeatureEnabled(T3baFeatureToggles::TCA_V11_MM_TABLES)) {
            $table->addColumn('ident', 'string', ['length' => 128, 'notnull' => true, 'default' => '']);
        }
        
        $table->setPrimaryKey(['uid']);
        $table->addIndex(['uid_local'], 'uid_local');
        $table->addIndex(['uid_foreign'], 'uid_foreign');
        
        return $mmTableName;
    }
    
    /**
     * Dumps the collected TCA changes into a single SQL string.
     * NOTE: This is a HEAVY operation that is not cached. Please use it with care!
     *
     * @return string
     */
    public function dump(): string
    {
        if (! isset($this->definition)) {
            return '';
        }
        
        return $this->dumper->dump(
            $this->processor->findTableDiff($this->definition)
        );
    }
    
    /**
     * Flushes the complete definition object and resets the registry
     */
    public function clear(): void
    {
        $this->definition = null;
    }
    
    /**
     * Removes all changed sql definitions for a specific table.
     * This will reset the table definition to the configuration loaded from the ext_tables.sql files
     *
     * @param   string  $tableName
     */
    public function clearTable(string $tableName): void
    {
        unset($this->definition->types[$tableName]);
    }
    
    /**
     * Makes sure the definition object exists and is initialized.
     * It will also load all sql strings and create table instances for them.
     */
    protected function loadDefinition(): void
    {
        if ($this->definition) {
            return;
        }
        
        // This is a hotfix, to prevent issues when the APCU Backend is disabled in the cli.
        // This removes the apcu backends and replaces them with runtime backends instead
        if (php_sapi_name() === 'cli') {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'] as &$config) {
                if ($config['backend'] === ApcuBackend::class) {
                    $config['backend'] = TransientMemoryBackend::class;
                }
            }
            unset($config);
        }
        
        Sql::$enabled = false;
        $definition = $this->reader->getTablesDefinitionString(false);
        Sql::$enabled = true;
        $statements = $this->reader->getStatementArray($definition);
        $rawTables = $this->migrator->parseCreateTableStatements($statements);
        
        $tables = [];
        foreach ($rawTables as $table) {
            if (! isset($tables[$table->getName()])) {
                $tables[$table->getName()] = $table;
            } else {
                TableAdapter::mergeTables($tables[$table->getName()], $table);
            }
        }
        
        $this->definition = $this->makeInstance(Definition::class, [$tables]);
    }
}
