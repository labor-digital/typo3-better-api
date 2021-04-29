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
 * Last modified: 2021.04.26 at 20:29
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table\ContentType;


use TYPO3\CMS\Core\DataHandling\DataHandler;

class DataHandlerAdapter extends DataHandler
{
    /**
     * Because TYPO3 gives me no good angle of attack to in DataHandler::compareFieldArrayWithCurrentAndUnset,
     * I must fall back to postprocess the history records of the extended content fields here.
     *
     * This is fix is required for the sys_history to work correctly with extension columns.
     *
     * Yes, this is not perfect and I SHOULD do it in the aforementioned method, to be save for updates.
     * but, it works for now.
     *
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     * @param   int                                       $id
     * @param   array                                     $childFields
     */
    public static function rewriteHistory(DataHandler $dataHandler, int $id, array $childFields): void
    {
        $record = &$dataHandler->historyRecords['tt_content:' . $id];
        $record['oldRecord'] = array_merge(
            $record['oldRecord'],
            array_filter($childFields)
        );
        
        foreach ($record['oldRecord'] as $k => $v) {
            if (isset($record['newRecord'][$k]) && $record['newRecord'][$k] === $v) {
                unset($record['oldRecord'][$k], $record['newRecord'][$k]);
            }
        }
    }
}
