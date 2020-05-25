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
 * Last modified: 2020.03.19 at 01:52
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;

use LaborDigital\Typo3BetterApi\Event\Events\BackendFormActionContextFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class CustomElementContextFilter implements LazyEventSubscriberInterface
{
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormActionContextFilterEvent::class, '__filterFormActionHandlerContextForCustomElements');
    }
    
    /**
     * This is an internal event which hooks into the BackendActionHandler and updates the
     * used context class for custom elements
     *
     * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormActionContextFilterEvent $event
     */
    public function __filterFormActionHandlerContextForCustomElements(BackendFormActionContextFilterEvent $event)
    {
        // Ignore everything that applies to a table
        if ($event->getConfig()['appliesToTable']) {
            return;
        }
        
        // Ignore everything that is not a custom element
        if (Arrays::getPath($event->getConfig(), ['config', 'config', 'renderType']) !== 'betterApiCustomElement') {
            return;
        }
        
        // Update the context object
        $event->setContextClass(CustomElementFormActionContext::class);
    }
}
