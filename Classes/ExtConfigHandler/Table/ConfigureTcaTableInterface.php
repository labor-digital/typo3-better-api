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
 * Last modified: 2021.01.13 at 19:35
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

/**
 * Interface ConfigureTcaTableInterface
 *
 * Allows you to configure the Table Configuration Array (TCA) of a specific database table.
 * The name of the table will be automatically inferred by the PHP namespace.
 *
 * @package LaborDigital\T3BA\ExtConfigHandler\Table
 *
 * @see     \LaborDigital\T3BA\ExtConfigHandler\Table\TcaTableNameProviderInterface Allows you to define a specifc
 *          table name without being dependent on the PHP namespace.
 */
interface ConfigureTcaTableInterface
{
    
    /**
     * Should be used to configure the table that is passed to it
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext            $context
     */
    public static function configureTable(TcaTable $table, ExtConfigContext $context): void;
}
