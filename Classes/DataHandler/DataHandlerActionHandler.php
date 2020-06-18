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
 * Last modified: 2020.03.21 at 22:01
 */

namespace LaborDigital\Typo3BetterApi\DataHandler;

use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbService;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormActionContextFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BackendFormActionPostProcessorEvent;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\EventBusInterface;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerActionHandler implements SingletonInterface, DataHandlerActionHandlerInterface
{
    
    /**
     * I leave this open if you want to add your own, custom mappings, feel free to do so...
     * The map from stack type to the context element class to generate
     *
     * @var array
     */
    public static $stackTypeContextMap
        = [
            'default' => DataHandlerActionContext::class,
        ];
    
    /**
     * This is open for adjustments as well, as you will probably need it when you start adding new context types...
     * It is the map of a stack type to the tca config option to look for
     *
     * @var array
     */
    public static $stackTypeConfigKeyMap
        = [
            'default'  => 'actionHandlers',
            'form'     => 'formFilterHandlers',
            'save'     => 'saveFilterHandlers',
            'saveLate' => 'saveLateFilterHandlers',
        ];
    
    /**
     * Is used to store the generated context lists when we are running on the backendActionFilter and backendAction
     * hook. Otherwise we would needlessly create overhead, by loading the context list twice from the tca...
     *
     * @var array
     */
    protected $contextListCache = [];
    
    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Domain\DbService\DbService
     */
    protected $dbService;
    
    /**
     * @var FlexFormTools
     */
    protected $flexFormTools;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService
     */
    protected $backendActionService;
    
    /**
     * BackendActionHandler constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     * @param   \Neunerlei\EventBus\EventBusInterface                          $eventBus
     * @param   \LaborDigital\Typo3BetterApi\Domain\DbService\DbService        $dbService
     * @param   \TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools           $flexFormTools
     */
    public function __construct(
        TypoContainerInterface $container,
        EventBusInterface $eventBus,
        DbService $dbService,
        FlexFormTools $flexFormTools
    ) {
        $this->eventBus      = $eventBus;
        $this->container     = $container;
        $this->dbService     = $dbService;
        $this->flexFormTools = $flexFormTools;
    }
    
    /**
     * Inject the backend action service
     *
     * @param   \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService  $backendActionService
     */
    public function injectBackendActionService(DataHandlerActionService $backendActionService)
    {
        $this->backendActionService = $backendActionService;
    }
    
    /**
     * This method can be used to traverse the TCA of a given table for a list of callable handlers.
     * It is in general agnostic to the type of handler it will execute. Use the $stackType argument in combination
     * with the static $stackTypeConfigKeyMap property to define which handler stack should be executed.
     *
     * The method is by default used to traverse the TCA of records for backend save filters, backend action handlers
     * and backend form filters. It is called in the BackendFormEventHandler class on multiple occasions in the
     * backend.
     *
     * It will also traverse flex forms that are defined on a table for possible handlers in it's definition and
     * add them to the execution stack.
     *
     * After it found all handlers and created a context configuration for each of them (a context configuration is a
     * lot of metadata for the next step to come)
     *
     * The context configuration's then will be converted into a context object which in turn is passed to each of the
     * registered handlers. All handler's can make changes to the linked value, based on the context (either the field,
     * or the table) they are bound to.
     *
     * @param   string      $stackType  The type of stack we should find the handlers for
     * @param   string      $tableName  The name of the table, which TCA we should traverse for handlers.
     * @param   string|int  $uid        The uid of the record we are executing the handlers for.
     * @param   object      $event      The typo3 event that lead to the call of this method.
     *                                  If you would ever want to create your own stack you should be able to pass
     *                                  a manually instantiated object here, as it is merely passed to the context
     *                                  object to provide additional information to the handlers
     * @param   array       $row        A database row, or a fraction of it, that corresponds with the given $uid.
     *                                  If this is empty, the method will try to request a fresh row from the database
     *                                  based on the given $uid. If any changes where made this reference will reflect
     *                                  all changes.
     * @param   bool        $isDirty    This reference is true if there were any changes made on the given $row
     */
    public function runActionStack(
        string $stackType,
        string $tableName,
        $uid,
        object $event,
        array &$row,
        &$isDirty = false
    ) {
        $isDirty = false;
        
        // Backup the original row
        $givenRow = array_map(function ($v) {
            return $v;
        }, $row);
        
        // Make sure the row is not empty
        $rowType = '';
        if (empty($row) && is_numeric($uid) && Arrays::hasPath($GLOBALS, ['TCA', $tableName])) {
            $query = $this->dbService->getQuery('tt_content', true);
            $rows  = $query->withWhere(['uid' => $uid])->getAll();
            $row   = count($rows) > 0 ? reset($rows) : [];
        } else {
            $rowType = BackendUtility::getTCAtypeValue($tableName, $row);
        }
        
        // Backup the original row
        $rawRow = array_map(function ($v) {
            return $v;
        }, $row);
        
        // Load the tca for the table
        $tca        = Arrays::getPath($GLOBALS, ['TCA', $tableName], []);
        $tcaColumns = Arrays::getPath($tca, ['columns'], []);
        
        // Merge type tca if required
        if (! empty($rowType)) {
            $typeColumns = Arrays::getPath($GLOBALS, ['TCA', $tableName, 'types', $rowType, 'columnsOverrides'], []);
            ArrayUtility::mergeRecursiveWithOverrule($tcaColumns, $typeColumns);
            $tca['columns'] = $tcaColumns;
        }
        
        // Check if we can skip this whole ordeal...
        if ($this->hasContextEmptyListCacheFor($stackType, $uid, $tableName, $rowType)) {
            return;
        }
        
        // Unpack all flex form fields
        $unpackedFlexFormFields = [];
        foreach ($row as $k => $v) {
            if (! isset($tcaColumns[$k])) {
                continue;
            }
            if (Arrays::getPath($tcaColumns, [$k, 'config', 'type']) !== 'flex') {
                continue;
            }
            if (is_array($v)) {
                continue;
            }
            $unpackedFlexFormFields[] = $k;
            $unpacked                 = empty($v) ? [] : GeneralUtility::xml2array($v);
            if (! is_array($unpacked)) {
                $unpacked = [];
            }
            $row[$k] = $unpacked;
        }
        
        // Get the context list
        $contextList = $this->readContextListForStack($stackType, $uid, $row, $tableName, $tca, $rowType);
        
        // Be done if there are no contexts
        if (! empty($contextList)) {
            // Create the new context object
            $contextClass = isset(static::$stackTypeContextMap[$stackType]) ?
                static::$stackTypeContextMap[$stackType] : static::$stackTypeContextMap['default'];
            
            // Prepare the action key
            $action = method_exists($event, 'getCommand') ? $event->getCommand() : $stackType;
            
            // Call the methods of all contests
            foreach ($contextList as $contextConfig) {
                // Finish the context config
                $contextConfig['event']     = $event;
                $contextConfig['row']       = $row;
                $contextConfig['uid']       = $uid;
                $contextConfig['tableName'] = $tableName;
                $contextConfig['action']    = $action;
                
                // Special handling if the key is empty -> Means the handler was registered on a table
                $contextConfig['appliesToTable'] = empty($contextConfig['key']);
                if ($contextConfig['appliesToTable']) {
                    $contextConfig['key']   = $tableName;
                    $contextConfig['value'] = $row;
                }
                
                // Allow the outside world to change the context
                $this->eventBus->dispatch(($e = new BackendFormActionContextFilterEvent(
                    $contextClass,
                    $contextConfig,
                    $stackType
                )));
                $contextClassLocal = $e->getContextClass();
                
                // Create the context
                /** @var \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionContext $contextObject */
                $contextObject = $this->container->get($contextClassLocal);
                $contextObject->__setContextArray($contextConfig);
                
                // Call the registered method
                $i = $this->container->get($contextConfig['class']);
                call_user_func([$i, $contextConfig['method']], $contextObject);
                
                // Allow the outside world to interfere...
                $this->eventBus->dispatch(new BackendFormActionPostProcessorEvent($contextObject, $i, $stackType));
                
                // Check if we have to update the value
                if ($contextObject->isValueDirty()) {
                    // We are so dirty...
                    $isDirty = true;
                    
                    // Update the whole row if it applies to a table
                    if ($contextConfig['appliesToTable']) {
                        $row = $contextObject->getValue();
                    } // Update a single value
                    else {
                        $row = Arrays::setPath($row, $contextObject->getPath(), $contextObject->getValue());
                    }
                    
                    // Update the context row
                    $contextObject->__setContextArray(['row' => $row, 'valueDirty' => false]);
                }
            }
        }
        
        // Check if we have to repack some flex form fields
        if (! empty($unpackedFlexFormFields)) {
            foreach ($unpackedFlexFormFields as $field) {
                if (is_array($row[$field])) {
                    $row[$field] = $this->flexFormTools->flexArray2Xml($row[$field]);
                }
            }
        }
        
        // Make sure to only update the changed values
        $rowChanged = $givenRow;
        foreach ($row as $k => $v) {
            if ($v != $rawRow[$k]) {
                $rowChanged[$k] = $v;
            }
        }
        $row = $rowChanged;
    }
    
    /**
     * Internal helper which returns true if we currently have a cached context list for the given values
     * but the context list is empty. If that is the case we can save a lot of work by ignoring the
     * TCA lookup and the overhead of looking up the list row from the database
     *
     * @param   string  $stackType  The type of stack we are currently trying to find the contexts for
     * @param   mixed   $uid        The uid of the record we are trying to find contexts for
     * @param   string  $tableName  The table name of the record we are trying to find contexts for
     * @param   string  $type       If not an empty string it holds the TCA type of the current table, which is
     *                              responsible for the current record
     *
     * @return bool
     */
    protected function hasContextEmptyListCacheFor(string $stackType, $uid, string $tableName, string $type): bool
    {
        $cacheKey = md5($stackType . $uid . $tableName . $type);
        
        return isset($this->contextListCache[$cacheKey]) && empty($this->contextListCache[$cacheKey]);
    }
    
    /**
     * Traverses the given tca array in order to find possible contexts for the current stack type in it.
     * It will then return the list of all contexts it found back to the parent method.
     *
     * @param   string  $stackType  The type of stack we are currently trying to find the contexts for
     * @param   mixed   $uid        The uid of the record we are trying to find contexts for
     * @param   array   $row        The database row, or a fragment of the database row we are currently working with
     * @param   string  $tableName  The table name of the record we are trying to find contexts for
     * @param   array   $tca        The TCA array for the table defined in $tableName
     * @param   string  $type       If not an empty string it holds the TCA type of the current table, which is
     *                              responsible for the current record
     *
     * @return array
     */
    protected function readContextListForStack(
        string $stackType,
        $uid,
        array $row,
        string $tableName,
        array $tca,
        string $type
    ): array {
        // Check if we already got a context cached
        $cacheKey = md5($stackType . $uid . $tableName . $type);
        if (isset($this->contextListCache[$cacheKey])) {
            return $this->contextListCache[$cacheKey];
        }
        
        // Prepare the context list
        $contexts = [];
        
        // Check if the tca has any registered handlers
        if (is_array($tca['dataHandlerActions'])) {
            $contexts = $this->addContextsForStack($stackType, $tca['dataHandlerActions'], $contexts);
        }
        
        // Run through all columns in the given row
        foreach ($row as $key => $value) {
            // Check if there is a field configuration for this key
            if (! isset($tca['columns'][$key])) {
                continue;
            }
            $tcaField = $tca['columns'][$key];
            
            // Skip if there is no config
            if (! is_array($tcaField) || ! is_array($tcaField['config'])) {
                continue;
            }
            
            // Check if there is a config field in the field tca
            if (is_array($tcaField['dataHandlerActions'])) {
                $contexts = $this->addContextsForStack($stackType, $tcaField['dataHandlerActions'], $contexts, [$key],
                    $key, $value);
            }
            
            // Check if this is a flex form field
            // This is going to be a lot of work... isn't it?
            if ($tcaField['config']['type'] === 'flex') {
                $contexts = Arrays::attach($contexts,
                    $this->readContextListFromFlexForm($stackType, $value, $tcaField, $tableName, $key, $row));
            }
        }
        
        // Get the registered handlers from the backend service
        $handlers = $this->backendActionService->getHandlersFor($tableName, $stackType);
        if (! empty($handlers)) {
            $matchedHandlers = [];
            foreach ($handlers as $handler) {
                // Check if we match the constraints
                if (count(array_intersect_assoc($handler['constraints'], $row)) !== count($handler['constraints'])) {
                    continue;
                }
                $matchedHandlers[] = $handler['handler'];
            }
            
            // Register the handlers as context
            if (! empty($matchedHandlers)) {
                $contexts = $this->addContextsForStack('inject', ['inject' => $matchedHandlers], $contexts);
            }
        }
        
        // Store the generated contexts
        $this->contextListCache[$cacheKey] = $contexts;
        
        // Done
        return $contexts;
    }
    
    /**
     * This is a sub method of readContextListForStack() which was stripped out of the main method, to keep it somewhat
     * readable. It is responsible for reading contexts out of flex form fields.
     *
     * @param   string  $configKey  One key of static::$stackTypeConfigKeyMap which is responsible for the callback
     *                              definitions of the current stackType
     * @param   array   $value      The value of the flex form field we are currently traversing. The value should
     *                              already be parsed into an array, which is normally done at the top of
     *                              runActionStack()
     * @param   array   $tcaField   The tca configuration array of the flex form field
     * @param   string  $tableName  The table name of the record we are trying to find contexts for
     * @param   string  $key        The key of the flex form field we are currently parsing
     * @param   array   $row        The database row, or a fragment of the database row we are currently working with
     *
     * @return array
     */
    protected function readContextListFromFlexForm(
        string $configKey,
        array $value,
        array $tcaField,
        string $tableName,
        string $key,
        array $row
    ): array {
        // Holds the list of all custom element handlers we found
        $contexts = [];
        
        // Try to find the structure id
        try {
            $structureId    = $this->flexFormTools->getDataStructureIdentifier($tcaField, $tableName, $key, $row);
            $structureArray = $this->flexFormTools->parseDataStructureByIdentifier($structureId);
        } catch (Throwable $e) {
            return [];
        }
        
        // To avoid complex recursions we flatten the structure and search in the resulting
        // one dimensional array for our marker keys and use the path's to extract the data we need
        foreach (Arrays::flatten($structureArray) as $k => $v) {
            // Skip if this is not the marker for a custom element
            if (! strpos($k, '.dataHandlerActions.' . $configKey . '.')) {
                continue;
            }
            
            // Parse the path and get the parent element
            $path = Arrays::parsePath($k);
            if (end($path) !== '0') {
                continue;
            }
            array_pop($path);
            array_pop($path);
            if (end($path) !== $configKey) {
                continue;
            }
            array_pop($path);
            
            // Store the config
            $configPath = $path;
            $config     = Arrays::getPath($structureArray, $configPath);
            array_pop($path);
            array_pop($path);
            
            // Try to extract the real value out of the given list
            $pointer   = &$value;
            $valuePath = [];
            foreach ($path as $partId => $part) {
                // Kill the loop if the pointer is no array
                if (! is_array($pointer)) {
                    break;
                }
                
                // Translate special keys
                if (! isset($pointer[$part])) {
                    if ($part === 'sheets') {
                        $part = 'data';
                    } elseif ($part === 'ROOT') {
                        $part = 'lDEF';
                    }
                }
                
                // Link to the new pointer
                if (isset($pointer[$part])) {
                    $pointer     = &$pointer[$part];
                    $valuePath[] = $part;
                }
                
                // Check for an "el" => This means we are inside a section
                if (is_array($pointer) && is_array($pointer['el'])) {
                    foreach ($pointer['el'] as $k => $el) {
                        // Ignore broken structures
                        if (! is_array($el)) {
                            continue;
                        }
                        
                        // Loop over items
                        $valuePath[] = 'el';
                        $valuePath[] = $k;
                        foreach ($el as $_k => $item) {
                            // Ignore broken structures
                            if (! is_array($item) || ! isset($item['el'])) {
                                continue;
                            }
                            $valuePath[] = $_k;
                            $valuePath[] = 'el';
                            
                            // Loop over the remaining path
                            $remainingPath = array_slice($path, $partId + 4);
                            $requiredField = reset($remainingPath);
                            
                            // Store the value
                            $fieldPath = ['el', $k, $_k, 'el', $requiredField, 'vDEF'];
                            if (Arrays::hasPath($pointer, $fieldPath)) {
                                $fullValuePath = Arrays::attach([$key], $valuePath, [$requiredField, 'vDEF']);
                                $contexts      = $this->addContextsForStack($configKey, $config, $contexts,
                                    $fullValuePath, $requiredField, Arrays::getPath($pointer, $fieldPath));
                            }
                            
                            array_pop($valuePath);
                            array_pop($valuePath);
                        }
                        array_pop($valuePath);
                        array_pop($valuePath);
                    }
                }
            }
            
            // Store the value if it has a vDef as last element
            if (is_array($pointer) && isset($pointer['vDEF'])) {
                $fullValuePath = Arrays::attach([$key], $valuePath, ['vDEF']);
                $contexts      = $this->addContextsForStack($configKey, $config, $contexts, $fullValuePath,
                    end($valuePath), $pointer['vDEF']);
            }
        }
        
        // Done
        return $contexts;
    }
    
    /**
     * This internal helper is used to read the configuration in the TCA for the field that matches our
     * current config key. It will then add a new context for each handler it finds there and adds it to
     * the given array of contexts before returning the merged list.
     *
     * @param   string  $configKey  One key of static::$stackTypeConfigKeyMap which is responsible for the callback
     *                              definitions of the current stackType
     * @param   array   $config     The tca configuration array of the field we are currently trying to get contexts
     *                              for. If the context parent is the table itself, this should be the complete TCA
     *                              array
     * @param   array   $contexts   The current list of contexts we should add the new contexts to
     * @param   array   $path       The current path in the row of data which has to be used to retrieve / update this
     *                              field's value. This is mostly for traversing flex form structures with an
     *                              hierarchical layout.
     * @param   string  $fieldName  The name of the field we creating a new context for. If the context parent is the
     *                              table itself, this should be the name of the table
     * @param   null    $value      The value of the field the context is created for. If the context parent is the
     *                              table itself, this should be the same as $row
     *
     * @return array
     */
    protected function addContextsForStack(
        string $configKey,
        array $config,
        array $contexts,
        array $path = [],
        string $fieldName = '',
        $value = null
    ): array {
        // Ignore if the config does not have the required config key
        if (! is_array($config[$configKey])) {
            return $contexts;
        }
        
        // Loop over the stack of objects
        foreach ($config[$configKey] as $handler) {
            // Prepare the handler
            $handler = is_string($handler) ? array_values(Naming::typoCallbackToArray($handler)) : $handler;
            if (! is_array($handler) || count($handler) < 2) {
                continue;
            }
            $handler = array_values($handler);
            
            // Create the new context
            $contexts[] = [
                'key'       => $fieldName,
                'path'      => $path,
                'value'     => $value,
                'config'    => $config,
                'configKey' => $configKey,
                'class'     => $handler[0],
                'method'    => $handler[1],
            ];
        }
        
        // Done
        return $contexts;
    }
}
