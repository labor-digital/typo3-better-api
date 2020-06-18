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
 * Last modified: 2020.03.20 at 00:35
 */

namespace LaborDigital\Typo3BetterApi\Domain\DbService;

use LaborDigital\Typo3BetterApi\Domain\BetterQuery\StandaloneBetterQuery;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

interface DbServiceInterface
{
    /**
     * Helper to execute a raw sql query to the default connection.
     *
     * @param   string  $query  The mysql query to execute. Can have "?" or named parameters (typo >=8)
     * @param   array   $args   The parameters to be used in the query
     *
     * @return array|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function query(string $query, array $args = []);
    
    /**
     * Helper to execute multiple queries inside a transaction.
     * The rollback will be automatically handled if one of the queries failed
     *
     * @param   iterable  $queries  A list of mysql queries you would supply to query()
     * @param   array     $args     A list of arguments for each query. Supply null when you have queries with and
     *                              without args
     *
     * @return mixed
     */
    public function multiQuery(iterable $queries, array $args = []);
    
    /**
     * Returns the instance of the database connection
     * BE CAREFUL WITH THIS!
     *
     * @param   string|null  $connectionName
     *
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    public function getConnection(?string $connectionName = null);
    
    /**
     * Persists all current database changes
     * Simple wrapper around the persistence manager
     */
    public function persistAll();
    
    /**
     * Simple link to typo3's select query.
     *
     * @param   string             $table    A table to select from
     * @param   string|array|null  $uid      Either a single uid, or an array of uids to select
     *                                       If null is given the uid field will be ignored
     * @param   string             $fields   A list of fields to select or '*' for all
     * @param   string             $where    An additional where clause
     * @param   string             $orderBy  The order by string to add to the query
     * @param   string             $limit    A limit of how many rows to return
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     * @deprecated use getQuery instead!
     * @see        \LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceInterface::getQuery()
     */
    public function getRecords(string $table, $uid, $fields = '*', $where = '', $orderBy = '', $limit = ''): array;
    
    /**
     * Helper to debug a typo3 query. Will render the sql statement, the result, and exceptions to the screen to see.
     *
     * @param   QueryInterface  $query  The query to debug
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function debugQuery($query);
    
    /**
     * Creates a new query builder instance either for a table or a connection name.
     * If the table name is given the connection name will be ignored.
     *
     * @param   string|null  $tableName       The name of the table you want a query builder for, null if you want to
     *                                        specify a connection name
     * @param   string|null  $connectionName  The name of the connection to create the query builder for
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    public function getQueryBuilder(?string $tableName = null, ?string $connectionName = null): QueryBuilder;
    
    /**
     * Creates a new instance of a better query object for the given table name.
     * Better Query is a simplified, speaking wrapper around the doctrine query builder fused with the
     * functionality of the ext base domain model settings.
     *
     * The main use case is to create select queries but you can also use the better query object to prepare
     * other sql actions by preparing the query builder
     *
     * @param   string  $tableName                  The name of the table you want to
     * @param   bool    $disableDefaultConstraints  If this is set to true all constraints will be disabled
     *                                              by default and you have to enable them explicitly
     *
     * @return \LaborDigital\Typo3BetterApi\Domain\BetterQuery\StandaloneBetterQuery
     */
    public function getQuery(string $tableName, bool $disableDefaultConstraints = false): StandaloneBetterQuery;
}
