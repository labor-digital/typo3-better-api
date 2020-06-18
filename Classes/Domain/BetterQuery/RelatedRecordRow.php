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
 * Last modified: 2020.03.25 at 21:16
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;

/**
 * Class RelatedRecordRow
 *
 * The default result when StandaloneBetterQuery::findRelated() is used to find related rows
 *
 * @package LaborDigital\Typo3BetterApi\Domain\BetterQuery
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
     * RelatedRecordRow constructor.
     *
     * @param   int     $uid
     * @param   string  $tableName
     * @param   array   $row
     */
    public function __construct(int $uid, string $tableName, array $row)
    {
        $this->uid       = $uid;
        $this->tableName = $tableName;
        $this->row       = $row;
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
}
