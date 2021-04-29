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
 * Last modified: 2021.04.26 at 17:07
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\Override;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\Backend\BackendUtilityRecordFilterEvent;
use TYPO3\CMS\Backend\Utility\T3BA__Copy__BackendUtility;

class ExtendedBackendUtility extends T3BA__Copy__BackendUtility
{
    /**
     * @inheritDoc
     */
    public static function getRecord($table, $uid, $fields = '*', $where = '', $useDeleteClause = true)
    {
        $row = parent::getRecord($table, $uid, $fields, $where, $useDeleteClause);
        
        return TypoEventBus::getInstance()->dispatch(new BackendUtilityRecordFilterEvent(
            $table, (int)$uid, $fields, $where, $useDeleteClause, $row
        ))->getRow();
    }
}
