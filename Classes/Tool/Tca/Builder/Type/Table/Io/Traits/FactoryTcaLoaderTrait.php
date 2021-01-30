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
 * Last modified: 2021.01.27 at 14:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Event\Tca\TableDefaultTcaFilterEvent;
use LaborDigital\T3BA\Event\Tca\TableFactoryTcaFilterEvent;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

trait FactoryTcaLoaderTrait
{

    /**
     * Either loads the tca for the given tca table from the global configuration or creates,
     * a new, default configuration for it.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return array
     */
    public function getTca(TcaTable $table): array
    {
        // Load the tca from globals...
        $tca = Arrays::getPath($GLOBALS, ['TCA', $table->getTableName()], []);

        // ... or find the default tca
        if (empty($tca)) {
            $tca = $this->generateDefaultTca($table);
        }

        // Allow filtering
        $table->getContext()->cs()->eventBus->dispatch(($e = new TableFactoryTcaFilterEvent($tca, $table)));

        return $e->getTca();
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
        $ctx->cs()->typoContext->path()->getExtensionIconPath($ctx->parent()->getExtKey());
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
