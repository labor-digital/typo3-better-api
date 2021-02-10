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
 * Last modified: 2021.01.14 at 19:50
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Event\Tca\TableDefaultTcaFilterEvent;
use LaborDigital\T3BA\Event\Tca\TableFactoryTcaFilterEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Sql\SqlRegistry;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class TableFactory implements PublicServiceInterface
{
    use ContainerAwareTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \LaborDigital\T3BA\Tool\Sql\SqlRegistry
     */
    protected $sqlRegistry;

    /**
     * TableFactory constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory  $typeFactory
     * @param   \LaborDigital\T3BA\Tool\Sql\SqlRegistry                        $sqlRegistry
     */
    public function __construct(TypeFactory $typeFactory, SqlRegistry $sqlRegistry)
    {
        $this->typeFactory = $typeFactory;
        $this->sqlRegistry = $sqlRegistry;
    }

    /**
     * Creates a new, empty instance for a certain TCA table
     *
     * @param   string                                         $tableName
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext  $configContext
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable
     */
    public function create(string $tableName, ExtConfigContext $configContext): TcaTable
    {
        return $this->getWithoutDi(
            TcaTable::class, [
                $tableName,
                $this->getWithoutDi(TcaBuilderContext::class, [$configContext]),
                $this,
                $this->typeFactory,
            ]
        );
    }

    /**
     * Either loads the tca for the given tca table from the global configuration or creates,
     * a new, default configuration for it.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return void
     */
    public function initialize(TcaTable $table): void
    {
        // Load the tca from globals...
        $tca = Arrays::getPath($GLOBALS, ['TCA', $table->getTableName()], []);

        // ... or find the default tca
        if (empty($tca)) {
            $tca = $this->generateDefaultTca($table);

            // Make sure new tables are registered in the SQL generation
            $this->sqlRegistry->getTable($table->getTableName());
        }

        // Allow filtering
        $table->getContext()->cs()->eventBus->dispatch(($e = new TableFactoryTcaFilterEvent($tca, $table)));

        // Update the raw tca
        $table->setRaw($e->getTca());

        // We have to make sure that all types are loaded, so we can calculate
        // the registered data hooks correctly. I have hoped not to rely on this,
        // because it causes a lot of, potentially unnecessary, overhead.
        // So, if there is a better solution for handling the dataHooks tell me, please!
        foreach ($table->getTypeNames() as $typeName) {
            $table->getType($typeName);
        }
    }

    /**
     * Internal helper to generate a blank "default" TCA for a new table.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return array
     */
    protected function generateDefaultTca(TcaTable $table): array
    {
        $ctx       = $table->getContext();
        $tableName = $table->getTableName();

        $default                  = TableDefaults::TABLE_TCA;
        $default['ctrl']['title'] = Inflector::toHuman(
            preg_replace('/^(.*?_domain_model_)/', '', $tableName)
        );

        $default['ctrl']['iconfile']
            = $this->cs()->typoContext->path()->getExtensionIconPath(
            $ctx->parent()->getExtKey());

        $default['columns']['l10n_parent']['config']['foreign_table']       = $tableName;
        $default['columns']['l10n_parent']['config']['foreign_table_where'] = str_replace(
            '{{table}}',
            $tableName,
            $default['columns']['l10n_parent']['config']['foreign_table_where']
        );

        // Allow filtering
        $ctx->cs()->eventBus->dispatch(($e = new TableDefaultTcaFilterEvent($default, $table)));

        return $e->getDefaultTca();
    }
}
