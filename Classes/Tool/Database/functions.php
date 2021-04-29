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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

use LaborDigital\T3BA\Tool\Database\BetterQuery\ExtBase\ExtBaseBetterQuery;
use LaborDigital\T3BA\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3BA\Tool\Database\DatabaseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

if (! function_exists('dbgQuery')) {
    /**
     * Helper to debug a typo3 query. Will render the sql statement, the result, and exceptions to the screen to see.
     *
     * @param   QueryInterface|ExtBaseBetterQuery|StandaloneBetterQuery  $query  The query to debug
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    function dbgQuery($query)
    {
        $result = $exception = null;
        $isStandalone = false;
        if ($query instanceof ExtBaseBetterQuery) {
            $query = $query->getQuery();
        }
        if ($query instanceof QueryResult) {
            $query = $query->getQuery();
        }
        if (! $query instanceof QueryInterface) {
            if (! $query instanceof StandaloneBetterQuery) {
                throw new DatabaseException('The given query object can not be used!');
            }
            $dQuery = $query->getQueryBuilder();
            $isStandalone = true;
        } else {
            $dQuery = GeneralUtility::getContainer()
                                    ->get(Typo3DbQueryParser::class)
                                    ->convertQueryToDoctrineQueryBuilder($query);
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
            $out[] = '"' . addslashes($v) . '"';
        }
        $queryString = str_replace($in, $out, $queryString);
        
        // Try to execute the message
        try {
            if ($isStandalone) {
                $first = $dQuery->getFirstResult();
                $result = $dQuery->execute();
            } else {
                $first = $query->execute()->getFirst();
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
            if (! empty($first)) {
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
