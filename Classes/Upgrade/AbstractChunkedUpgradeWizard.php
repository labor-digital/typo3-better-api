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
 * Last modified: 2021.07.19 at 23:40
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Upgrade;


use LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\DataHandler\DataHandlerService;
use LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;

/**
 * Class AbstractChunkedUpgradeWizard
 *
 * Specific implementation that helps with batch upgrades of a single table.
 * The data can be processed in chunks of a variable size, limiting the memory usage considerably.
 * Use the protected properties to define the chunk query and execute the loop like this:
 *
 * public function executeUpdate(): bool
 * {
 *      while($chunk = $this->getChunk()){
 *          foreach ($chunk as $row){
 *              // Do something with $row here!
 *          }
 *      }
 * }
 *
 * @package LaborDigital\T3ba\Upgrade
 */
abstract class AbstractChunkedUpgradeWizard extends AbstractUpgradeWizard
{
    /**
     * The name of the table that should be processed
     *
     * @var string
     */
    protected $tableName = 'tt_content';
    
    /**
     * A where array, that is compatible with {@see StandaloneBetterQuery::withWhere}
     *
     * @var int[]
     */
    protected $where = ['uid !=' => -1];
    
    /**
     * The fields to select for each row. Can be NULL to retrieve the whole database row
     *
     * @var string[]|null
     */
    protected $selectFields = ['uid', 'pid'];
    
    /**
     * Defines how many rows should be processed in a single chunk.
     * Can be lowered if you work with rows with a lot of content.
     *
     * @var int
     */
    protected $chunkSize = 200;
    
    /**
     * Defines if queries should contain hidden records as well
     *
     * @var bool
     */
    protected $includeHidden = true;
    
    /**
     * Defines if queries should contain deleted records as well
     *
     * @var bool
     */
    protected $includeDeleted = false;
    
    /**
     * The number of all rows that have to be processed
     *
     * @var int
     * @readonly
     */
    protected $count = 0;
    
    /**
     * The retrieved uids of the chunks to process
     *
     * @var mixed
     * @private
     */
    protected $chunks;
    
    /**
     * Returns the chunk that should be processed next, or null if the end of the list was reached
     *
     * @return array|null
     */
    protected function getChunk(): ?array
    {
        $query = $this->getQuery();
        
        if (! isset($this->chunks)) {
            $rows = $query->withWhere($this->where, 'mainQuery')
                          ->withOrder(['uid' => 'asc'])
                          ->getAll(['uid']);
            $rows = array_column($rows, 'uid');
            $this->count = count($rows);
            $this->chunks = array_chunk($rows, $this->chunkSize);
        }
        
        $chunkUids = array_shift($this->chunks);
        if (empty($chunkUids)) {
            return null;
        }
        
        return $query->withWhere(['uid in' => $chunkUids])->getAll($this->selectFields);
    }
    
    /**
     * Resets the current main table and flushes the internal caches
     *
     * @param   string  $tableName
     */
    protected function setMainTable(string $tableName): void
    {
        $this->tableName = $tableName;
        $this->reset();
    }
    
    /**
     * Clears the local caches
     */
    protected function reset(): void
    {
        $this->count = 0;
        $this->chunks = null;
    }
    
    /**
     * Returns the instance of the database service
     *
     * @return \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected function getDbService(): DbService
    {
        return $this->cs()->di->cs()->db;
    }
    
    /**
     * Returns an instance of a record data handler either for the main table of this upgrade wizard,
     * or the table provided as an argument.
     *
     * @param   string|null  $tableName  If empty, the main table is used, otherwise the name of the database table
     *                                   to create the record data handler for
     *
     * @return \LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler
     */
    protected function getDataHandler(?string $tableName = null): RecordDataHandler
    {
        return TypoContext::getInstance()->di()->getService(DataHandlerService::class)
                          ->getRecordDataHandler($tableName ?? $this->tableName);
    }
    
    /**
     * Returns a new instance of a query for either either for the main table of this upgrade wizard,
     * or the table provided as an argument.
     *
     * @param   string|null  $tableName  If empty, the main table is used, otherwise the name of the database table
     *                                   to create the query for
     *
     * @return \LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\StandaloneBetterQuery
     */
    protected function getQuery(?string $tableName = null): StandaloneBetterQuery
    {
        $query = TypoContext::getInstance()->di()->cs()->db
            ->getQuery($tableName ?? $this->tableName);
        
        if ($this->includeHidden) {
            $query = $query->withIncludeHidden();
        }
        
        if ($this->includeDeleted) {
            $query = $query->withIncludeDeleted();
        }
        
        return $query;
    }
}