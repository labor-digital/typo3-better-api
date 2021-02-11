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
 * Last modified: 2020.08.24 at 15:57
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Core\BootStage;

use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Event\InternalCreateDependencyInjectionContainerEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfigHandler\EventSubscriber\Handler;

class EventHandlerRegistrationStage implements BootStageInterface
{
    protected $isBound = false;

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(InternalCreateDependencyInjectionContainerEvent::class,
            [$this, 'onDiContainerBeingInstantiated'], ['priority' => -10]);
    }

    /**
     * Loads the event handler configuration and registers them into the event bus
     *
     * @param   \LaborDigital\T3BA\Event\InternalCreateDependencyInjectionContainerEvent  $event
     */
    public function onDiContainerBeingInstantiated(InternalCreateDependencyInjectionContainerEvent $event): void
    {
        // Don't rebind the events
        if ($this->isBound) {
            return;
        }
        $this->isBound = true;

        // Run the event bus loader
        $container = $event->getContainer();
        $eventBus  = $container->get(TypoEventBus::class);
        $loader    = $container->get(ExtConfigService::class)->makeLoader(ExtConfigService::EVENT_BUS_LOADER_KEY);
        $loader->setContainer($container);
        $loader->clearHandlerLocations();
        $loader->registerHandler(new Handler($eventBus));
        $state = $loader->load();

        // Register lazy subscribers
        /** @var \LaborDigital\T3BA\Core\EventBus\TypoListenerProvider $listenerProvider */
        $listenerProvider = $eventBus->getConcreteListenerProvider();
        foreach ($state->get('subscribers.lazy') as $lazySubscriber) {
            $listenerProvider->addListener(...$lazySubscriber);
        }

        // Register dynamic subscribers
        foreach ($state->get('subscribers.runtime') as $subscriber) {
            $eventBus->addSubscriber($container->get($subscriber));
        }
    }
}
