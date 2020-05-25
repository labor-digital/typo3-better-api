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
 * Last modified: 2020.03.19 at 18:43
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Addon;

use LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Backend\Form\Element\GroupElement;

class GroupElementsCanTriggerReloadApplier implements LazyEventSubscriberInterface
{
    use ChangeFunctionBuilderTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(BackendFormNodePostProcessorEvent::class, '__onPostProcess');
    }
    
    /**
     * This applier allows group elements to emit the page reload when they have changed.
     *
     * @param \LaborDigital\Typo3BetterApi\Event\Events\BackendFormNodePostProcessorEvent $event
     */
    public function __onPostProcess(BackendFormNodePostProcessorEvent $event)
    {
        if (!$event->getNode() instanceof GroupElement) {
            return;
        }
        $fieldChangeFunc = Arrays::getPath($event->getProxy()->getProperty('data'), ['parameterArray', 'fieldChangeFunc']);
        if (empty($fieldChangeFunc)) {
            return;
        }
        
        // Build the change function
        $result = $event->getResult();
        $result['html'] = $this->buildOnChangeFunction($result['html'], $fieldChangeFunc, [
            'eventToListenFor' => 'DOMNodeInserted',
        ]);
        $event->setResult($result);
    }
}
