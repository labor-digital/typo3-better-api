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
 * Last modified: 2021.07.16 at 19:41
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Configuration\Table\Override;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Table\ConfigureTcaTableInterface;
use LaborDigital\T3ba\ExtConfigHandler\Table\TcaTableNameProviderInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

class TtContentTable implements ConfigureTcaTableInterface, TcaTableNameProviderInterface
{
    
    /**
     * @inheritDoc
     */
    public static function getTableName(): string
    {
        return 'tt_content';
    }
    
    /**
     * @inheritDoc
     */
    public static function configureTable(TcaTable $table, ExtConfigContext $context): void
    {
        // In order to create our virtual colPos for inline content elements
        // we have to ensure that colPos is signed and therefore need to add it first to our schema
        // if not already done by another table definition
        $schema = $table->getSchema();
        if (! $schema->hasColumn('colPos', true)) {
            $schema->addColumn('colPos', 'integer')->setDefault('0')->setLength(11);
        }
        $schema->getColumn('colPos', true)->setUnsigned(false);
    }
    
}