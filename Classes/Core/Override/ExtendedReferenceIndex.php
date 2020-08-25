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
 * Last modified: 2020.04.14 at 12:56
 */

namespace LaborDigital\T3BA\Core\Override;

use LaborDigital\Typo3BetterApi\Event\Events\RefIndexRecordDataFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Core\Database\BetterApiClassOverrideCopy__ReferenceIndex;

class ExtendedReferenceIndex extends BetterApiClassOverrideCopy__ReferenceIndex
{

    /**
     * @inheritDoc
     */
    protected function getRecordRawCached(string $tableName, int $uid)
    {
        // Store the current cache list length to detect changes
        $listLength    = count($this->recordCache);
        $row           = parent::getRecordRawCached($tableName, $uid);
        $listLengthNow = count($this->recordCache);

        // Detect changes
        if ($listLength === $listLengthNow) {
            return $row;
        }

        // Get changed id
        end($this->recordCache);
        $id = key($this->recordCache);

        // Allow post processing
        $hasRow = is_array($row);
        if (! $hasRow) {
            $row = [];
        }
        TypoEventBus::getInstance()->dispatch(($e = new RefIndexRecordDataFilterEvent($tableName, $uid, $row)));
        if ($hasRow) {
            $this->recordCache[$id] = $e->getRow();
        }

        // Done
        return $e->getRow();
    }
}
