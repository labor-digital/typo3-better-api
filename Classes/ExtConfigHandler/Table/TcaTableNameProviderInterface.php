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

/**
 * Interface TcaTableNameProviderInterface
 *
 * Extension for the ConfigureTcaTableInterface to provide a specific table name that should not be
 * inferred by the tables PHP namespace.
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Table
 * @see     \LaborDigital\T3ba\ExtConfigHandler\Table\ConfigureTcaTableInterface
 */
interface TcaTableNameProviderInterface
{
    
    /**
     * Must return the name of the table you want to configure with your configuration class
     *
     * @return string
     */
    public static function getTableName(): string;
}
