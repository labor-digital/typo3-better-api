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


namespace LaborDigital\T3ba\ExtConfigHandler\Table;


use LaborDigital\T3ba\Event\Backend\BackendUtilityRecordFilterEvent;
use LaborDigital\T3ba\Event\Core\RefIndexRecordDataFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\ActionFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\ActionPostProcessorEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerCompareFieldArrayFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerDbFieldsFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerRecordInfoFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerRecordInfoWithPermsFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\SaveAfterDbOperationsEvent;
use LaborDigital\T3ba\Event\DataHandler\SaveFilterEvent;
use LaborDigital\T3ba\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\ExtConfigHandler\Table\ContentType\DataHandlerAdapter;
use LaborDigital\T3ba\Tool\DataHandler\DataHandlerService;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ExtensionRowRepository;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class ContentTypeApplier extends AbstractExtConfigApplier
{
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ExtensionRowRepository
     */
    protected $repository;
    
    /**
     * @var DataHandler
     */
    protected $dataHandler;
    
    /**
     * Lazy high level caching to avoid multiple db requests on the child row if possible.
     *
     * @var array|null
     */
    protected $childRowCache;
    
    /**
     * Used only if a new record is created in tt_content
     *
     * @var int|null
     */
    protected $delayedChildRelation;
    
    /**
     * The list of child row history entries that must be merged into their tt_content history
     *
     * @var array
     */
    protected $additionalHistoryRewrites = [];
    
    public function __construct(ExtensionRowRepository $repository, DataHandlerService $dataHandlerService)
    {
        $this->repository = $repository;
        $this->dataHandler = $dataHandlerService->getEmptyDataHandler();
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $priorityOptions = ['priority' => 1000];
        $subscription->subscribe(SaveFilterEvent::class, 'onDataSave', $priorityOptions);
        $subscription->subscribe(SaveAfterDbOperationsEvent::class, 'onSaveDbOperations', $priorityOptions);
        $subscription->subscribe(ActionFilterEvent::class, 'onAction', $priorityOptions);
        $subscription->subscribe(ActionPostProcessorEvent::class, 'onActionPostProcessor', $priorityOptions);
        $subscription->subscribe(BackendUtilityRecordFilterEvent::class, 'onBeRecordLoad');
        $subscription->subscribe(DataHandlerRecordInfoFilterEvent::class, 'onRecordInfoFilter');
        $subscription->subscribe(DataHandlerRecordInfoWithPermsFilterEvent::class, 'onRecordInfoWithPermsFilter');
        $subscription->subscribe(DataHandlerDbFieldsFilterEvent::class, 'onDbFieldFilter', $priorityOptions);
        $subscription->subscribe(RefIndexRecordDataFilterEvent::class, 'onRefIndexFilter', $priorityOptions);
        $subscription->subscribe(FormFilterEvent::class, 'onFormFilter', $priorityOptions);
        $subscription->subscribe(DataHandlerCompareFieldArrayFilterEvent::class, 'onFieldArrayCompare', $priorityOptions);
    }
    
    /**
     * Makes sure that the internal cache gets cleared when we start to save a new tt_content record
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\SaveFilterEvent  $event
     */
    public function onDataSave(SaveFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        $this->flushInternalCache();
    }
    
    public function onAction(ActionFilterEvent $event): void
    {
        $this->handleDeleteAndRestore($event->getTableName(), $event->getId(), $event->getCommand(), 'undelete');
    }
    
    public function onActionPostProcessor(ActionPostProcessorEvent $event): void
    {
        $this->handleDeleteAndRestore($event->getTableName(), $event->getId(), $event->getCommand(), 'delete');
    }
    
    public function onBeRecordLoad(BackendUtilityRecordFilterEvent $event): void
    {
        $event->setRow(
            $this->handleRecordLookup(
                $event->getTableName(),
                $event->getId(),
                $event->getFieldList(),
                $event->getRow()
            )
        );
    }
    
    public function onRecordInfoWithPermsFilter(DataHandlerRecordInfoWithPermsFilterEvent $event): void
    {
        $event->setResult(
            $this->handleRecordLookup(
                $event->getTableName(),
                $event->getId(),
                $event->getFieldList(),
                $event->getResult()
            )
        );
    }
    
    /**
     * Is used to block database requests to virtual columns when the data handler processes the record
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\DataHandlerRecordInfoFilterEvent  $event
     */
    public function onRecordInfoFilter(DataHandlerRecordInfoFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        // Ignore if there are multiple fields
        $fields = $event->getFieldList();
        if ($fields === '*' || strpos($fields, ',') !== false) {
            return;
        }
        
        $columns = ContentTypeUtil::getColumnMap();
        if (! isset($columns[$fields])) {
            return;
        }
        
        $event->setConcreteInfoProvider(function (string $field, string $table, $id) {
            return [$field => $this->getChildRow($id)[$field] ?? null];
        });
    }
    
    /**
     * Executed when the data handler writes the database rows using the insertDB() method.
     * This method will extract our extension columns from the main table and instead write
     * their data into the extension table.
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\DataHandlerDbFieldsFilterEvent  $event
     */
    public function onDbFieldFilter(DataHandlerDbFieldsFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        $row = $event->getRow();
        // Row must not always include a uid, therefore we have to "simulate" the uid here
        $rowWithUid = array_merge(['uid' => $event->getId()], $event->getRow());
        $cType = $this->resolveColumnValue('CType', $rowWithUid);
        
        // If we can't resolve a cType we are not needed...
        if (! is_string($cType)) {
            return;
        }
        
        $childPointerField = ContentTypeUtil::getChildPointerFieldName();
        
        // If there is no extension table for the new cType we drop everything
        // and let the data handler go on by itself.
        if (! ContentTypeUtil::hasExtensionTable($cType)) {
            // There are no extension tables -> skip
            if (empty(ContentTypeUtil::getTableMap())) {
                return;
            }
            
            $row = ContentTypeUtil::removeAllExtensionColumns($row);
            $row[$childPointerField] = '0';
            $event->setRow($row);
            
            return;
        }
        
        // When the cType is being changed we try to drop the old row
        $oldCType = $this->resolveColumnValue('CType', ['uid' => $event->getId()]);
        if (is_string($oldCType) && $oldCType !== $cType && ContentTypeUtil::hasExtensionTable($oldCType)) {
            $this->repository->deleteChildRow($oldCType, $event->getId());
        }
        
        $childRow = ContentTypeUtil::extractChildFromParent($row, $cType);
        $row = ContentTypeUtil::removeAllExtensionColumns($row);
        
        // A new record is created -> Delay the child row generation until we have a uid for our record
        $parentId = $event->getId();
        if (! is_int($event->getId())) {
            $parentId = -1;
        }
        
        $childRow['pid'] = $this->resolveColumnValue('pid', $rowWithUid);
        $childRow['sys_language_uid'] = $this->resolveColumnValue('sys_language_uid', $rowWithUid);
        if (isset($this->delayedChildRelation)) {
            // This block will be executed when inline relations are present and prevents
            // the creation of zombie entries in the database
            if ($parentId === -1) {
                throw new \LogicException('This should never happen: Tried to register a child for delayed relation, while another one waits for relation');
            }
            
            $row[$childPointerField] = $this->delayedChildRelation;
            $this->persistDelayedChildRelation($cType, $parentId, $childRow['pid']);
        } else {
            $row[$childPointerField] = $this->repository->saveChildRow($cType, $parentId, $childRow);
        }
        
        $event->setRow($row);
        
        if ($parentId === -1) {
            $this->delayedChildRelation = $row[$childPointerField];
        } else {
            // Rewrite the dataHandlers history entry to incorporate the extension columns
            DataHandlerAdapter::rewriteHistory(
                $event->getDataHandler(),
                $parentId,
                ContentTypeUtil::convertChildForParent($this->getChildRow($parentId), $cType),
                $this->additionalHistoryRewrites
            );
        }
    }
    
    /**
     * When a new record is created we need to wait for the new uid, therefore
     * the onDbFieldFilter() can delay the relation creation between the table and the extension table.
     * If that happens this method will clear up the remaining work to link both tables together.
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\SaveAfterDbOperationsEvent  $event
     */
    public function onSaveDbOperations(SaveAfterDbOperationsEvent $event): void
    {
        if (! $this->delayedChildRelation || $event->getTableName() !== 'tt_content') {
            return;
        }
        
        $row = $event->getRow();
        $this->persistDelayedChildRelation($row['CType'], $event->getId(), $row['pid']);
        
    }
    
    /**
     * Makes sure the ref-index has access to all our extension columns in addition to the default tt_content columns
     *
     * @param   \LaborDigital\T3ba\Event\Core\RefIndexRecordDataFilterEvent  $event
     */
    public function onRefIndexFilter(RefIndexRecordDataFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        $event->setRow($this->mergeChildIntoParent($event->getRow()));
    }
    
    /**
     * Injects the extension fields into the database row when the form engine builds the backend form
     * of the tt_content table
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\FormFilterEvent  $event
     */
    public function onFormFilter(FormFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        $this->flushInternalCache();
        
        $data = $event->getData();
        $data['databaseRow'] = $this->mergeChildIntoParent($data['databaseRow']);
        $event->setData($data);
    }
    
    public function onFieldArrayCompare(DataHandlerCompareFieldArrayFilterEvent $event): void
    {
        if ($event->getTableName() !== 'tt_content') {
            return;
        }
        
        $fieldArray = $event->getFieldArray();
        $cType = $this->resolveColumnValue('CType', ['uid' => $event->getId()]);
        $childFieldArray = ContentTypeUtil::extractChildFromParent($fieldArray, $cType);
        
        if (empty($childFieldArray)) {
            return;
        }
        
        $comp = $event->getConcreteComparator();
        $event->setConcreteComparator(function ($table, $id) use ($comp, $fieldArray, $cType, $childFieldArray) {
            $res = $comp($table, $id, $fieldArray);
            $childTable = ContentTypeUtil::getTableMap()[$cType] ?? '';
            $childUid = $this->getChildRow($id)['uid'] ?? 0;
            $childRes = $comp($childTable, $childUid, $childFieldArray);
            $childRes = ContentTypeUtil::remapColumns($childRes, $cType, true);
            
            $this->additionalHistoryRewrites['tt_content:' . $id] = [$childTable . ':' . $childUid => $cType];
            
            return array_merge($res, $childRes);
        });
    }
    
    /**
     * Handler to toggle the deleted state for a child table row
     *
     * @param   string  $tableName
     * @param           $parentUid
     * @param   string  $command
     * @param   string  $targetCommand
     */
    public function handleDeleteAndRestore(string $tableName, $parentUid, string $command, string $targetCommand): void
    {
        if ($tableName !== 'tt_content') {
            return;
        }
        
        $cType = $this->resolveColumnValue('CType', ['uid' => $parentUid]);
        if (! ContentTypeUtil::hasExtensionTable($cType)) {
            return;
        }
        
        if ($command === 'delete' && $targetCommand === $command) {
            $this->repository->deleteChildRow($cType, $parentUid);
            
            return;
        }
        
        if ($command === 'undelete' && $targetCommand === $command) {
            $this->repository->restoreChildRow($cType, $parentUid);
        }
    }
    
    /**
     * Handler implementation that is used to enrich the given row by a child row, when possible.
     *
     * @param   string            $tableName
     * @param   int|string|mixed  $parentUid
     * @param   string            $fieldList
     * @param   mixed             $row
     *
     * @return array|mixed
     */
    protected function handleRecordLookup(string $tableName, $parentUid, string $fieldList, $row)
    {
        if (! is_int($parentUid)) {
            return $row;
        }
        
        if ($tableName !== 'tt_content') {
            return $row;
        }
        
        if ($fieldList !== '*') {
            return $row;
        }
        
        if (! is_array($row) || empty($row['CType']) || $row['CType'] === 'list') {
            return $row;
        }
        
        if (ContentTypeUtil::hasExtensionTable($row['CType'])) {
            $row = $this->mergeChildIntoParent($row);
        }
        
        return $row;
    }
    
    /**
     * Internal helper to resolve the value of a single column, either on the given row
     * or by retrieving the value from the database
     *
     * @param   string  $columnName  The name of the column to retrieve the value for
     * @param   array   $row         The row MUST INCLUDE THE UID to retrieve the value for
     *
     * @return mixed|null
     */
    protected function resolveColumnValue(string $columnName, array $row)
    {
        if (isset($row[$columnName])) {
            return $row[$columnName];
        }
        
        if (! is_numeric($row['uid'])) {
            return null;
        }
        
        return $this->dataHandler->recordInfo('tt_content', $row['uid'], $columnName)[$columnName] ?? null;
    }
    
    /**
     * Internal wrapper around the content type repository that applies a small first-level cache
     * to avoid multiple queries to achieve the same on different locations.
     *
     * @param $uidOrRow
     *
     * @return array
     */
    protected function getChildRow($uidOrRow): array
    {
        $uid = $this->resolveUid($uidOrRow);
        
        if ($uid === null) {
            return [];
        }
        
        if (($this->childRowCache['id'] ?? null) === $uid) {
            return $this->childRowCache['row'];
        }
        
        $lookupRow = is_array($uidOrRow) ? array_merge(['uid' => $uid], $uidOrRow) : ['uid' => $uid];
        $child = $this->repository->getChildRow(
            $this->resolveColumnValue('CType', $lookupRow), $uid
        );
        
        $this->childRowCache = [
            'id' => $uid,
            'row' => $child,
        ];
        
        return $child;
    }
    
    /**
     * Internal helper to resolve a uid from either a numeric value or a row
     *
     * @param $uidOrRow
     *
     * @return int|null
     */
    protected function resolveUid($uidOrRow): ?int
    {
        if (is_array($uidOrRow) && is_numeric($uidOrRow['uid'] ?? null)) {
            $uidOrRow = $uidOrRow['uid'];
        }
        
        if (is_numeric($uidOrRow)) {
            $uidOrRow = (int)$uidOrRow;
        }
        
        if (! is_int($uidOrRow)) {
            return null;
        }
        
        return $uidOrRow;
    }
    
    /**
     * Internal helper that will resolve a potential child row and automatically merge
     * it into the given row, before returning the merged result.
     *
     * @param   array  $parentRow
     *
     * @return array
     */
    protected function mergeChildIntoParent(array $parentRow): array
    {
        $childRow = $this->getChildRow($parentRow);
        if (empty($childRow)) {
            return $parentRow;
        }
        
        $cType = $this->resolveColumnValue('CType', $parentRow);
        if (! is_string($cType)) {
            return $parentRow;
        }
        
        return array_merge(
            $parentRow,
            ContentTypeUtil::convertChildForParent($childRow, $cType)
        );
    }
    
    /**
     * Applies the ct_parent update to the extension table, for an uid which was registered for "delayed" relation.
     *
     * @param   string  $cType
     * @param   int     $parentUid
     * @param   int     $parentPid
     */
    protected function persistDelayedChildRelation(string $cType, int $parentUid, int $parentPid): void
    {
        if (empty($this->delayedChildRelation)) {
            return;
        }
        
        $this->repository->saveChildRow($cType, $parentUid, [
            'uid' => $this->delayedChildRelation,
            'pid' => $parentPid,
        ]);
        
        $this->delayedChildRelation = null;
    }
    
    /**
     * Clears the internal child row cache
     */
    protected function flushInternalCache(): void
    {
        $this->childRowCache = [];
    }
}
