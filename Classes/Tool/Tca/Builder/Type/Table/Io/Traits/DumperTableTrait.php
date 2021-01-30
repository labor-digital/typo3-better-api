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
 * Last modified: 2021.01.28 at 14:59
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

trait DumperTableTrait
{
    /**
     * Dumps the sql definition for this table into the TCA
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     * @param   array                                                    $tca
     */
    protected function dumpSql(TcaTable $table, array &$tca): void
    {
        $tca['@sql'] = $table->getContext()->cs()->sqlBuilder->getTableSql($table->getTableName());
    }
}
