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
 * Last modified: 2021.04.26 at 17:09
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\Backend;


class BackendUtilityRecordFilterEvent extends AbstractBackendUtilityRecordEvent
{
    /**
     * An array containing the found column, or null if nothing could be resolved
     *
     * @var array|null
     */
    protected $row;

    public function __construct(
        string $tableName,
        int $uid,
        string $fields,
        string $where,
        bool $useDeleteClause,
        ?array $row
    ) {
        parent::__construct($tableName, $uid, $fields, $where, $useDeleteClause);
        $this->row = $row;
    }

    /**
     * Returns an array containing the found column, or null if nothing could be resolved
     *
     * @return array|null
     */
    public function getRow(): ?array
    {
        return $this->row;
    }

    /**
     * Allows you to update the array containing the found column. Can be set to null if nothing could be resolved
     *
     * @param   array|null  $row
     *
     * @return BackendUtilityRecordFilterEvent
     */
    public function setRow(?array $row): BackendUtilityRecordFilterEvent
    {
        $this->row = $row;

        return $this;
    }
}
