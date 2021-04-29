<?php
/*
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
 * Last modified: 2020.09.08 at 18:04
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHandler\Record;


use LaborDigital\T3BA\Tool\DataHandler\DataHandlerService;

/**
 * Class RecordDataHandler
 *
 * A simple abstraction to perform record actions using the data handler in an object oriented way
 *
 * @package LaborDigital\T3BA\Tool\DataHandler\Record
 */
class RecordDataHandler
{
    
    /**
     * The table name to handle the actions
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * @var \LaborDigital\T3BA\Tool\DataHandler\DataHandlerService
     */
    protected $handlerService;
    
    /**
     * RecordDataHandler constructor.
     *
     * @param   string                                                  $tableName
     * @param   \LaborDigital\T3BA\Tool\DataHandler\DataHandlerService  $handlerService
     */
    public function __construct(string $tableName, DataHandlerService $handlerService)
    {
        $this->tableName = $tableName;
        $this->handlerService = $handlerService;
    }
    
    /**
     * Returns the instance of the data handler service
     *
     * @return \LaborDigital\T3BA\Tool\DataHandler\DataHandlerService
     */
    public function getHandlerService(): DataHandlerService
    {
        return $this->handlerService;
    }
    
    /**
     * Creates a new record in the database.
     *
     * @param   array     $data       The record row to add to the database
     * @param   int|null  $pid        An optional pid to store the record on
     * @param   bool      $force      If set to true, the record is created as forced admin user,
     *                                ignoring all permissions or access rights!
     *
     * @return int The uid of the new record
     */
    public function save(array $data, ?int $pid = null, bool $force = false): int
    {
        if ($pid !== null) {
            $data['pid'] = $pid;
        }
        
        $isNew = false;
        if (! isset($data['uid']) || ! is_numeric($data['uid'])) {
            $isNew = true;
            $data['uid'] = 'NEW_1';
        }
        
        $uid = $data['uid'];
        unset($data['uid']);
        
        $handler = $this->handlerService->processData([
            $this->tableName => [
                $uid => $data,
            ],
        ], [], $force);
        
        if (! $isNew) {
            return $uid;
        }
        
        return reset($handler->substNEWwithIDs);
    }
    
    /**
     * Creates a copy of a certain record. If the $targetPageId is empty, the copy will be created on the current page.
     * Otherwise it will be copied as a child of the given target page.
     *
     * @param   int       $uid        The record id to copy
     * @param   int|null  $targetPid  The page id to copy record to. If left empty the record copy will
     *                                be created on the current page
     * @param   bool      $force      If set to true, the record is copied as forced admin user,
     *                                ignoring all permissions or access rights!
     *
     * @return int
     */
    public function copy(int $uid, ?int $targetPid = null, bool $force = false): int
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
     * @param   int   $uid        The record uid to move
     * @param   int   $targetPid  The page id to move the page to
     * @param   bool  $force      If set to true, the record is moved as forced admin user,
     *                            ignoring all permissions or access rights!
     *
     * @return void
     */
    public function move(int $uid, int $targetPid, bool $force = false): void
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
     * @param   int   $uid
     * @param   bool  $force   If set to true, the record is restored as forced admin user,
     *                         ignoring all permissions or access rights!
     */
    public function delete(int $uid, bool $force = false): void
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
     * @param   int   $uid     The uid of the record to restore
     * @param   bool  $force   If set to true, the record is restored as forced admin user,
     *                         ignoring all permissions or access rights!
     */
    public function restore(int $uid, bool $force = false): void
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
