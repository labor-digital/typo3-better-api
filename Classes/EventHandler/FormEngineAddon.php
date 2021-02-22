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
 * Last modified: 2020.11.01 at 22:35
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\DataHandler\DataHandlerDefaultFilterEvent;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodeFilterEvent;
use LaborDigital\T3BA\Event\FormEngine\BackendFormNodePostProcessorEvent;
use LaborDigital\T3BA\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3BA\FormEngine\Addon\DbBaseId;
use LaborDigital\T3BA\FormEngine\Addon\FalFileBaseDir;
use LaborDigital\T3BA\FormEngine\Addon\FieldDefaultAndPlaceholderTranslation;
use LaborDigital\T3BA\FormEngine\Addon\GroupElementsCanTriggerReload;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class FormEngineAddon implements LazyEventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(BackendFormNodeFilterEvent::class, 'onNodeFilter');
        $subscription->subscribe(BackendFormNodePostProcessorEvent::class, 'onPostProcess');
        $subscription->subscribe(FormFilterEvent::class, 'onFormFilter', ['priority' => 200]);
        $subscription->subscribe(DataHandlerDefaultFilterEvent::class, 'onDefaultFilter');
    }

    public function onNodeFilter(BackendFormNodeFilterEvent $event): void
    {
        FalFileBaseDir::onNodeFilter($event);
    }

    public function onPostProcess(BackendFormNodePostProcessorEvent $event): void
    {
        DbBaseId::onPostProcess($event);
        FalFileBaseDir::onPostProcess($event);
        GroupElementsCanTriggerReload::onPostProcess($event);
    }

    public function onFormFilter(FormFilterEvent $event): void
    {
        FieldDefaultAndPlaceholderTranslation::onFormFilter($event);
    }
    
    public function onDefaultFilter(DataHandlerDefaultFilterEvent $event): void
    {
        FieldDefaultAndPlaceholderTranslation::onDefaultFilter($event);
    }
}
