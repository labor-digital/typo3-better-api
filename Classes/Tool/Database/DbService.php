<?php
declare(strict_types=1);
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\Tool\Database;

use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class DbService implements SingletonInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use TypoContextAwareTrait;

    /**
     * Persists all current database changes
     * Simple wrapper around the extBase persistence manager
     */
    public function persistAll(): void
    {
        $this->getSingletonOf(PersistenceManagerInterface::class)->persistAll();
    }

    /**
     * Returns the instance of the database connection pool
     *
     * @return \TYPO3\CMS\Core\Database\ConnectionPool
     */
    public function getConnectionPool(): ConnectionPool
    {
        if ($this->hasLocalSingleton(ConnectionPool::class)) {
            return $this->getSingletonOf(ConnectionPool::class);
        }

        $connectionPool = $this->getWithoutDi(ConnectionPool::class);
        $this->setLocalSingleton(ConnectionPool::class, $connectionPool);

        return $connectionPool;
    }

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
    public function getQueryBuilder(?string $tableName = null, ?string $connectionName = null): QueryBuilder
    {
        if (! empty($tableName)) {
            $connection = $this->getConnectionPool()->getConnectionForTable($tableName);
        } else {
            $connection = $this->getConnection($connectionName);
        }

        $qb = $connection->createQueryBuilder();
        if (! empty($tableName)) {
            $qb->from($tableName);
        }

        return $qb;
    }

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
     * @return StandaloneBetterQuery
     */
    public function getQuery(string $tableName, bool $disableDefaultConstraints = false): StandaloneBetterQuery
    {
        if (class_exists($tableName)) {
            $tableName = NamingUtil::resolveTableName($tableName);
        }

        $query = $this->getWithoutDi(
            StandaloneBetterQuery::class, [
                $tableName,
                $this->getQueryBuilder($tableName),
                $this->getTypoContext()->di()->getObjectManager()->get(QuerySettingsInterface::class),
                $this->getTypoContext(),
                $this->getSingletonOf(Session::class),
            ]
        );

        if ($disableDefaultConstraints) {
            $query = $query->withIncludeHidden()->withIncludeDeleted()->withLanguage(false);
        }

        return $query;
    }

    /**
     * Returns the instance of a database connection
     * BE CAREFUL WITH THIS!
     *
     * @param   string|null  $connectionName  Optional selector to retrieve a specific connection.
     *                                        If omitted the default connection will be returned
     *
     * @return \TYPO3\CMS\Core\Database\Connection
     */
    public function getConnection(?string $connectionName = null): Connection
    {
        return $this->getConnectionPool()
                    ->getConnectionByName($connectionName ?? ConnectionPool::DEFAULT_CONNECTION_NAME);
    }
}
