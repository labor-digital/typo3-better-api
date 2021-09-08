<?php /** @noinspection ExposingInternalClassesInspection */
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
 * Last modified: 2021.09.07 at 23:12
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Database\BetterQuery\Util;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Database\BetterQuery\AbstractBetterQuery;
use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\RelatedRecordRow;
use LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Database\RelationHandler;

class RelationResolver implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected $db;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected $contentRepository;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\BetterQuery\Util\OverlayResolver
     */
    protected $overlayResolver;
    
    /**
     * The list of resolved entries/the result of the resolve method
     *
     * @var array
     */
    protected $list = [];
    
    /**
     * The list of fields to be resolved
     *
     * @var array
     */
    protected $fields = [];
    
    /**
     * The given and prepared options
     *
     * @var array
     */
    protected $options;
    
    /**
     * The name of the table to work with
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The query object to resolve the relations for
     *
     * @var AbstractBetterQuery
     */
    protected $query;
    
    public function __construct(DbService $db, ContentRepository $contentRepository, OverlayResolver $overlayResolver)
    {
        $this->db = $db;
        $this->contentRepository = $contentRepository;
        $this->overlayResolver = $overlayResolver;
    }
    
    public function resolve(array $fields, array $options, AbstractBetterQuery $query): array
    {
        if (empty($fields)) {
            throw new BetterQueryException('The given $fields value is empty!');
        }
        
        $this->fields = $fields;
        $this->list = array_fill_keys($fields, []);
        $this->tableName = $query->getTableName();
        $this->query = $query;
        $this->prepareOptions($options);
        
        foreach ($this->fetchRows() as $row) {
            $this->runWithResolvedTca($row);
        }
        
        $list = $this->list;
        
        $this->fields = null;
        $this->list = null;
        $this->tableName = null;
        $this->query = null;
        $this->options = null;
        
        return $list;
    }
    
    /**
     * Fetches the rows of the SOURCE records from the database.
     * This also includes tt_content extensions
     *
     * @return array
     */
    protected function fetchRows(): array
    {
        $rows = $this->query->getQueryBuilder()->select('*')->execute()->fetchAllAssociative();
        
        if ($this->tableName === 'tt_content') {
            $rows = array_map(function (array $row): array {
                return $this->contentRepository->getExtendedRow($row);
            }, $rows);
        }
        
        return $rows;
    }
    
    /**
     * Remaps the tca for the special tt_content table and executes resolveSingleRow() for the given row
     *
     * @param   array  $row
     */
    protected function runWithResolvedTca(array $row): void
    {
        $runner = function () use ($row) {
            TcaUtil::runWithResolvedTypeTca($row, $this->tableName, function () use ($row) {
                $this->resolveSingleRow($row);
            });
        };
        
        if ($this->tableName === 'tt_content') {
            ContentTypeUtil::runWithRemappedTca($row, $runner);
        } else {
            $runner();
        }
    }
    
    /**
     * Resolves all relation fields for the given row fetching the relations and passing them on to hydrateEntitiesOfRecord()
     *
     * @param   array  $row
     */
    protected function resolveSingleRow(array $row): void
    {
        foreach ($this->findFieldTcaConfig() as $fieldName => $fieldConfig) {
            $relationHandler = $this->prepareRelationHandler($fieldName, $row, $fieldConfig);
            $relations = $relationHandler->getFromDB();
            $this->list[$fieldName][$row['uid']] = empty($relations)
                ? []
                : $this->hydrateEntitiesOfRecord($relationHandler, $relations);
        }
    }
    
    /**
     * Internal helper to create the instance of the TYPO3 relation handler based on the current configuration
     *
     * @param   string  $fieldName
     * @param   array   $row
     * @param   array   $fieldConfig
     *
     * @return \TYPO3\CMS\Core\Database\RelationHandler
     */
    protected function prepareRelationHandler(
        string $fieldName,
        array $row,
        array $fieldConfig
    ): RelationHandler
    {
        [$mmTable, $tableList] = $this->findFieldTableConfig($fieldConfig);
        $relationHandler = $this->makeInstance(RelationHandler::class);
        $relationHandler->setFetchAllFields(true);
        
        $relationHandler->start(
            empty($mmTable) ? $row[$fieldName] : '',
            $tableList,
            $mmTable,
            $row['uid'],
            $this->tableName,
            $fieldConfig
        );
        // Generate additional constraints for every table
        // Only extract the "where" part from the query
        // This is done, so we can apply the frontend constraints to the backend utility we use
        foreach ($relationHandler->tableArray as $localTable => $items) {
            $additionalWhere = $this->db->getQuery($localTable)
                                        ->withLanguage(false)
                                        ->withIncludeHidden($this->options['includeHiddenChildren'])
                                        ->getQueryBuilder()->getSQL();
            $additionalWhereParts = explode('WHERE', $additionalWhere);
            $relationHandler->additionalWhere[$localTable] = ' AND ' . end($additionalWhereParts);
        }
        
        return $relationHandler;
    }
    
    /**
     * Utilizes the TYPO3 relation handler to convert the resolved relations into RelatedRecordRow instances
     *
     * @param   \TYPO3\CMS\Core\Database\RelationHandler  $relationHandler
     * @param   array                                     $relations
     *
     * @return array
     */
    protected function hydrateEntitiesOfRecord(RelationHandler $relationHandler, array $relations): array
    {
        $list = [];
        
        foreach ($relationHandler->itemArray as $item) {
            $row = $relations[$item['table']][$item['id']] ?? null;
            
            if (! $row) {
                continue;
            }
            
            if ($this->query instanceof StandaloneBetterQuery) {
                $row = $this->overlayResolver->resolve(
                    $this->tableName,
                    $row,
                    $this->query->useVersionOverlay(),
                    $this->query->getSettings()
                );
                
                if (empty($row)) {
                    continue;
                }
            }
            
            $list[] = $this->makeInstance(
                RelatedRecordRow::class,
                [
                    (int)$item['id'],
                    $item['table'],
                    $relations[$item['table']][$item['id']],
                    is_string($this->options['model']) ? [$item['table'] => $this->options['model']] : $this->options['model'],
                ]
            );
        }
        
        return $list;
    }
    
    /**
     * Internal helper to extract the relation configuration for the TYPO3 relation handler from a field's TCA configuration
     *
     * @param   array  $fieldConfig
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException
     */
    protected function findFieldTableConfig(array $fieldConfig): array
    {
        $mmTable = $fieldConfig['MM'] ?? '';
        $tableList = '';
        
        if (isset($fieldConfig['type']) && $fieldConfig['type'] === 'group') {
            $tableList = $fieldConfig['allowed'] ?? '';
        } elseif (isset($fieldConfig['foreign_table'])) {
            $tableList = $fieldConfig['foreign_table'];
        }
        
        if (empty($tableList)) {
            throw new BetterQueryException('Could not retrieve the foreign tables from the TCA!');
        }
        
        return [
            empty(trim($mmTable)) ? null : $mmTable,
            $tableList ?? '',
        ];
    }
    
    /**
     * Extracts the TCA configuration for all required fields
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException
     */
    protected function findFieldTcaConfig(): array
    {
        $config = Arrays::getPath($GLOBALS, ['TCA', $this->tableName, 'columns', $this->fields, 'config']);
        
        if (! is_array($config) || count($config) !== count($this->fields)) {
            throw new BetterQueryException(
                'One or more of the requested fields: "' . implode('", "', $this->fields) .
                '" were not found in the TCA of table: "' . $this->tableName . '"!'
            );
        }
        
        return $config;
    }
    
    /**
     * Validates the possible options and fills in defaults
     *
     * @param   array  $options
     *
     * @return void
     */
    protected function prepareOptions(array $options): void
    {
        $this->options = Options::make($options, [
            'includeHiddenChildren' => [
                'type' => 'bool',
                'default' => false,
            ],
            'model' => [
                'type' => ['string', 'array', 'null'],
                'default' => null,
            ],
        ]);
    }
}