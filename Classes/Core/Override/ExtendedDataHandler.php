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
 * Last modified: 2020.03.19 at 13:25
 */

namespace LaborDigital\T3ba\Core\Override;

use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerCompareFieldArrayFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerDbFieldsFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerDefaultFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerRecordInfoFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\DataHandlerRecordInfoWithPermsFilterEvent;
use TYPO3\CMS\Core\DataHandling\T3BaCopyDataHandler;

class ExtendedDataHandler extends T3BaCopyDataHandler
{
    /**
     * @inheritDoc
     */
    public function recordInfo($table, $id, $fieldList)
    {
        /** @noinspection PhpParamsInspection */
        $e = TypoEventBus::getInstance()->dispatch(new DataHandlerRecordInfoFilterEvent(
            $table,
            $id,
            $fieldList,
            $this,
            function ($fieldList, $table, $id) {
                return parent::recordInfo($table, $id, $fieldList);
            }
        ));
        
        return call_user_func($e->getConcreteInfoProvider(), $e->getFieldList(), $table, $id);
    }
    
    /**
     * @inheritDoc
     */
    public function newFieldArray($table)
    {
        /** @noinspection PhpParamsInspection */
        return TypoEventBus::getInstance()->dispatch(new DataHandlerDefaultFilterEvent(
            $table, parent::newFieldArray($table), $this
        ))->getRow();
    }
    
    /**
     * @inheritDoc
     */
    public function updateDB($table, $id, $fieldArray)
    {
        /** @noinspection PhpParamsInspection */
        $e = TypoEventBus::getInstance()->dispatch(new DataHandlerDbFieldsFilterEvent(
            'update',
            $table,
            $fieldArray,
            $id,
            $this
        ));
        
        parent::updateDB($e->getTableName(), $e->getId(), $e->getRow());
    }
    
    /**
     * @inheritDoc
     */
    public function insertDB(
        $table,
        $id,
        $fieldArray,
        $newVersion = false,
        $suggestedUid = 0,
        $dontSetNewIdIndex = false
    )
    {
        /** @noinspection PhpParamsInspection */
        $e = TypoEventBus::getInstance()->dispatch(new DataHandlerDbFieldsFilterEvent(
            'insert',
            $table,
            $fieldArray,
            $id,
            $this,
            [
                'newVersion' => $newVersion,
                'suggestedUid' => $suggestedUid,
                'dontSetNewIdIndex' => $dontSetNewIdIndex,
            ]
        ));
        
        return parent::insertDB(
            $e->getTableName(),
            $e->getId(),
            $e->getRow(),
            $newVersion,
            $suggestedUid,
            $dontSetNewIdIndex
        );
    }
    
    /**
     * @inheritDoc
     */
    protected function recordInfoWithPermissionCheck(string $table, int $id, $perms, string $fieldList = '*')
    {
        $result = parent::recordInfoWithPermissionCheck($table, $id, $perms, $fieldList);
        
        /** @noinspection PhpParamsInspection */
        return TypoEventBus::getInstance()->dispatch(new DataHandlerRecordInfoWithPermsFilterEvent(
            $table, $id, $fieldList, $this, $result, $perms
        ))->getResult();
    }
    
    /**
     * @inheritDoc
     */
    public function compareFieldArrayWithCurrentAndUnset($table, $id, $fieldArray)
    {
        $e = TypoEventBus::getInstance()->dispatch(
            new DataHandlerCompareFieldArrayFilterEvent($table, $id, $fieldArray, function ($t, $i, $f) {
                return parent::compareFieldArrayWithCurrentAndUnset($t, $i, $f);
            })
        );
        
        return ($e->getConcreteComparator())($table, $id, $fieldArray);
    }
}
