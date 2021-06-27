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


namespace LaborDigital\T3ba\Tool\Tca\ContentType\Domain;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\DataHandler\DataHandlerService;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;

class ExtensionRowRepository implements PublicServiceInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected $dbService;
    
    /**
     * @var \LaborDigital\T3ba\Tool\DataHandler\DataHandlerService
     */
    protected $dataHandlerService;
    
    public function __construct(DbService $dbService, DataHandlerService $dataHandlerService)
    {
        $this->dbService = $dbService;
        $this->dataHandlerService = $dataHandlerService;
    }
    
    /**
     * Retrieves the raw row of a extension table.
     *
     * @param   string  $cType             The cType/signature the extension table should be resolved for
     * @param   int     $uid               The uid to retrieve the row for
     * @param   bool    $treatIdAsChildId  By default $uid is the uid of the tt_content record.
     *                                     If you set this argument to true you can instead directly
     *                                     retrieve the data using a child row uid
     *
     * @return array
     */
    public function getChildRow(string $cType, int $uid, bool $treatIdAsChildId = false): array
    {
        if (! ContentTypeUtil::hasExtensionTable($cType)) {
            return [];
        }
        
        $row = $this->dbService->getQuery(ContentTypeUtil::getTableMap()[$cType])
                               ->withLanguage(false)
                               ->withWhere($treatIdAsChildId ? ['uid' => $uid] : ['ct_parent' => $uid])
                               ->getFirst();
        
        return is_array($row) ? $row : [];
    }
    
    /**
     * Saves the content of a single extension table row into the database
     * The uid will be automatically resolve,d but can be overwritten using $data['uid' => ...].
     *
     * @param   string  $cType      The cType/signature the child row should be saved for
     * @param   int     $parentUid  The uid of the tt_content record that got extended
     * @param   array   $data       The fields of the extension row to store
     *
     * @return int The uid of the extension table row
     */
    public function saveChildRow(string $cType, int $parentUid, array $data): int
    {
        [$tableName, $uid] = $this->resolveTableNameAndUid($cType, $parentUid);
        
        if (! $data['uid'] && $uid !== null) {
            $data['uid'] = $uid;
        }
        
        $data['ct_parent'] = $parentUid;
        
        return $this->dataHandlerService->getRecordDataHandler($tableName)->save($data);
    }
    
    /**
     * Restores a previously deleted extension row
     *
     * @param   string  $cType      The cType/signature the child row should be restored for
     * @param   int     $parentUid  The uid of the tt_content record that got extended
     */
    public function restoreChildRow(string $cType, int $parentUid): void
    {
        [$tableName] = $this->resolveTableNameAndUid($cType, $parentUid);
        $parentRow = $this->dataHandlerService->getEmptyDataHandler()->recordInfo('tt_content', $parentUid, 'ct_child');
        
        if (! is_array($parentRow) || ! is_numeric($parentRow['ct_child'])) {
            return;
        }
        
        $this->dataHandlerService->getRecordDataHandler($tableName)->restore($parentRow['ct_child']);
    }
    
    /**
     * Deletes a extension row
     *
     * @param   string  $cType      The cType/signature the child row should be deleted for
     * @param   int     $parentUid  The uid of the tt_content record that got extended
     */
    public function deleteChildRow(string $cType, int $parentUid): void
    {
        [$tableName, $uid] = $this->resolveTableNameAndUid($cType, $parentUid);
        
        if ($uid === null) {
            return;
        }
        
        $this->dataHandlerService->getRecordDataHandler($tableName)->delete($uid);
    }
    
    /**
     * Internal helper to retrieve both the table name and extension row uid based on the $cType and $parentUid
     * The result is an array where the first index is the name of the table, and the uid provides the second.
     *
     * @param   string  $cType
     * @param   int     $parentUid
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\UnknownChildTableException
     */
    protected function resolveTableNameAndUid(string $cType, int $parentUid): array
    {
        $tableMap = ContentTypeUtil::getTableMap();
        $tableName = $tableMap[$cType] ?? null;
        if (! $tableName) {
            throw new UnknownChildTableException(
                'Could not save the child row for cType: ' . $cType . ' on tt_content '
                . $parentUid . ', because there is no mapped table for that');
        }
        
        $row = $this->dbService->getQuery($tableName)
                               ->withLanguage(false)
                               ->withWhere(['ct_parent' => $parentUid])
                               ->getFirst();
        
        return [
            $tableName,
            is_array($row) && is_numeric($row['uid']) ? $row['uid'] : null,
        ];
    }
}
