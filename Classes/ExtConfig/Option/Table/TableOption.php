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
 * Last modified: 2020.03.21 at 21:12
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Table;

use LaborDigital\Typo3BetterApi\Event\Events\ExtTablesLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\SqlDefinitionFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\TcaCompletelyLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\TcaWithoutOverridesLoadedEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtConfigOptionInterface;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class TableOption
 *
 * Can be used to configure TYPO3 database tables / also known as the TCA
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\Table
 */
class TableOption extends AbstractExtConfigOption implements ExtConfigOptionInterface
{
    use ExtBasePersistenceMapperTrait;
    
    /**
     * Contains the list of registered model classes and their mapped tables
     * @var array
     */
    protected $tableModels = [];
    
    /**
     * Holds a list of tables that are allowed on standard pages
     * @var array
     */
    protected $tablesOnStandardPages = [];
    
    /**
     * Stores the list of the table positions when showing the list view
     * @var array
     */
    protected $tableListPositions = [];
    
    /**
     * @inheritDoc
     */
    public function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(TcaWithoutOverridesLoadedEvent::class, '__applyTableTca', ['priority' => 200]);
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, '__applyTableTcaOverrides', ['priority' => 500]);
        $subscription->subscribe(ExtTablesLoadedEvent::class, '__applyExtTables');
        $subscription->subscribe(SqlDefinitionFilterEvent::class, '__applySqlExtension');
    }
    
    /**
     * Use this to register a new table in typo3's database.
     * You should NOT use this if you want to edit existing tables! Use registerTableOverride() for that!
     *
     * This is executed after typo3 loaded the base tca files.
     *
     * @param string $configClass The name of the class which is responsible for configuring your table.
     *                            The given class should implement the TableConfigurationInterface
     * @param string $tableName   The name of the table you want to create.
     *                            You may write the name like ...table name to automatically expand it to
     *                            tx_extkey_domain_model_table. If left empty the table name is automatically build
     *                            based on the class name
     *
     * @return $this
     */
    public function registerNewTable(string $configClass, ?string $tableName = null): TableOption
    {
        if (empty($tableName)) {
            $tableName = $this->getTableNameFromConfigClass($configClass);
        }
        return $this->addRegistrationToCachedStack('tables', $this->getRealTableName($tableName), $configClass);
    }
    
    /**
     * Similar to registerNewTable() but registers all table definitions in a directory at once.
     *
     * @param string $directory The path to the directory to add. Either as absolute path or as EXT:... path
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\TableOption
     */
    public function registerNewTableDirectory(string $directory = 'EXT:{{extkey}}/Classes/Domain/Table'): TableOption
    {
        return $this->addDirectoryToCachedStack('tables', $directory, function (string $className) {
            // Check if the class implements the correct interface
            return in_array(TableConfigurationInterface::class, class_implements($className));
        }, function (string $className) {
            return $this->getRealTableName($this->getTableNameFromConfigClass($className));
        });
    }
    
    /**
     * Use if you want to modify existing tables.
     * You should NOT use this to define new tables! Use registerNewTable() for that!
     *
     * This is executed after typo3 loaded the tca.overrides files.
     *
     * @param string $configClass The name of the class which is responsible for configuring your table.
     *                            The given class should implement the TableConfigurationInterface
     * @param string $tableName   The name of the table you want to modify.
     *                            You may write the name like ...table name to automatically expand it to
     *                            tx_extkey_domain_model_table. If left empty the table name is automatically build
     *                            based on the class name
     *
     * @return $this
     */
    public function registerTableOverride(string $configClass, ?string $tableName = null): TableOption
    {
        if (empty($tableName)) {
            $tableName = $this->getTableNameFromConfigClass($configClass);
        }
        return $this->addOverrideToCachedStack('tables', $this->getRealTableName($tableName), $configClass);
    }
    
    /**
     * Use this method if you want to remove a specific table configuration. This works both for new tables as well as
     * overrides.
     *
     * @param string $configClass        The name of the class which should no longer be responsible for configuring
     *                                   your table.
     * @param string $tableName          The name of the table you want to remove the config for.
     *                                   You may write the name like ...table name to automatically expand it to
     *                                   tx_extkey_domain_model_table.
     * @param bool   $overrides          If this is true the $configClass will be removed from the overrides instead of
     *                                   the registrations
     *
     * @return $this
     */
    public function removeTableConfig(string $configClass, ?string $tableName = null, bool $overrides = false): TableOption
    {
        if (empty($tableName)) {
            $tableName = $this->getTableNameFromConfigClass($configClass);
        }
        return $this->removeFromCachedStack('tables', $this->getRealTableName($tableName), $configClass, $overrides);
    }
    
    /**
     * You may use this method if you want to configure a model to map to a specific table.
     *
     * This is quite useful if your database table does not match the extbase defaults.
     *
     * ATTENTION: Make sure you add the Table-Model Mapping static typoscript to your setup!
     * Otherwise the mapping will not be performed!
     *
     * @param string $tableName   The name of the table you want to map the model to
     *                            You may write the name like ...table name to automatically expand it to
     *                            tx_extkey_domain_model_table
     * @param string $modelClass  The name of the model-class you want to map to the given table
     *
     * @return $this
     */
    public function registerModelForTable(string $tableName, string $modelClass): TableOption
    {
        $this->tableModels[$this->getRealTableName($tableName)][] = $modelClass;
        return $this;
    }
    
    /**
     * The registered filter called every time the typo3 backend saves data for $tableName using the backend forms.
     *
     * @param string $tableName        The name of the table to register the filter for
     * @param string $filterClass      The full name of the class containing the filter
     * @param string $filterMethod     The method of the $filterClass to call when the filter is executed
     * @param array  $fieldConstraints These constraints are an array of field keys and values that have to
     *                                 match in a table row in order for this service to call the renderer class.
     *
     * @return $this
     * @see \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService::registerActionHandler()
     */
    public function registerBackendSaveFilter(string $tableName, string $filterClass, string $filterMethod = 'filter', array $fieldConstraints = [])
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->registerActionHandler($this->getRealTableName($tableName), 'save', $filterClass, $filterMethod, $fieldConstraints);
        return $this;
    }
    
    /**
     * Can be used to remove a previously registered save filter from a table.
     *
     * Note: This method also removes filters even if the filter is registered after calling this method.
     * The filter than is blacklisted!
     *
     * @param string $tableName    The name of the table to remove the filter from
     * @param string $filterClass  The full name of the class containing the filter
     * @param string $filterMethod The method of the $filterClass which should no longer be called when the filter is
     *                             executed
     *
     * @return $this
     */
    public function removeBackendSaveFilter(string $tableName, string $filterClass, string $filterMethod = 'filter')
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->removeActionHandler($this->getRealTableName($tableName), 'save', $filterClass, $filterMethod);
        return $this;
    }
    
    /**
     * Register a new backend form filter for a table.
     *
     * This filter can be used to filter the tca as well as the as the raw table data when the backend builds a form
     * using the form engine. The event contains all the data that are passed to objects that implement the
     * FormDataProviderInterface interface.
     *
     * @param string $tableName        The name of the table to register the filter for
     * @param string $filterClass      The full name of the class containing the filter
     * @param string $filterMethod     The method of the $filterClass to call when the filter is executed
     * @param array  $fieldConstraints These constraints are an array of field keys and values that have to
     *                                 match in a table row in order for this service to call the renderer class.
     *
     * @return $this
     * @see \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService::registerActionHandler()
     */
    public function registerBackendFormFilter(string $tableName, string $filterClass, string $filterMethod = 'filter', array $fieldConstraints = [])
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->registerActionHandler($this->getRealTableName($tableName), 'form', $filterClass, $filterMethod, $fieldConstraints);
        return $this;
    }
    
    /**
     * Can be used to remove a previously registered form filter from a table.
     *
     * @param string $tableName    The name of the table to remove the filter from
     * @param string $filterClass  The full name of the class containing the filter
     * @param string $filterMethod The method of the $filterClass which should no longer be called when the filter is
     *                             executed
     *
     * @return $this
     */
    public function removeBackendFormFilter(string $tableName, string $filterClass, string $filterMethod = 'filter')
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->removeActionHandler($this->getRealTableName($tableName), 'form', $filterClass, $filterMethod);
        return $this;
    }
    
    /**
     * The registered method is called every time the backend performs an action. Actions are deletion,
     * translation, copy or moving of a record and many others.
     *
     * @param string $tableName        The name of the table to register the handler for
     * @param string $handlerClass     The full name of the class containing the handler
     * @param string $handlerMethod    The method of the $filterClass to call when the filter is executed
     * @param array  $fieldConstraints These constraints are an array of field keys and values that have to
     *                                 match in a table row in order for this service to call the renderer class.
     *
     * @return $this
     * @see \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService::registerActionHandler()
     */
    public function registerBackendActionHandler(string $tableName, string $handlerClass, string $handlerMethod = 'handle', array $fieldConstraints = [])
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->registerActionHandler($this->getRealTableName($tableName), 'default', $handlerClass, $handlerMethod, $fieldConstraints);
        return $this;
    }
    
    /**
     * Removes a previously registered backend action handler from the table.
     *
     * @param string $tableName     The name of the table to remove the filter from
     * @param string $handlerClass  The full name of the class containing the handler
     * @param string $handlerMethod The method of the $handlerClass which should no longer be called when the handler
     *                              stack is executed
     *
     * @return $this
     */
    public function removeBackendActionHandler(string $tableName, string $handlerClass, string $handlerMethod = 'handle')
    {
        if (!$this->context->TypoContext->getEnvAspect()->isBackend()) {
            return $this;
        }
        $this->context->DataHandlerActions->removeActionHandler($this->getRealTableName($tableName), 'default', $handlerClass, $handlerMethod);
        return $this;
    }
    
    /**
     * By default tables are only allowed in "folder" elements. If you want to allow a table on
     * default "pages" as well supply the name of the table and we handle the rest...
     *
     * @param string $tableName
     *
     * @return $this
     */
    public function allowTableOnStandardPages(string $tableName): TableOption
    {
        $this->tablesOnStandardPages[] = $this->getRealTableName($tableName);
        return $this;
    }
    
    /**
     * Can be used to configure the order of tables when they are rendered in the "list" mode in the backend.
     * The table with $tableName will be sorted either before or after the table with $otherTableName
     *
     * @param string $tableName      The table name to be configured
     * @param string $otherTableName The table to relatively position this one to
     * @param bool   $before         True by default, if set to false the table will be shown after the $otherTableName
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\TableOption
     */
    public function registerTableListPosition(string $tableName, string $otherTableName, bool $before = true): TableOption
    {
        $this->tableListPositions[$tableName][$before ? 'before' : 'after'][] = $otherTableName;
        return $this;
    }
    
    /**
     * Internal event handler to run the "new tables" generator stack, when the TCA files were loaded
     */
    public function __applyTableTca()
    {
        // Flush the sql builder
        $this->context->SqlGenerator->flush();
        
        // Build and apply the table registration tca
        $this->applyTableTca(false);
    }
    
    /**
     * Internal event handler to run the "modify tables" generator stack, when the TCA override files were loaded.
     * Will also persist some generated values into a cache for the appliers that are required on every execution.
     */
    public function __applyTableTcaOverrides()
    {
        // Build and apply the table override tca
        $this->applyTableTca(true);
        
        // Register mapping typoScript
        $typoScript = $this->getCachedValueOrRun('tableMappingTypoScript', function () {
            // Get the table config ts
            $ts = $this->getTableConfig()->typoScript;
            
            // Get our local ts
            foreach ($this->tableModels as $tableName => $modelList) {
                $ts .= PHP_EOL . $this->getPersistenceTs($modelList, $tableName);
            }
            
            // Done
            return $ts;
        });
        
        $this->context->TypoScript->addSetup($typoScript, [
            'title' => 'BetterApi - Table-Model Mapping',
        ]);
    }
    
    /**
     * @inheritDoc
     */
    public function __applyExtTables()
    {
        // Allow tables on standard pages
        $tablesOnStandardPages = $this->getCachedValueOrRun('tablesOnStandardPages', function () {
            return $this->getTableConfig()->tablesOnStandardPages;
        });
        foreach ($tablesOnStandardPages as $tableName) {
            ExtensionManagementUtility::allowTableOnStandardPages($tableName);
        }
        foreach ($this->tablesOnStandardPages as $tableName) {
            ExtensionManagementUtility::allowTableOnStandardPages($tableName);
        }
        
        // Add page ts config for table ordering when in backend
        if ($this->context->TypoContext->getEnvAspect()->isBackend()) {
            $this->context->TypoScript->addPageTsConfig($this->getTableListOrderTsConfig());
        }
    }
    
    /**
     * Internal event handler which is called in the install tool when the sql schema is validated
     * It injects our compiled sql code for typo3 to use
     *
     * @param \LaborDigital\Typo3BetterApi\Event\Events\SqlDefinitionFilterEvent $event
     */
    public function __applySqlExtension(SqlDefinitionFilterEvent $event)
    {
        $event->addNewDefinition($this->getTableConfig()->sql);
    }
    
    /**
     * Internal helper which is used to unfold the "..." prefixed table names to a ext base, default table name
     *
     * @param string $tableName
     *
     * @return string
     */
    public function getRealTableName(string $tableName): string
    {
        $tableName = trim($tableName);
        if (substr($tableName, 0, 3) !== '...') {
            return $tableName;
        }
        return implode('_', array_filter(['tx', Naming::flattenExtKey($this->context->getExtKey()),
                                          'domain', 'model', strtolower(Inflector::toCamelBack(substr($tableName, 3))),
        ]));
    }
    
    /**
     *
     * Internal helper that is used if there was no table name given.
     * In that case we will use the config class as naming base and try to extract the plugin name out of it.
     *
     * We will automatically strip suffixes like table, ext, config, configuration, controller and override(s)
     * from the base name before we convert it into a plugin name
     *
     * @param string $configClass
     *
     * @return string
     */
    public function getTableNameFromConfigClass(string $configClass): string
    {
        $baseName = Path::classBasename($configClass);
        $baseName = preg_replace('~(Tables?)?(Ext)?(Config|Configuration)?(Overrides?)?$~', '', $baseName);
        return '...' . $baseName;
    }
    
    /**
     * Internal helper that executes the stack of table configurations and applies the resulting TCA modifications to
     * the TCA array. This method runs twice. Once for the normal TCA files and once for the TCA.overrides files.
     *
     * @param bool $overrides Should be true if the stack for the overrides is executed
     */
    protected function applyTableTca(bool $overrides)
    {
        $generator = $this->context->getInstanceOf(TableConfigGenerator::class);
        $tableTcaList = $generator->generateTableTcaList($this->getCachedStackDefinitions('tables', $overrides), $this->context, $overrides);
        
        foreach ($tableTcaList as $table => $tca) {
            $GLOBALS['TCA'][$table] = $tca;
        }
    }
    
    /**
     * Returns the instance of the table configuration.
     * The object may be cached for better performance.
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\TableConfig
     */
    protected function getTableConfig(): TableConfig
    {
        return $this->getCachedValueOrRun('tableConfig', function () {
            return $this->context->getInstanceOf(TableConfigGenerator::class)->generateTableConfig($this->context);
        });
    }
    
    /**
     * Is used to build the ts config string that is required to define the order of tables in the backend's list view
     * @return string
     */
    protected function getTableListOrderTsConfig(): string
    {
        return $this->getCachedValueOrRun('tableListOrder', function () {
            $ts = [];
            $config = Arrays::merge($this->tableListPositions, $this->getTableConfig()->tableListPositions);
            foreach ($config as $table => $c) {
                $c = Arrays::merge(['before' => [], 'after' => []], $c);
                $c['before'] = array_unique($c['before']);
                $c['after'] = array_unique($c['after']);
                
                $tsLocal = [];
                $tsLocal[] = 'mod.web_list.tableDisplayOrder.' . $table . ' {';
                if (!empty($c['before'])) {
                    $tsLocal[] = 'before = ' . implode(', ', $c['before']);
                }
                if (!empty($c['after'])) {
                    $tsLocal[] = 'after = ' . implode(', ', $c['after']);
                }
                $tsLocal[] = '}';
                $ts[] = implode(PHP_EOL, $tsLocal);
            }
            return implode(PHP_EOL, $ts);
        });
    }
}
