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


namespace LaborDigital\T3BA\Tool\Tca\ContentType\Domain;


use InvalidArgumentException;
use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\Database\BetterQuery\AbstractBetterQuery;
use LaborDigital\T3BA\Tool\Database\DbService;
use LaborDigital\T3BA\Tool\Tca\ContentType\ContentTypeUtil;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class ContentRepository implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Database\DbService
     */
    protected $dbService;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\ContentType\Domain\ExtensionRowRepository
     */
    protected $rowRepository;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper
     */
    protected $dataMapper;
    
    public function __construct(DbService $dbService, ExtensionRowRepository $rowRepository, DataMapper $dataMapper)
    {
        $this->dbService = $dbService;
        $this->rowRepository = $rowRepository;
        $this->dataMapper = $dataMapper;
    }
    
    /**
     * Retrieves either a single row, or multiple rows based on the given better query instance.
     * All rows will contain the extension fields when they are returned.
     *
     * @param   AbstractBetterQuery  $query     The query object to retrieve the tt_content records with.
     *                                          If the query does not select from tt_content, an exception is thrown.
     * @param   bool                 $getAll    By default only the first result is returned, if you set $getAll to
     *                                          true, all rows will be returned instead.
     * @param   bool                 $remapped  By default the extension fields get stripped of their namespace prefix,
     *                                          if you set the $remapped parameter to false, they are kept instead
     *
     * @return array|null
     */
    public function getByQuery(AbstractBetterQuery $query, bool $getAll = false, bool $remapped = true): ?array
    {
        if ($query->getTableName() !== 'tt_content') {
            throw new InvalidArgumentException(
                'Your query must select data from the "tt_content" table, yours is configured to select from "'
                . $query->getTableName() . '", instead.');
        }
        
        if ($getAll) {
            $result = [];
            foreach ($query->getAll() as $row) {
                $result[] = $this->getExtendedRow($row, $remapped);
            }
            
            return $result;
        }
        
        $result = $query->getFirst();
        if (empty($result) || ! is_array($result)) {
            return null;
        }
        
        return $this->getExtendedRow($result, $remapped);
    }
    
    /**
     * Returns a single row of the tt_content which contain the extension fields when returned.
     *
     * @param   int   $uid        The uid of the tt_content record to load
     * @param   bool  $remapped   By default the extension fields get stripped of their namespace prefix,
     *                            if you set the $remapped parameter to false, they are kept instead
     *
     * @return array|null
     */
    public function getByUid(int $uid, bool $remapped = true): ?array
    {
        return $this->getByQuery(
            $this->dbService->getQuery('tt_content')->withWhere(['uid' => $uid]), false,
            $remapped
        );
    }
    
    /**
     * @param $rowOrUid
     *
     * @return \LaborDigital\T3BA\Tool\Tca\ContentType\Domain\AbstractDataModel
     */
    public function hydrateModel($rowOrUid): AbstractDataModel
    {
        $row = [];
        if (is_array($rowOrUid)) {
            $row = $this->getExtendedRow($rowOrUid);
        } elseif (is_numeric($rowOrUid)) {
            $row = $this->getByQuery($this->dbService->getQuery('tt_content')->withWhere(['uid' => $rowOrUid]));
        }
        
        $cType = $row['CType'] ?? '';
        $class = ContentTypeUtil::getModelClass($cType);
        
        return ContentTypeUtil::runWithRemappedTca($cType, function () use ($row, $class) {
            $mapped = $this->dataMapper->map($class, [$row]);
            /** @var AbstractDataModel $model */
            $model = reset($mapped);
            
            $model->_setProperty('__raw', $row);
            $model->_setProperty('__flex', $this->resolveFlexFormColumns($row));
            
            return $model;
        });
    }
    
    /**
     * Receives either an array of tt_content uids or a list of tt_content rows, which will get extended
     * and converted into their matched domain models. If no explicit domain model was configured, the
     * DefaultDataModel is used instead.
     *
     * @param   array  $rowsOrUids
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
     */
    public function hydrateAll(array $rowsOrUids): ObjectStorage
    {
        $result = $this->makeInstance(ObjectStorage::class);
        foreach ($rowsOrUids as $rowOrUid) {
            $result->attach(
                $this->hydrateModel($rowOrUid)
            );
        }
        
        return $result;
    }
    
    /**
     * Retrieves a row of the tt_content table, loads the extension row and returns the merged sum of both
     *
     * @param   array  $row       The row to extend with the extension fields.
     *                            Both "uid" and "CType" fields must be present in the row, otherwise
     *                            the original row will be returned.
     * @param   bool   $remapped  By default the extension fields get stripped of their namespace prefix,
     *                            if you set the $remapped parameter to false, they are kept instead
     *
     * @return array
     */
    public function getExtendedRow(array $row, bool $remapped = true): array
    {
        if (! is_numeric($row['uid']) || ! is_string($row['CType'])) {
            return $row;
        }
        
        $childRow = $this->rowRepository->getChildRow($row['CType'], $row['uid']);
        
        $row = array_merge($row, ContentTypeUtil::convertChildForParent($childRow, $row['CType']));
        
        return $remapped ? ContentTypeUtil::remapColumns($row, $row['CType']) : $row;
    }
    
    /**
     * Internal helper to resolve the flex form columns into the __flex magic storage key
     *
     * @param   array  $row
     *
     * @return array
     */
    protected function resolveFlexFormColumns(array $row): array
    {
        $flexFormService = $this->getService(FlexFormService::class);
        $colConfig = $GLOBALS['TCA']['tt_content']['columns'] ?? [];
        $flexCols = [];
        foreach ($colConfig as $col => $conf) {
            if (empty($row[$col])
                || ($conf['config']['type'] ?? null) !== 'flex') {
                continue;
            }
            
            $flexCols[$col] = $flexFormService->convertFlexFormContentToArray($row[$col]);
        }
        
        return $flexCols;
    }
}
