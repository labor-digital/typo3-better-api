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

use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryTypo3DbQueryParserAdapter;
use LaborDigital\T3ba\Tool\Database\BetterQuery\ExtBase\ExtBaseBetterQuery;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3ba\Tool\Database\DatabaseException;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

if (! function_exists('dbgQuery')) {
    /**
     * Helper to debug a typo3 query. Will render the sql statement, the result, and exceptions to the screen to see.
     *
     * @param   QueryInterface|ExtBaseBetterQuery|StandaloneBetterQuery|QueryBuilder|QueryResultInterface  $query
     *
     * @throws \LaborDigital\T3ba\Tool\Database\DatabaseException
     */
    function dbgQuery($query)
    {
        $result = $exception = null;
        $isStandalone = false;
        if ($query instanceof ExtBaseBetterQuery) {
            $query = $query->getQuery();
        }
        if ($query instanceof QueryResultInterface) {
            $query = $query->getQuery();
        }
        if (! $query instanceof QueryInterface) {
            if (! $query instanceof QueryBuilder) {
                if (! $query instanceof StandaloneBetterQuery) {
                    throw new DatabaseException('The given query object can not be used!');
                }
                $dQuery = $query->getQueryBuilder();
            } else {
                $dQuery = $query;
            }
            $isStandalone = true;
        } else {
            $dQuery = BetterQueryTypo3DbQueryParserAdapter::getConcreteQueryParser()
                                                          ->convertQueryToDoctrineQueryBuilder($query);
            /** @noinspection SuspiciousBinaryOperationInspection */
            if ($query->getStatement() === null) {
                $dQuery->getRestrictions()->removeAll();
            }
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
            $in[] = ':' . $k;
            $out[] = '"' . addslashes((string)$v) . '"';
        }
        $queryString = str_replace($in, $out, $queryString);
        
        // Try to execute the message
        try {
            if ($isStandalone) {
                $result = $dQuery->execute()->fetchAllAssociative();
                $first = empty($result) ? null : reset($result);
            } else {
                $result = (clone $query)->execute(true);
                $first = (clone $query)->execute()->getFirst();
            }
        } catch (Exception $e) {
            $exception = $e->getMessage();
        }
        
        // Show general query information
        if (function_exists('dbg')) {
            dbg($queryString);
        } else {
            DebuggerUtility::var_dump($queryString, 'Query string');
        }
        
        try {
            if (php_sapi_name() !== 'cli') {
                if (! empty($exception)) {
                    DebuggerUtility::var_dump($exception, 'Db Errors');
                }
                try {
                    $args = [true, false, [Container::class, Session::class, TypoContext::class], ['session', 'caServices']];
                    ob_start();
                    // Render as plaintext first -> if it fails we still have the styles available
                    DebuggerUtility::var_dump($query, null, 8, true, ...$args);
                    ob_end_clean();
                    
                    DebuggerUtility::var_dump($query, 'Query Object', 8, false, ...$args);
                    
                } catch (\Throwable $e) {
                    echo '<em>Error while rendering the query: "' . $e->getMessage() . '"</em>';
                }
                
                if (! empty($first)) {
                    DebuggerUtility::var_dump($first, 'First result item');
                }
                DebuggerUtility::var_dump($result, 'Raw result');
                DebuggerUtility::var_dump($GLOBALS['TYPO3_DB'], 'Db Connection');
            }
        } catch (Exception $e) {
            echo '<h2>Db Error!</h2>';
            DebuggerUtility::var_dump($e);
        }
        exit();
    }
}
