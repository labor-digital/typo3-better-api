<?php
/*
 * Copyright 2020 Martin Neundorfer (Neunerlei)
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
 * Last modified: 2020.08.09 at 14:49
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

namespace LaborDigital\T3BA\Core\Override;

use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerDbFieldsFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerRecordInfoFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Core\DataHandling\BetterApiClassOverrideCopy__DataHandler;

class ExtendedDataHandler extends BetterApiClassOverrideCopy__DataHandler
{
    /**
     * @inheritDoc
     */
    public function recordInfo($table, $id, $fieldList)
    {
        /** @noinspection PhpParamsInspection */
        TypoEventBus::getInstance()->dispatch(($e = new DataHandlerRecordInfoFilterEvent(
            $table,
            $id,
            $fieldList,
            $this,
            function ($fieldList) use ($table, $id) {
                return parent::recordInfo($table, $id, $fieldList);
            }
        )));

        return call_user_func($e->getConcreteInfoProvider(), $e->getFieldList());
    }

    /**
     * @inheritDoc
     */
    public function updateDB($table, $id, $fieldArray)
    {
        /** @noinspection PhpParamsInspection */
        TypoEventBus::getInstance()->dispatch(($e = new DataHandlerDbFieldsFilterEvent(
            'update',
            $table,
            $fieldArray,
            $id,
            $this
        )));
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
    ) {
        /** @noinspection PhpParamsInspection */
        TypoEventBus::getInstance()->dispatch(($e = new DataHandlerDbFieldsFilterEvent(
            'insert',
            $table,
            $fieldArray,
            $id,
            $this,
            [
                'newVersion'        => $newVersion,
                'suggestedUid'      => $suggestedUid,
                'dontSetNewIdIndex' => $dontSetNewIdIndex,
            ]
        )));

        return parent::insertDB($e->getTableName(), $e->getId(), $e->getRow(), $newVersion, $suggestedUid,
            $dontSetNewIdIndex);
    }
}
