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
 * Last modified: 2020.03.19 at 13:54
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;

use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class FixSectionToggleStateApplier implements LazyEventSubscriberInterface
{
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormNodeFilterEvent::class, '__onNodeDataFilter');
    }
    
    /**
     * This applier fixes an issue with the typo3 flex form sections.
     * For some reason they don't get their OPEN/CLOSED states applied, even if they are stored correctly
     * in the database.
     *
     * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodeFilterEvent $event
     *
     * @todo Keep track, if this is fixed for a TYPO3 version > 9
     */
    public function __onNodeDataFilter(BackendFormNodeFilterEvent $event)
    {
        $data = $event->getProxy()->getProperty('data');
        if (!isset($data['parameterArray']) || $data['renderType'] !== 'flexFormSectionContainer' ||
            !is_array($data['flexFormRowData'])) {
            return;
        }
        
        // Update the data for this section
        foreach ($data['flexFormRowData'] as $k => $v) {
            if (!isset($v['_TOGGLE']) || !is_array($v) || key($v) === '_TOGGLE') {
                continue;
            }
            $data['flexFormRowData'][$k] = Arrays::setPath($v, key($v) . '.el._TOGGLE', $v['_TOGGLE']);
        }
        $event->getProxy()->setProperty('data', $data);
    }
}
