<?php
/*
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
 * Last modified: 2020.10.18 at 18:00
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\DataHandler\Adapter;

use LaborDigital\T3BA\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use LaborDigital\T3BA\Event\DataHandler\ActionFilterEvent;
use LaborDigital\T3BA\Event\DataHandler\ActionPostProcessorEvent;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class ActionEventAdapter extends AbstractCoreHookEventAdapter
{
    
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][static::class]
            = static::class;
    }
    
    public function processCmdmap_preProcess(&$command, &$table, &$id, &$value, $parent, &$pasteUpdate): void
    {
        static::$bus->dispatch(($e = new ActionFilterEvent($command, $table, $id, $value, $pasteUpdate,
            $parent)));
        $command = $e->getCommand();
        $table = $e->getTableName();
        $id = $e->getId();
        $value = $e->getValue();
        $pasteUpdate = $e->getPasteSpecialData();
    }
    
    public function processCmdmap_postProcess(
        $command,
        $table,
        $id,
        $value,
        DataHandler $parent,
        &$pasteUpdate,
        &$pasteDataMap
    ): void
    {
        // Make sure to extract the new uid when a record was copied
        $newElementId = -1;
        if ($command === 'copy' || $command === 'copyToLanguage') {
            $newElementId = Arrays::getPath($parent->copyMappingArray, [$table, $id], $newElementId);
        }
        
        // Emit the event
        static::$bus->dispatch(($e = new ActionPostProcessorEvent(
            $command,
            $table,
            $id,
            $newElementId,
            $value,
            $pasteUpdate,
            $pasteDataMap,
            $parent
        )));
        $pasteDataMap = $e->getPasteDataMap();
        $pasteUpdate = $e->getPasteSpecialData();
    }
}
