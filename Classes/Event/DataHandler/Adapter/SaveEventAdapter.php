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
 * Last modified: 2021.06.25 at 21:12
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Event\DataHandler\Adapter;


use ArgumentCountError;
use LaborDigital\T3ba\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use LaborDigital\T3ba\Event\DataHandler\SaveAfterDbOperationsEvent;
use LaborDigital\T3ba\Event\DataHandler\SaveFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\SavePostProcessorEvent;
use LaborDigital\T3ba\Tool\Translation\Translator;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Throwable;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class SaveEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][static::class]
            = static::class;
    }
    
    public function processDatamap_preProcessFieldArray(array &$fieldArray, $table, &$id, DataHandler $pObj): void
    {
        // Ignore this if only the visibility is toggled >= 8.7.20 ?
        if (array_keys($fieldArray) === ['hidden']) {
            return;
        }
        
        try {
            $this->EventBus()->dispatch(($e = new SaveFilterEvent(
                $fieldArray,
                $table,
                $id,
                $pObj
            )));
            $fieldArray = $e->getRow();
            $id = $e->getId();
        } catch (Throwable $e) {
            $this->handleErrorMessages($e, $pObj);
            $fieldArray = [];
        }
    }
    
    public function processDatamap_postProcessFieldArray($status, $table, &$id, &$fieldArray, DataHandler $pObj): void
    {
        // Ignore this if only the visibility is toggled >= 8.7.20 ?
        if (array_keys($fieldArray) === ['hidden']) {
            return;
        }
        
        try {
            $this->EventBus()->dispatch(($e = new SavePostProcessorEvent(
                $status,
                $table,
                $id,
                $fieldArray,
                $pObj
            )));
            $id = $e->getId();
            $fieldArray = $e->getRow();
        } catch (Throwable $e) {
            $this->handleErrorMessages($e, $pObj);
            $fieldArray = [];
        }
    }
    
    public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, DataHandler $pObj): void
    {
        try {
            $id = $pObj->substNEWwithIDs[$id] ?? null;
            
            if ($id === null) {
                return;
            }
            
            $this->EventBus()->dispatch(($e = new SaveAfterDbOperationsEvent(
                $status,
                $table,
                $id,
                $fieldArray,
                $pObj
            )));
        } catch (Throwable $e) {
            $this->handleErrorMessages($e, $pObj);
        }
    }
    
    /**
     * Converts the given throwable into a readable error message for the backend user
     *
     * @param   \Throwable                                $e
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $pObj
     *
     * @throws \Throwable
     */
    protected function handleErrorMessages(Throwable $e, DataHandler $pObj): void
    {
        if ($e instanceof ServiceNotFoundException || $e instanceof ArgumentCountError) {
            throw $e;
        }
        
        if ($pObj->enableLogging) {
            $pObj->log('', 0, 0, 0, 1,
                $this->TypoContext()->di()->getService(Translator::class)->translate($e->getMessage()));
        }
    }
}
