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
 * Last modified: 2021.01.13 at 19:52
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table;


use LaborDigital\T3BA\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3BA\ExtConfig\AbstractExtConfigApplier;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Psr\Container\ContainerInterface;

class ConfigureTcaTableApplier extends AbstractExtConfigApplier
{
    protected const CLASS_NAME_TABLE_MAP_CACHE_KEY = 'tca.classNameTableMap';

    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigService
     */
    protected $extConfigService;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * ConfigureTcaTableApplier constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigService  $extConfigService
     * @param   \Psr\Container\ContainerInterface              $container
     */
    public function __construct(ExtConfigService $extConfigService, ContainerInterface $container)
    {
        $this->extConfigService = $extConfigService;
        $this->container        = $container;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(TcaWithoutOverridesLoadedEvent::class, 'onTcaLoad');
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaLoadOverride');
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
    }

    public function onExtLocalConfLoaded()
    {
        $cache = $this->extConfigService->getFs()->getCache();
        if ($cache->has(static::CLASS_NAME_TABLE_MAP_CACHE_KEY)) {
            NamingUtil::$tcaTableClassNameMap = $cache->get(static::CLASS_NAME_TABLE_MAP_CACHE_KEY);
        }
    }

    public function onTcaLoad()
    {
        $this->loadTableConfig(false);
        dbge('TCA LOADED');
    }

    public function onTcaLoadOverride()
    {
        $this->loadTableConfig(true);
        dbge('TCA COMPLETELY LOADED');
    }

    protected function loadTableConfig(bool $overrides): void
    {
        $loader = $this->extConfigService->makeLoader($overrides ? 'TcaTables' : 'TcaTablesOverrides');
        $loader->clearHandlerLocations();
        $loader->setContainer($this->container);
        $loader->setCache(null);

        $loader->registerHandler($this->container->get(ConfigureTcaTableHandler::class)->setLoadOverrides($overrides));

        $state = $loader->load();

        if ($overrides) {
            dbg($state->get('classNameTableMap'));
            $this->extConfigService->getFs()->getCache()->set(
                static::CLASS_NAME_TABLE_MAP_CACHE_KEY, $state->get('classNameTableMap')
            );
        }
        dbge($state->get('tca'));
    }
}
