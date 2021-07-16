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


namespace LaborDigital\T3ba\Tool\Tca\ContentType\Builder\Io;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\DomainModelMapStep;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\Step\TablesOnStandardPagesStep;
use LaborDigital\T3ba\Tool\Sql\ColumnAdapter;
use LaborDigital\T3ba\Tool\Sql\SqlRegistry;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Dumper as TableDumper;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3ba\Tool\Tca\ContentType\Builder\ContentType;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\DefaultDataModel;
use Neunerlei\Inflection\Inflector;

class Dumper
{
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Dumper
     */
    protected $tableDumper;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Sql\SqlRegistry
     */
    protected $sqlRegistry;
    
    /**
     * The list of registered type instances that should be dumped
     *
     * @var ContentType[]
     */
    protected $typesToDump = [];
    
    public function __construct(TableDumper $tableDumper, TableFactory $tableFactory, SqlRegistry $sqlRegistry)
    {
        $this->tableDumper = $tableDumper;
        $this->tableFactory = $tableFactory;
        $this->sqlRegistry = $sqlRegistry;
    }
    
    /**
     * Adds a new content type definition to the list of dumped types
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\ContentType\Builder\ContentType  $type
     */
    public function registerType(ContentType $type): void
    {
        $this->typesToDump[] = $type;
    }
    
    /**
     * Dumps an array of modified tca tables
     *
     * @param   array             $tca
     * @param   ExtConfigContext  $context
     *
     * @return array
     */
    public function dump(array $tca, ExtConfigContext $context): array
    {
        if (empty($this->typesToDump)) {
            return [];
        }
        
        // Start a fresh instance of the tt_content table to which we will add our types
        $table = $this->tableFactory->create('tt_content', $context);
        $this->tableFactory->initialize($table);
        
        $result = [];
        $tables = [];
        $columns = [];
        $typeColumns = [];
        $models = [];
        foreach ($this->typesToDump as $type) {
            $cType = $type->getTypeName();
            $models[$cType] = $type->getDataModelClass();
            $columnNameMap = $this->renameExtensionColumns($tca, $type);
            $this->rewriteDisplayConditions($columnNameMap, $type);
            $sqlColumns = $this->extractExtensionSqlColumns($cType, $columnNameMap);
            $tableName = $this->generateExtensionTableSql($cType, $sqlColumns);
            $columnNameMap = array_flip($columnNameMap);
            $typeColumns[$cType] = $columnNameMap;
            $columns[] = $columnNameMap;
            if ($tableName) {
                $result[$tableName] = $this->generateExtensionTca($tableName, $cType, $sqlColumns);
                $tables[$cType] = $tableName;
            }
            $table->setLoadedType($cType, $type);
        }
        $columns = array_merge(...$columns);
        $models = array_filter($models, static function (string $v) {
            return $v !== DefaultDataModel::class;
        });
        
        $tableTca = $this->tableDumper->dump($table);
        $tableTca['ctrl']['contentType'] = [
            'tables' => $tables,
            'columns' => $columns,
            'typeColumns' => $typeColumns,
            'typeModels' => $models,
        ];
        $tableTca = $this->registerModelClasses($tableTca, $models, $typeColumns);
        $result['tt_content'] = $this->processContentTca($tableTca, $tables);
        
        return $result;
    }
    
    protected function renameExtensionColumns(array $tca, ContentType $type): array
    {
        $nameMap = [];
        foreach ($type->getFields() as $field) {
            $columnName = $field->getId();
            if (array_key_exists($columnName, $tca['tt_content']['columns'] ?? [])) {
                continue;
            }
            
            $nsColumnName = 'ct_' . $type->getSignature() . '_' . $columnName;
            $nameMap[$columnName] = $nsColumnName;
            $node = FieldAdapter::getNode($field);
            $node->getParent()->renameChild($columnName, $nsColumnName);
            
            // Handle foreign field relations
            $raw = $field->getRaw();
            if (isset($raw['config']['foreign_match_fields']['fieldname'])
                && $raw['config']['foreign_match_fields']['fieldname'] === $columnName) {
                $field->addConfig(['foreign_match_fields' => ['fieldname' => $nsColumnName]]);
            }
        }
        
        return $nameMap;
    }
    
    /**
     * Iterates all fields in the type and rewrites the display conditions if short fieldnames have been used.
     *
     * @param   array                                                        $nameMap
     * @param   \LaborDigital\T3ba\Tool\Tca\ContentType\Builder\ContentType  $type
     */
    protected function rewriteDisplayConditions(array $nameMap, ContentType $type): void
    {
        if (empty($nameMap)) {
            return;
        }
        
        foreach ($type->getFields() as $field) {
            $condition = $field->getDisplayCondition();
            if (empty($condition)) {
                continue;
            }
            
            $field->setDisplayCondition($this->rewriteSingleDisplayCondition($nameMap, $condition));
        }
    }
    
    /**
     * Processes a single display condition value in order to rewrite short keys
     * with their extended key name provided by the content type
     *
     * @param   array         $nameMap    The map between short and long field names
     * @param   array|string  $condition  The condition to rewrite if needed
     *
     * @return array|mixed|string
     */
    protected function rewriteSingleDisplayCondition(array $nameMap, $condition)
    {
        if (is_array($condition)) {
            $conditionClean = [];
            foreach ($condition as $k => $v) {
                $conditionClean[$k] = $this->rewriteSingleDisplayCondition($nameMap, $v);
            }
            
            return $conditionClean;
        }
        
        if (is_string($condition) && str_contains(strtolower($condition), 'field:')) {
            $parts = explode(':', $condition);
            $update = false;
            $checkNext = false;
            foreach ($parts as $k => $part) {
                if (strtolower($part) === 'field') {
                    $checkNext = true;
                    continue;
                }
                
                if ($checkNext) {
                    $checkNext = false;
                    
                    if (isset($nameMap[$part])) {
                        $update = true;
                        $parts[$k] = $nameMap[$part];
                    }
                }
            }
            
            if ($update) {
                return implode(':', $parts);
            }
        }
        
        return $condition;
    }
    
    protected function extractExtensionSqlColumns(string $cType, array $nameMap): array
    {
        $extractedColumns = [];
        $type = $this->sqlRegistry->getType('tt_content', $cType);
        
        foreach ($nameMap as $columnName => $foo) {
            $extractedColumns[$columnName] = $type->getColumn($columnName);
            $type->dropColumn($columnName);
        }
        
        return $extractedColumns;
    }
    
    protected function generateExtensionTableSql(string $cType, array $columns): ?string
    {
        if (empty($columns)) {
            return null;
        }
        
        $tableName = 'ct_' . $cType;
        
        $table = $this->sqlRegistry->getType($tableName, 'contentType');
        
        $table->addColumn('ct_parent', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        
        foreach ($columns as $columnName => $column) {
            $col = $table->addColumn($columnName, 'integer');
            ColumnAdapter::inheritConfig($col, $column);
        }
        
        return $tableName;
    }
    
    protected function generateExtensionTca(string $tableName, string $cType, array $columnNames): array
    {
        $tca = TableDefaults::TABLE_TCA;
        $tca['ctrl']['title'] = 'Content Type - Table Extension - ' . $cType;
        $tca['ctrl']['hideTable'] = true;
        
        $tca['ctrl'][TablesOnStandardPagesStep::CONFIG_KEY] = true;
        
        $tca['columns']['l10n_parent']['config']['foreign_table'] = $tableName;
        $tca['columns']['l10n_parent']['config']['foreign_table_where'] = str_replace(
            '{{table}}',
            $tableName,
            $tca['columns']['l10n_parent']['config']['foreign_table_where']
        );
        
        $columnDefault = ['config' => ['type' => 'input', 'readOnly' => true]];
        
        $columnNames = array_merge(['ct_parent'], array_keys($columnNames));
        foreach ($columnNames as $columnName) {
            $tca['columns'][$columnName] = array_merge($columnDefault, ['label' => $columnName]);
            $showItem[] = $columnName;
        }
        
        $tca['types'][0]['showitem'] = implode(',', $columnNames);
        
        return $tca;
    }
    
    /**
     * Enhances the tca of the tt_content table to include the model mapping for our content models.
     * The post processor will pick them up and generate the required typoscript for ous.
     *
     * @param   array  $tableTca
     * @param   array  $models
     * @param   array  $typeColumns
     *
     * @return array
     */
    protected function registerModelClasses(array $tableTca, array $models, array $typeColumns): array
    {
        if (empty($models)) {
            return $tableTca;
        }
        
        foreach ($models as $cType => $className) {
            $columns = array_values($typeColumns[$cType] ?? []);
            $mapping = array_combine($columns, $columns);
            $mapping = array_map(static function (string $column) {
                return Inflector::toProperty($column);
            }, $mapping);
            
            $tableTca['ctrl'][DomainModelMapStep::CONFIG_KEY][$className] = $mapping;
        }
        
        // Attach default class mapping
        if (! isset($tableTca['ctrl'][DomainModelMapStep::CONFIG_KEY][DefaultDataModel::class])) {
            $tableTca['ctrl'][DomainModelMapStep::CONFIG_KEY][DefaultDataModel::class] = [];
        }
        
        return $tableTca;
    }
    
    protected function processContentTca(array $tca, array $tableMap): array
    {
        if (empty($tableMap)) {
            return $tca;
        }
        
        $this->sqlRegistry->getType('tt_content', 'contentType')
                          ->addColumn('ct_child', 'integer', ['length' => 11, 'notnull' => true, 'default' => 0]);
        
        $tca['columns']['ct_child'] = [
            'label' => 'Content Type Extension Map',
            'description' => 'Allows the tt_content table to map to a set of extended columns on a foreign table like it would extend the table itself',
            'config' => [
                'type' => 'passthrough',
            ],
        ];
        
        foreach ($tableMap as $signature => $tableName) {
            if (! $tca['types'][$signature]) {
                continue;
            }
            
            $tca['types'][$signature]['columnsOverrides']['ct_child'] = [
                'config' => ['foreign_table' => $tableName],
            ];
        }
        
        return $tca;
    }
}
