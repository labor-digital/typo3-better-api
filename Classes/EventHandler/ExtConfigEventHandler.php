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
 * Last modified: 2020.08.24 at 21:57
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\Core\ExtConfigLoadedEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\StandAloneHandlerInterface;
use Neunerlei\Configuration\Event\AfterConfigLoadEvent;
use Neunerlei\Configuration\Event\BeforeConfigLoadEvent;
use Neunerlei\Configuration\Finder\FilteredHandlerFinder;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Psr\Container\ContainerInterface;

class ExtConfigEventHandler implements LazyEventSubscriberInterface
{

    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigService
     */
    protected $configService;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * True when the config state was injected into the container
     *
     * @var bool
     */
    protected $configStateInjected = false;

    /**
     * ExtConfigEventHandler constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigService  $configService
     * @param   \Psr\Container\ContainerInterface              $container
     */
    public function __construct(ExtConfigService $configService, ContainerInterface $container)
    {
        $this->configService = $configService;
        $this->container     = $container;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtConfigLoadedEvent::class, 'onExtConfigLoaded', ['priority' => 100]);
        $subscription->subscribe(BeforeConfigLoadEvent::class, 'onBeforeConfigLoad', ['priority' => 100]);
        $subscription->subscribe(AfterConfigLoadEvent::class, 'onAfterConfigLoad', ['priority' => 100]);
    }

    /**
     * Provides the ext config main configuration state to the container instance
     *
     * @param   \Neunerlei\Configuration\Event\BeforeConfigLoadEvent  $event
     */
    public function onBeforeConfigLoad(BeforeConfigLoadEvent $event): void
    {
        if ($this->configStateInjected || $event->getLoaderContext()->type !== 'ExtConfigMain') {
            return;
        }

        $this->configStateInjected = true;
        $this->container->set(ConfigState::class, $event->getLoaderContext()->configContext->getState());
    }

    /**
     * Inject the state into the container after we have initialized it
     *
     * @param   \Neunerlei\Configuration\Event\AfterConfigLoadEvent  $event
     */
    public function onAfterConfigLoad(AfterConfigLoadEvent $event): void
    {
        if ($event->getLoaderContext()->type === 'ExtConfigMain') {
            $this->container->set(ConfigState::class, $event->getState());
        }
    }

    /**
     * Executes the ext config loader
     */
    public function onExtConfigLoaded(): void
    {
        $loader = $this->configService->makeLoader('ExtConfigMain');
        $loader->setHandlerFinder(new FilteredHandlerFinder([StandAloneHandlerInterface::class], []));
        $loader->setContainer($this->container);
        $loader->setCache(null); // @todo remove this
        $loader->load();
    }

}
