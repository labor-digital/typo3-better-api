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


use LaborDigital\T3BA\Event\Configuration\ExtBasePersistenceRegistrationEvent;
use LaborDigital\T3BA\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\Event\Core\ExtTablesLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3BA\ExtConfig\AbstractExtConfigApplier;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3BA\Tool\Sql\SqlRegistry;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Applier extends AbstractExtConfigApplier
{
    protected const TCA_META_CACHE_KEY = 'extConfig.tca.meta';

    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigService
     */
    protected $extConfigService;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * The loaded meta information
     *
     * @var array
     */
    protected $meta = [];

    /**
     * ConfigureTcaTableApplier constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigService  $extConfigService
     * @param   \Psr\Container\ContainerInterface              $container
     */
    public function __construct(ExtConfigService $extConfigService, ContainerInterface $container)
    {
        $this->extConfigService = $extConfigService;
        $this->cache            = $extConfigService->getFs()->getCache();
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
        $subscription->subscribe(ExtTablesLoadedEvent::class, 'onExtTablesLoaded');
        $subscription->subscribe(ExtBasePersistenceRegistrationEvent::class, 'onPersistenceRegistration');
    }

    /**
     * Applies default configuration if the ext local conf files have been loaded
     */
    public function onExtLocalConfLoaded(): void
    {
        $this->applyDefaults();
    }

    /**
     * Applies the tca meta information to the system after the ext_tables.php files have been loaded
     */
    public function onExtTablesLoaded(): void
    {
        $this->applyListPositions();
        $this->applyTableOnStandardPages();
    }

    /**
     * Injects the persistence configuration into the extbase domain mapper
     *
     * @param   \LaborDigital\T3BA\Event\Configuration\ExtBasePersistenceRegistrationEvent  $event
     */
    public function onPersistenceRegistration(ExtBasePersistenceRegistrationEvent $event): void
    {
        $classes = $event->getClasses();
        $list    = $this->state->get('tca.meta.extbase.persistence');
        if (is_array($list)) {
            foreach ($list as $class => $def) {
                $current = $classes[$class] ?? [];
                ArrayUtility::mergeRecursiveWithOverrule($current, $def);
                $classes[$class] = $current;
            }
        }
        $event->setClasses($classes);
    }

    /**
     * The first pass of table loading is done after TYPO3 loaded all TCA/yourTable.php files
     */
    public function onTcaLoad(): void
    {
        $this->container->get(SqlRegistry::class)->clear();
        $this->loadTableConfig(false);
    }

    /**
     * The second pass of table loading is done after TYPO3 loaded all TCA/Overrides/yourTable.php files
     */
    public function onTcaLoadOverride(): void
    {
        $this->loadTableConfig(true);

        // Extract additional tca rules and collect them in a "meta" array we use in this applier
        $processor = $this->container->get(TcaPostProcessor::class);
        $this->cache->set(static::TCA_META_CACHE_KEY, $this->meta = $processor->process());
        $this->applyDefaults();
    }

    /**
     * Internal helper to load the tca definitions from the ext config classes
     *
     * @param   bool  $overrides  True to load the tca table overrides instead of the normal table definitions
     */
    protected function loadTableConfig(bool $overrides): void
    {
        $loader = $this->extConfigService->makeLoader(
            $overrides ? ExtConfigService::TCA_OVERRIDE_LOADER_KEY
                : ExtConfigService::TCA_LOADER_KEY);

        $loader->clearHandlerLocations();
        $loader->setContainer($this->container);
        $loader->setCache(null);

        $loader->registerHandler($this->container
            ->get(Handler::class)
            ->setLoadOverrides($overrides));

        $loader->load();
    }

    /**
     * Applies the
     */
    protected function applyDefaults(): void
    {
        $this->applyMetaToState();
        $this->applyNamingUtilClassMap();
    }

    /**
     * Injects the tca.meta node into the global configuration object
     */
    protected function applyMetaToState(): void
    {
        $meta       = $this->cache->get(static::TCA_META_CACHE_KEY, []);
        $publicMeta = $this->state->get('tca.meta', []);
        ArrayUtility::mergeRecursiveWithOverrule($publicMeta, $meta);
        $this->state->set('tca.meta', $publicMeta);
    }

    /**
     * Prepares the NamingUtil class by injecting our stored class map into the the class->table map
     */
    protected function applyNamingUtilClassMap(): void
    {
        $list = $this->state->get('tca.meta.classNameMap');
        if (is_array($list)) {
            NamingUtil::$tcaTableClassNameMap = array_merge(NamingUtil::$tcaTableClassNameMap, $list);
        }
    }

    /**
     * Injects the ts config for the table list positioning into the backend
     */
    protected function applyListPositions(): void
    {
        // Apply list positions
        $def = $this->state->get('tca.meta.backend.listPosition');
        if (is_string($def)) {
            ExtensionManagementUtility::addPageTSConfig($def);
        }
    }

    /**
     * Applies the on standard page configuration into the extension management utility
     */
    protected function applyTableOnStandardPages(): void
    {
        // Apply tables on standard pages
        $list = $this->state->get('tca.meta.onStandardPages');
        if (is_array($list)) {
            array_map([ExtensionManagementUtility::class, 'allowTableOnStandardPages'], $list);
        }
    }
}
