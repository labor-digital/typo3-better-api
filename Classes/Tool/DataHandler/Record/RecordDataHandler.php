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


namespace LaborDigital\T3ba\Tool\DataHandler\Record;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\DataHandler\DataHandlerService;
use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Class RecordDataHandler
 *
 * A simple abstraction to perform record actions using the data handler in an object-oriented way
 *
 * @package LaborDigital\T3ba\Tool\DataHandler\Record
 */
class RecordDataHandler implements NoDiInterface
{
    
    /**
     * The table name to handle the actions
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * @var \LaborDigital\T3ba\Tool\DataHandler\DataHandlerService
     */
    protected $handlerService;
    
    /**
     * RecordDataHandler constructor.
     *
     * @param   string                                                  $tableName
     * @param   \LaborDigital\T3ba\Tool\DataHandler\DataHandlerService  $handlerService
     */
    public function __construct(string $tableName, DataHandlerService $handlerService)
    {
        $this->tableName = $tableName;
        $this->handlerService = $handlerService;
    }
    
    /**
     * Returns the instance of the data handler service
     *
     * @return \LaborDigital\T3ba\Tool\DataHandler\DataHandlerService
     */
    public function getHandlerService(): DataHandlerService
    {
        return $this->handlerService;
    }
    
    /**
     * Creates a new record in the database.
     *
     * @param   array             $data      The record row to add to the database
     * @param   int|null          $pid       An optional pid to store the record on
     * @param   bool|string|null  $force     True to force the execution as _t3ba_adminUser_
     *                                       'soft': Keeps the current user but sets the "admin" flag in data handler
     *                                       false|null: Don't force the execution -> default
     *                                       WARNING: this ignores all permissions or access rights!
     *
     * @return int The uid of the new record
     */
    public function save(array $data, ?int $pid = null, $force = null): int
    {
        if ($pid !== null) {
            $data['pid'] = $pid;
        }
        
        $isNew = false;
        if (! isset($data['uid']) || ! is_numeric($data['uid'])) {
            $isNew = true;
            $data['uid'] = StringUtility::getUniqueId('NEW_');
        }
        
        $uid = $data['uid'];
        unset($data['uid']);
        
        $handler = $this->handlerService->processData([
            $this->tableName => [
                $uid => $data,
            ],
        ], [], $force);
        
        if (! $isNew) {
            return (int)$uid;
        }
        
        return reset($handler->substNEWwithIDs);
    }
    
    /**
     * Creates a copy of a certain record. If the $targetPageId is empty, the copy will be created on the current page.
     * Otherwise it will be copied as a child of the given target page.
     *
     * @param   int               $uid        The record id to copy
     * @param   int|null          $targetPid  The page id to copy record to. If left empty the record copy will
     *                                        be created on the current page
     * @param   bool|string|null  $force      True to force the execution as _t3ba_adminUser_
     *                                        'soft': Keeps the current user but sets the "admin" flag in data handler
     *                                        false|null: Don't force the execution -> default
     *                                        WARNING: this ignores all permissions or access rights!
     *
     * @return int
     */
    public function copy(int $uid, ?int $targetPid = null, $force = null): int
    {
        $handler = $this->handlerService->processCommands([
            $this->tableName => [
                $uid => [
                    'copy' => $targetPid ?? -$uid,
                ],
            ],
        ], [], $force);
        
        return $handler->copyMappingArray[$this->tableName][$uid];
    }
    
    /**
     * Moves a record with the given uid to another page
     *
     * @param   int               $uid        The record uid to move
     * @param   int               $targetPid  The page id to move the page to
     * @param   bool|string|null  $force      True to force the execution as _t3ba_adminUser_
     *                                        'soft': Keeps the current user but sets the "admin" flag in data handler
     *                                        false|null: Don't force the execution -> default
     *                                        WARNING: this ignores all permissions or access rights!
     *
     * @return void
     */
    public function move(int $uid, int $targetPid, $force = null): void
    {
        $this->handlerService->processCommands([
            $this->tableName => [
                $uid => [
                    'move' => $targetPid,
                ],
            ],
        ], [], $force);
    }
    
    /**
     * Marks a record as "deleted". It still can be restored using the restore() method.
     *
     * @param   int               $uid
     * @param   bool|string|null  $force  True to force the execution as _t3ba_adminUser_
     *                                    'soft': Keeps the current user but sets the "admin" flag in data handler
     *                                    false|null: Don't force the execution -> default
     *                                    WARNING: this ignores all permissions or access rights!
     */
    public function delete(int $uid, $force = null): void
    {
        $this->handlerService->processCommands([
            $this->tableName => [
                $uid => [
                    'delete' => 1,
                ],
            ],
        ], [], $force);
    }
    
    /**
     * Restores a record by removing the marker that defines it as "deleted".
     *
     * @param   int               $uid    The uid of the record to restore
     * @param   bool|string|null  $force  True to force the execution as _t3ba_adminUser_
     *                                    'soft': Keeps the current user but sets the "admin" flag in data handler
     *                                    false|null: Don't force the execution -> default
     *                                    WARNING: this ignores all permissions or access rights!
     */
    public function restore(int $uid, $force = null): void
    {
        $this->handlerService->processCommands([
            $this->tableName => [
                $uid => [
                    'undelete' => 0,
                ],
            ],
        ], [], $force);
    }
}
