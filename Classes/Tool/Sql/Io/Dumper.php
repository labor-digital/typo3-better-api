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
 * Last modified: 2021.02.08 at 22:59
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Sql\Io;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Event\Sql\CreateTableStatementFilterEvent;

class Dumper
{
    use ContainerAwareTrait;

    /**
     * Dumps the given list of tables as a single SQL string.
     * This is normally the output of DefinitionProcessor::findTableDiff()
     *
     * @param   Table[]  $tables
     *
     * @return string
     * @see \LaborDigital\T3BA\Tool\Sql\Io\DefinitionProcessor::findTableDiff()
     */
    public function dump(array $tables): string
    {
        $sql        = [];
        $tableNames = [];

        foreach ($tables as $table) {
            $statement = $this->generateSqlForTable($table);

            if (! empty($statement)) {
                $tableNames[] = $table->getName();
                $sql[]        = $statement;
            }
        }

        $statement = implode(PHP_EOL, $sql);

        return $this->cs()->eventBus
            ->dispatch(new CreateTableStatementFilterEvent($tableNames, $statement))
            ->getStatement();
    }


    /**
     * Generates the "CREATE TABLE" string for a single table
     *
     * @param   \Doctrine\DBAL\Schema\Table  $table
     *
     * @return string
     */
    protected function generateSqlForTable(Table $table): ?string
    {
        $schema = $this->makeInstance(Schema::class, [[$table]]);
        $sql    = $schema->toSql($this->cs()->db->getConnection()->getDatabasePlatform());
        $sql    = $sql[0] ?? '';

        if (empty($sql)) {
            return null;
        }

        $sql = '--' . PHP_EOL .
               '-- Table structure for table \'' . $table->getName() . '\'' . PHP_EOL .
               '--' . PHP_EOL .
               $sql .
               ';';

        return $sql;
    }


}
