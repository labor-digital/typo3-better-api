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
 * Last modified: 2020.08.24 at 20:33
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\EventHandler;


use LaborDigital\T3BA\Core\Event\ExtConfigLoadedEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\StandAloneHandlerInterface;
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
    }

    /**
     * Executes the ext config loader and provides the config state to the container
     */
    public function onExtConfigLoaded(): void
    {
        $loader = $this->configService->makeLoader('extLocalConf');
        $loader->setHandlerFinder(new FilteredHandlerFinder([StandAloneHandlerInterface::class], []));
        $loader->setContainer($this->container);
        $loader->setCache(null); // @todo remove this
        $state = $loader->load();

        // Inject the state into the container
        $this->container->set(ConfigState::class, $state);
    }

}
