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


namespace LaborDigital\T3ba\ExtConfigHandler\Table;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\Traits\DelayedConfigExecutionTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Dumper;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;

class Loader implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;
    use DelayedConfigExecutionTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Dumper
     */
    protected $tableDumper;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    protected $configContext;
    
    public function __construct(
        TableFactory $tableFactory,
        Dumper $tableDumper,
        ExtConfigContext $configContext
    )
    {
        $this->tableFactory = $tableFactory;
        $this->tableDumper = $tableDumper;
        $this->configContext = $configContext;
    }
    
    /**
     * Executes the TCA extension for the normal table definitions
     */
    public function loadTables(): void
    {
        $this->executeLoad('default');
    }
    
    /**
     * Executes the TCA extension for the table override definitions
     */
    public function loadTableOverrides(): void
    {
        $this->executeLoad('override');
    }
    
    /**
     * Internal handler to load the registered set of table configuration classes
     * and inject the configuration result into the "TCA" array
     *
     * @param   string  $definitionKey  "override" or "default" to define which table definitions to load
     */
    protected function executeLoad(string $definitionKey): void
    {
        $table = null;
        $this->runDelayedConfig(
            $this->getTypoContext()->config()->getConfigState(),
            $this->configContext,
            'tca.loadableTables.' . $definitionKey,
            function (string $className, string $tableName) use (&$table) {
                // We have to create a table within a certain extension namespace,
                // because we might need that information for the initialize() method call.
                // Why? For stuff like resolving the icon based on the vendor or similar operations.
                if ($table === null) {
                    $table = $this->tableFactory->create($tableName, $this->configContext);
                    $this->tableFactory->initialize($table);
                }
                
                call_user_func([$className, 'configureTable'], $table, $this->configContext);
                
                array_map(
                    static function (TcaTableType $type) {
                        $type->ignoreFieldIdIssues(false);
                    },
                    $table->getLoadedTypes()
                );
            },
            function (string $tableName) use (&$table) {
                if ($table !== null) {
                    $GLOBALS['TCA'][$tableName] = $this->tableDumper->dump($table);
                }
                $table = null;
            }
        );
    }
}
