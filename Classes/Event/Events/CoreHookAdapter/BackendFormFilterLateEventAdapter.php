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
 * Last modified: 2020.03.18 at 14:52
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;

use LaborDigital\Typo3BetterApi\Event\Events\BackendFormFilterLateEvent;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineConfiguration;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaInlineIsOnSymmetricSide;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaInputPlaceholders;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;

class BackendFormFilterLateEventAdapter extends AbstractCoreHookEventAdapter implements FormDataProviderInterface
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][static::class] = [
            'depends' => [TcaInputPlaceholders::class],
            'before'  => [TcaInlineIsOnSymmetricSide::class],
        ];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['inlineParentRecord'][static::class] = [
            'depends' => [TcaInlineConfiguration::class],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function addData(array $result)
    {
        if (!isset($result['tableName'])) {
            return $result;
        }
        static::$bus->dispatch(($e = new BackendFormFilterLateEvent($result['tableName'], $result)));
        return $e->getData();
    }
}
