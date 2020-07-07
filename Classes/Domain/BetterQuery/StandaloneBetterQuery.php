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
 * Last modified: 2020.03.20 at 18:03
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\DoctrineQueryAdapter;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbService;
use LaborDigital\Typo3BetterApi\Page\PageService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\RelationHandler;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

class StandaloneBetterQuery extends AbstractBetterQuery
{
    
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
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext           $typoContext
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\Session                 $session
     */
    public function __construct(
        string $tableName,
        QueryBuilder $queryBuilder,
        QuerySettingsInterface $settings,
        TypoContext $typoContext,
        Session $session
    ) {
        parent::__construct(new DoctrineQueryAdapter($tableName, $queryBuilder, $settings), $typoContext, $session);
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
     * @inheritDoc
     */
    public function getQueryBuilder(): QueryBuilder
    {
        $this->applyWhere();
        $qb = $this->adapter->getQueryBuilder();
        if ($qb->getType() === \Doctrine\DBAL\Query\QueryBuilder::SELECT) {
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
        }, $qb->execute()->fetchAll());
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
        
        $result = $qb->execute()->fetch();
        if (is_array($result)) {
            $result = $this->handleTranslationAndVersionOverlay($this->adapter->getTableName(), $result);
        }
        
        return $result;
    }
    
    /**
     * Executes the query as delete statement
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function delete()
    {
        return $this->getQueryBuilder()->delete($this->adapter->getTableName())->execute();
    }
    
    /**
     * Executes the query as insert statement
     *
     * @param   array  $values  The values to specify for the insert query indexed by column names
     *
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function insert(array $values)
    {
        return $this->getQueryBuilder()->insert($this->adapter->getTableName())->values($values, true)->execute();
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
        $queryBuilder = $this->getQueryBuilder()->update($this->adapter->getTableName());
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
     * @param   array|string  $field                  Either a single field you want to query the relations for or a
     *                                                list of fields as an array.
     * @param   bool          $includeHiddenChildren  Set this to true if you want to include hidden children into your
     *                                                result
     *
     * @return array Returns either a list of entries per field name or a list of entries when only a single field is
     *               given. The list of entries is ordered by the name of the foreign table.
     * @throws \LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQueryException
     * @see \LaborDigital\Typo3BetterApi\Domain\BetterQuery\RelatedRecordRow
     */
    public function getRelated($field, bool $includeHiddenChildren = false): array
    {
        if ($isSingleField = is_string($field)) {
            $field = [$field];
        }
        if (! is_array($field)) {
            throw new BetterQueryException('Only strings and arrays are allowed as $field!');
        }
        
        // Prepare the configuration
        $qb        = $this->getQueryBuilder();
        $table     = $this->adapter->getTableName();
        $tcaConfig = Arrays::getPath($GLOBALS, ['TCA', $table, 'columns', $field, 'config']);
        if (! is_array($tcaConfig)) {
            throw new BetterQueryException(
                "The requested field: \"$field\" was not found in the TCA of table: \"$table\"!"
            );
        }
        
        // Fix issues with virtual columns
        $cols          = $qb->getConnection()->getSchemaManager()->listTableColumns($table);
        $findAllFields = count(array_filter($field, static function ($fieldName) use ($cols) {
                return isset($cols[$fieldName]);
            })) !== count($field);
        $selectFields  = $findAllFields ? ['*'] : $field;
        
        // Query the results from the database
        $records = (clone $qb)->select('uid', ...$selectFields)->execute()->fetchAll();
        if (empty($records)) {
            return [];
        }
        
        // Lazy load additional dependencies
        $container = TypoContainer::getInstance();
        $dbService = $container->get(DbService::class);
        
        // Iterate the configuration for the fields
        $resultsByField       = [];
        $additionalWhereCache = [];
        foreach ($tcaConfig as $currentField => $config) {
            // Get the table definition for the tca type
            $mmTable   = $config['MM'] ?? '';
            $tableList = '';
            if (isset($config['type']) && $config['type'] === 'group') {
                $tableList = $config['allowed'] ?? '';
            } elseif (isset($config['foreign_table'])) {
                $tableList = $config['foreign_table'];
            }
            if (empty($tableList)) {
                throw new BetterQueryException('Could not retrieve the foreign tables from the TCA!');
            }
            
            // Resolve the relations for every element
            foreach ($records as $result) {
                // Create the relation handler
                $relationHandler = $container->get(RelationHandler::class);
                $relationHandler->setFetchAllFields(true);
                $relationHandler->start(
                    empty($mmTable) ? $result[$currentField] : '',
                    $tableList,
                    $mmTable,
                    $result['uid'],
                    $this->adapter->getTableName(),
                    $config
                );
                
                // Generate additional constraints for every table
                // This is done so we can apply the frontend constraints to the backend utility we use
                foreach ($relationHandler->tableArray as $localTable => $items) {
                    // Build additional where or load it from cache
                    $additionalWhere = $additionalWhereCache[$localTable] ??
                                       $dbService->getQuery($localTable)
                                                 ->withLanguage(false)
                                                 ->withIncludeHidden($includeHiddenChildren)
                                                 ->getQueryBuilder()->getSQL();
                    // Only extract the "where" part from the query
                    $additionalWhereParts                          = explode('WHERE', $additionalWhere);
                    $additionalWhere                               = ' AND ' . end($additionalWhereParts);
                    $relationHandler->additionalWhere[$localTable] = $additionalWhere;
                }
                
                // Request the database using the backend relation handler
                $relations = $relationHandler->getFromDB();
                
                // Handle Overlays
                foreach ($relations as $localTable => $rows) {
                    foreach ($rows as $k => $row) {
                        $relations[$localTable][$k] = $this->handleTranslationAndVersionOverlay($localTable, $row);
                    }
                }
                
                // Generate objects that are in order by their sorting
                $relationList = [];
                foreach ($relationHandler->itemArray as $item) {
                    if (! isset($relations[$item['table']][$item['id']])) {
                        continue;
                    }
                    $relationList[] = new RelatedRecordRow(
                        (int)$item['id'],
                        $item['table'],
                        $relations[$item['table']][$item['id']]
                    );
                }
                
                // Store the relations
                $resultsByField[$currentField][$result['uid']] = $relationList;
            }
        }
        
        // Check if we got a single field request
        if ($isSingleField) {
            return $resultsByField[reset($field)] ?? [];
        }
        
        // Done
        return $resultsByField;
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
        // Create page repository if required
        if (empty($this->pageRepository)) {
            $this->pageRepository = TypoContainer::getInstance()->get(PageService::class)->getPageRepository();
        }
        
        // Apply the version overlay
        if ($this->versionOverlay) {
            $this->pageRepository->versionOL($tableName, $row, true);
        }
        
        // Apply the translation overlay only if required
        if (! $this->adapter->getSettings()->getRespectSysLanguage()) {
            return $row;
        }
        $languageUid = $this->adapter->getSettings()->getLanguageUid();
        if ($languageUid < 0) {
            return $row;
        }
        
        // This is basically a copy of the logic in PageRepository->getLanguageOverlay()
        if (! Arrays::hasPath($GLOBALS, ['TCA', $tableName, 'ctrl', 'languageField'])) {
            return $row;
        }
        if ($tableName === 'pages') {
            return $this->pageRepository->getPageOverlay($row, $languageUid);
        }
        
        return $this->pageRepository->getRecordOverlay(
            $tableName,
            $row,
            $languageUid,
            is_string($this->adapter->getSettings()->getLanguageOverlayMode()) ? 'hideNonTranslated' : '1'
        );
    }
}
