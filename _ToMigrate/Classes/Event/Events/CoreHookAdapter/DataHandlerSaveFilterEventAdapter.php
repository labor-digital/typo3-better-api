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
 * Last modified: 2020.03.19 at 12:24
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;

use Exception;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSaveFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSavePostProcessorEvent;
use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DataHandlerSaveFilterEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][static::class]
            = static::class;
    }
    
    public function processDatamap_preProcessFieldArray(array &$fieldArray, $table, &$id, DataHandler $pObj)
    {
        // Ignore this if only the visibility is toggled >= 8.7.20 ?
        if (array_keys($fieldArray) === ['hidden']) {
            return;
        }
        
        // Handle exceptions as backend error messages
        try {
            static::$bus->dispatch(($e = new DataHandlerSaveFilterEvent(
                $fieldArray,
                $table,
                $id,
                $pObj
            )));
            $fieldArray = $e->getRow();
            $id         = $e->getId();
        } catch (Exception $e) {
            // Handle error messages if required
            if ($pObj->enableLogging) {
                $pObj->log('', 0, 0, 0, 1,
                    static::$container->get(TranslationService::class)->translateMaybe($e->getMessage()));
            }
            
            $fieldArray = [];
        }
    }
    
    public function processDatamap_postProcessFieldArray($status, $table, &$id, &$fieldArray, DataHandler $pObj)
    {
        // Ignore this if only the visibility is toggled >= 8.7.20 ?
        if (array_keys($fieldArray) === ['hidden']) {
            return;
        }
        
        // Handle exceptions as backend error messages
        try {
            static::$bus->dispatch(($e = new DataHandlerSavePostProcessorEvent(
                $status,
                $table,
                $id,
                $fieldArray,
                $pObj
            )));
            $id = $e->getId();
            $fieldArray = $e->getRow();
        } catch (Exception $e) {
            // Handle error messages if required
            if ($pObj->enableLogging) {
                $pObj->log('', 0, 0, 0, 1,
                    static::$container->get(TranslationService::class)->translateMaybe($e->getMessage()));
            }
            
            $fieldArray = [];
        }
    }
}
