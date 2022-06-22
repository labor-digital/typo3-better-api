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


namespace LaborDigital\T3ba\ExtConfigHandler\Table\ContentType;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DataHandlerAdapter extends DataHandler implements NoDiInterface
{
    /**
     * Because TYPO3 gives me no good angle of attack to in DataHandler::compareFieldArrayWithCurrentAndUnset,
     * I must fall back to postprocess the history records of the extended content fields here.
     *
     * This is fix is required for the sys_history to work correctly with extension columns.
     *
     * Yes, this is not perfect, and I SHOULD do it in the aforementioned method, to be safe for updates.
     * but, it works for now.
     *
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     * @param   int                                       $id
     * @param   array                                     $childFields
     * @param   array|null                                $additionalRewrites
     */
    public static function rewriteHistory(DataHandler $dataHandler, int $id, array $childFields, ?array $additionalRewrites = null): void
    {
        $key = 'tt_content:' . $id;
        $record = &$dataHandler->historyRecords[$key];
        
        // This construct is needed to rewrite the extension fields back into the tt_content table.
        // Theoretically, with the new construct, the other code in this method
        // should no longer be required.
        if (is_array($additionalRewrites) && is_array($additionalRewrites[$key] ?? null)) {
            foreach ($additionalRewrites[$key] as $childKey => $cType) {
                if (isset($dataHandler->historyRecords[$childKey])) {
                    if (! is_array($record)) {
                        $record = [];
                    }
                    
                    $childRecord = $dataHandler->historyRecords[$childKey];
                    foreach (['oldRecord', 'newRecord'] as $recordType) {
                        $remapped = ContentTypeUtil::remapColumns($childRecord[$recordType] ?? [], $cType, true);
                        $record[$recordType] = array_merge($record[$recordType] ?? [], $remapped);
                    }
                    
                    unset($dataHandler->historyRecords[$childKey]);
                }
            }
        }
        
        // @todo in v11 evaluate if the following code can be removed
        if (! $record) {
            return;
        }
        
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
