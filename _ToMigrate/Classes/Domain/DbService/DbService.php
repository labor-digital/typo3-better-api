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
 * Last modified: 2020.03.20 at 16:37
 */

namespace LaborDigital\Typo3BetterApi\Domain\DbService;

use Exception;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\StandaloneBetterQuery;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class DbService implements DbServiceInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected $container;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     */
    protected $lazyPersistenceManager;
    
    /**
     * DbService constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     * @param   \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface     $lazyPersistenceManager
     */
    public function __construct(TypoContainerInterface $container, PersistenceManagerInterface $lazyPersistenceManager)
    {
        $this->container              = $container;
        $this->lazyPersistenceManager = $lazyPersistenceManager;
    }
    
    /**
     * @inheritDoc
     */
    public function persistAll()
    {
        $this->lazyPersistenceManager->persistAll();
    }
    
    /**
     * @inheritDoc
     */
    public function getQueryBuilder(?string $tableName = null, ?string $connectionName = null): QueryBuilder
    {
        if (! empty($tableName)) {
            $connection = $this->container->get(ConnectionPool::class)->getConnectionForTable($tableName);
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
     * @inheritDoc
     */
    public function getQuery(string $tableName, bool $disableDefaultConstraints = false): StandaloneBetterQuery
    {
        $queryBuilder = $this->getQueryBuilder($tableName);
        $query        = $this->container->get(StandaloneBetterQuery::class, ['args' => [$tableName, $queryBuilder]]);
        if ($disableDefaultConstraints) {
            $query = $query->withIncludeHidden()->withIncludeDeleted()->withLanguage(false);
        }
        
        return $query;
    }
    
    /**
     * @inheritDoc
     * @deprecated
     */
    public function query(string $query, array $args = [])
    {
        // Get connection
        $connection = $this->getConnection();
        // Execute query
        $statement = $connection->executeQuery($query, $args);
        if ($statement->columnCount() > 0) {
            return $statement->fetchAll();
        }
        
        return true;
    }
    
    /**
     * @inheritDoc
     * @deprecated
     */
    public function multiQuery(iterable $queries, array $args = [])
    {
        $connection = $this->getConnection();
        $result     = [];
        $c          = 0;
        try {
            $connection->beginTransaction();
            foreach ($queries as $key => $query) {
                $a = $c++ === 0 ? current($args) : next($args);
                if (! is_string($query)) {
                    continue;
                }
                if (empty($a)) {
                    $a = [];
                }
                $result[] = $this->query($query, $a);
            }
            $connection->commit();
        } catch (Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw $e;
        }
        
        return $result;
    }
    
    /**
     * @inheritDoc
     */
    public function getConnection(?string $connectionName = null)
    {
        /** @var ConnectionPool $pool */
        $pool = $this->container->get(ConnectionPool::class);
        
        return $pool->getConnectionByName(empty($connectionName) ? ConnectionPool::DEFAULT_CONNECTION_NAME
            : $connectionName);
    }
    
    /**
     * @inheritDoc
     */
    public function getRecords(string $table, $uid, $fields = '*', $where = '', $orderBy = '', $limit = ''): array
    {
        $uid = Arrays::makeFromStringList($uid);
        if (! empty($uid)) {
            $where = 'uid IN (' . implode(',', $uid) . ') ' . $where;
        }
        // Get connection
        $connection = $this->getConnection();
        $builder    = $connection->createQueryBuilder();
        $builder->select(...Arrays::makeFromStringList($fields));
        $builder->from($table);
        $builder->where($where);
        
        // Prepare order by
        if (! empty($orderBy)) {
            $orderByParts = explode(',', $orderBy);
            foreach ($orderByParts as $part) {
                $parts = array_filter(array_map('trim', explode(' ', $part)));
                $builder->addOrderBy(array_shift($parts), array_shift($parts));
            }
        }
        
        // Add Limit
        if (! empty($limit)) {
            $builder->setMaxResults((int)$limit);
        }
        
        // Execute query
        $result = $builder->execute()->fetchAll();
        
        return ! is_array($result) ? [] : $result;
    }
    
    /**
     * @inheritDoc
     */
    public function debugQuery($query)
    {
        $result       = $exception = $count = null;
        $isStandalone = false;
        if ($query instanceof BetterQuery) {
            $query = $query->getQuery();
        }
        if ($query instanceof QueryResult) {
            $query = $query->getQuery();
        }
        if (! $query instanceof QueryInterface) {
            if (! $query instanceof StandaloneBetterQuery) {
                throw new DbServiceException('The given query object can not be used!');
            }
            $dQuery       = $query->getQueryBuilder();
            $isStandalone = true;
        } else {
            $parser = $this->container->get(Typo3DbQueryParser::class);
            $dQuery = $parser->convertQueryToDoctrineQueryBuilder($query);
            if (! empty($query->getLimit())) {
                $dQuery->setMaxResults($query->getLimit());
            }
            if (! empty($query->getOffset())) {
                $dQuery->setFirstResult($query->getOffset());
            }
        }
        
        // Build the query
        $queryString = $dQuery->getSQL();
        
        // Prepare query with parameters
        $in = $out = [];
        foreach ($dQuery->getParameters() as $k => $v) {
            $in[]  = ':' . $k;
            $out[] = '"' . addslashes($v) . '"';
        }
        $queryString = str_replace($in, $out, $queryString);
        
        // Try to execute the message
        try {
            if ($isStandalone) {
                $first  = $dQuery->getFirstResult();
                $result = $dQuery->execute();
            } else {
                $first  = $query->execute()->getFirst();
                $result = $query->execute(true);
            }
        } catch (Exception $e) {
            $exception = $e->getMessage();
        }
        
        // Show general query information
        echo '<h5>Query string</h5>';
        if (function_exists('dbg')) {
            dbg($queryString);
        } else {
            DebuggerUtility::var_dump($queryString);
        }
        
        try {
            if (! empty($exception)) {
                echo '<h5>Db Errors</h5>';
                DebuggerUtility::var_dump($exception);
            }
            echo '<h5>Query Object</h5>';
            DebuggerUtility::var_dump($query);
            if (isset($first) && ! empty($first)) {
                echo '<h5>First result entity</h5>';
                DebuggerUtility::var_dump($first);
            }
            echo '<h5>Raw result</h5>';
            DebuggerUtility::var_dump($result);
            echo '<h5>Db Connection</h5>';
            DebuggerUtility::var_dump($GLOBALS['TYPO3_DB']);
        } catch (Exception $e) {
            echo '<h2>Db Error!</h2>';
            DebuggerUtility::var_dump($e);
        }
        exit();
    }
}
