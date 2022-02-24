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
 * Last modified: 2021.07.26 at 09:50
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Database\BetterQuery\AbstractBetterQuery;
use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException;
use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryTypo3DbQueryParserAdapter;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Util\OverlayResolver;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Util\RelationResolver;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Throwable;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

class StandaloneBetterQuery extends AbstractBetterQuery
{
    use ContainerAwareTrait;
    
    /**
     * The instance of the page repository after it was requested
     *
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;
    
    /**
     * True if the version overlay should be applied for the query result
     *
     * @var bool
     */
    protected $versionOverlay = true;
    
    /**
     * Creates a new query object
     *
     * @param   string                                                         $tableName
     * @param   \TYPO3\CMS\Core\Database\Query\QueryBuilder                    $queryBuilder
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface  $settings
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext                $typoContext
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\Session                 $session
     */
    public function __construct(
        string $tableName,
        QueryBuilder $queryBuilder,
        QuerySettingsInterface $settings,
        TypoContext $typoContext,
        Session $session
    )
    {
        parent::__construct(new DoctrineQueryAdapter($tableName, $queryBuilder, $settings, $typoContext), $typoContext,
            $session);
    }
    
    /**
     * Sets the flag that determines if the version/workspace overlay should be applied or not; TRUE by default
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function withVersionOverlay(bool $state): self
    {
        $this->versionOverlay = $state;
        
        return $this;
    }
    
    /**
     * Returns true if the version/workspace overlay is used, false if not
     *
     * @return bool
     */
    public function useVersionOverlay(): bool
    {
        return $this->versionOverlay;
    }
    
    /**
     * Returns the configured instance of the query builder for this query
     *
     * @param   bool  $forSelect  By default all select query constraints are added to the query builder instance.
     *                            You can set this to false if you want to get a query builder for an update/delete or
     *                            insert query
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    public function getQueryBuilder(bool $forSelect = true): QueryBuilder
    {
        $this->applyWhere($this->adapter);
        $qb = $this->adapter->getQueryBuilder();
        if ($forSelect) {
            BetterQueryTypo3DbQueryParserAdapter::addConstraintsOfSettings(
                $this->adapter->getTableName(),
                $qb,
                $this->adapter->getSettings()
            );
        }
        
        return $qb;
    }
    
    /**
     * Executes the currently configured query and returns the results
     *
     * @param   array|null  $fieldList  Optional list of fields that should be selected from the database
     *
     * @return array
     */
    public function getAll(?array $fieldList = null): array
    {
        $qb = $this->getQueryBuilder();
        
        // Only select a sparse field list
        if ($fieldList !== null) {
            $qb->select(...$fieldList);
        }
        
        return array_map(function (array $row) {
            $tableName = $this->adapter->getTableName();
            
            return $this->handleTranslationAndVersionOverlay($tableName, $row);
        }, $qb->execute()->fetchAllAssociative());
    }
    
    
    /**
     * Returns the total number of items in the result set, matching the given query parameters
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->getQueryBuilder()->execute()->rowCount();
    }
    
    /**
     * Returns the first element from the queries result set that matches your criteria
     *
     * @param   array|null  $fieldList  Optional list of fields that should be selected from the database
     *
     * @return mixed
     */
    public function getFirst(?array $fieldList = null)
    {
        $qb = $this->getQueryBuilder();
        
        // Only select a sparse field list
        if ($fieldList !== null) {
            $qb->select(...$fieldList);
        }
        
        $result = $qb->execute()->fetchAssociative();
        if (is_array($result)) {
            $result = $this->handleTranslationAndVersionOverlay($this->adapter->getTableName(), $result);
        }
        
        return is_array($result) ? $result : null;
    }
    
    /**
     * Executes the query as delete statement
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function delete()
    {
        return $this->getQueryBuilder(false)
                    ->delete($this->adapter->getTableName())
                    ->execute();
    }
    
    /**
     * Executes the query as insert statement
     *
     * @param   array  $values              The values to specify for the insert query indexed by column names
     * @param   bool   $returnLastInsertId  deprecated, will be the new default in v11, if set to true the
     *
     * @return mixed|int The uid of the new record will be returned if $returnLastInsertId is set to true
     * @todo remove $returnLastInsertId and always return the uid, the default result is the number of affected rows.
     */
    public function insert(array $values, bool $returnLastInsertId = false)
    {
        $qb = $this->getQueryBuilder(false)
                   ->insert($this->adapter->getTableName())
                   ->values($values, true);
        
        $res = $qb->execute();
        if (! $returnLastInsertId) {
            return $res;
        }
        
        return (int)$qb->getConnection()->lastInsertId($this->adapter->getTableName());
    }
    
    /**
     * Executes the query as update statement
     *
     * @param   array  $values
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function update(array $values)
    {
        $queryBuilder = $this->getQueryBuilder(false)
                             ->update($this->adapter->getTableName());
        foreach ($values as $column => $value) {
            $queryBuilder->set($column, $value, true);
        }
        
        return $queryBuilder->execute();
    }
    
    /**
     * Finds related records for a field in the queried table.
     * The result is an array for all relations of the given field for every result of the query.
     *
     * Translation overlays will be automatically applied.
     *
     * @param   array  $fields          The names of the group fields to find the relations for
     * @param   array  $options         A list of additional options:
     *                                  - includeHiddenChildren (bool) false: Set this to true if you
     *                                  want to include hidden children into your result
     *
     *                                  - model (string|array): Only required if you want to use getModel()
     *                                  on the result row. Either the class name of a model to map all related rows,
     *                                  or an array of 'tableName' => 'modelClassName' if you want to relate
     *                                  multiple tables using a "group" field
     *
     * @return RelatedRecordRow[][] Returns either a list of entries per field name. The list of entries is ordered by
     *                            the name of the foreign table.
     * @throws BetterQueryException
     * @throws BetterQueryException
     * @see RelatedRecordRow
     */
    public function getRelated(array $fields, array $options = []): array
    {
        return $this->getService(RelationResolver::class)
                    ->resolve(
                        $fields,
                        $options,
                        $this
                    );
    }
    
    /**
     * Runs the given callable inside a transaction scope connection of this query object.
     * All actions will be committed after your callback was executed, and automatically rolled
     * back if the callable has thrown an exception.
     *
     * @param   callable  $callable  The callback to execute inside the transaction context.
     *                               Receives this query instance as only parameter
     *
     * @throws \Throwable
     */
    public function runInTransaction(callable $callable): void
    {
        $connection = $this->adapter->getQueryBuilder()->getConnection();
        $autoCommitBackup = $connection->isAutoCommit();
        try {
            $connection->beginTransaction();
            $connection->setAutoCommit(false);
            $callable($this);
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollBack();
            throw $exception;
        } finally {
            $connection->setAutoCommit($autoCommitBackup);
        }
    }
    
    /**
     * Internal helper to handle translation and version overlays of a single row of a given database table
     *
     * @param   string  $tableName
     * @param   array   $row
     *
     * @return array
     */
    protected function handleTranslationAndVersionOverlay(string $tableName, array $row): array
    {
        return $this->getService(OverlayResolver::class)->resolve($tableName, $row, $this->versionOverlay, $this->adapter->getSettings());
    }
}
