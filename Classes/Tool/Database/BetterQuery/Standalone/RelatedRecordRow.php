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

namespace LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone;

use LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;

/**
 * Class RelatedRecordRow
 *
 * The default result when StandaloneBetterQuery::findRelated() is used to find related rows
 *
 * @package LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone
 */
class RelatedRecordRow
{
    
    /**
     * The unique id of the record in this row
     *
     * @var int
     */
    protected $uid;
    
    /**
     * The name of the table this row comes from
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The raw database row that was fetched
     *
     * @var array
     */
    protected $row;
    
    /**
     * A map of table names to the matching extbase models
     *
     * @var array|null
     */
    protected $modelMap;
    
    /**
     * RelatedRecordRow constructor.
     *
     * @param   int         $uid
     * @param   string      $tableName
     * @param   array       $row
     * @param   array|null  $modelMap
     */
    public function __construct(int $uid, string $tableName, array $row, ?array $modelMap)
    {
        $this->uid = $uid;
        $this->tableName = $tableName;
        $this->row = $row;
        $this->modelMap = $modelMap;
    }
    
    /**
     * Returns the unique id of the record in this row
     *
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }
    
    /**
     * Returns the name of the table this row comes from
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the raw database row that was fetched
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Returns the row as a mapped extbase object
     *
     * @return \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|mixed
     * @throws \LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException
     */
    public function getModel(): AbstractEntity
    {
        if (empty($this->modelMap)) {
            throw new BetterQueryException(
                'You can\'t require the relations as model, because you did not configure a model map while using getRelated()');
        }
        if (! isset($this->modelMap[$this->getTableName()])) {
            throw new BetterQueryException(
                'Could not hydrate a related row for table: ' . $this->getTableName()
                . ' because it was not mapped to a model');
        }
        $objects = GeneralUtility::getContainer()
                                 ->get(DataMapper::class)
                                 ->map($this->modelMap[$this->getTableName()], [$this->row]);
        
        return reset($objects);
    }
}
