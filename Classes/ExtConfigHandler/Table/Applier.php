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


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\Configuration\ExtBasePersistenceRegistrationEvent;
use LaborDigital\T3BA\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\Event\Core\ExtTablesLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\Event\Core\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Applier extends AbstractExtConfigApplier
{
    use ContainerAwareTrait;

    protected const TCA_META_CACHE_KEY = 'extConfig.tca.meta';

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;

    /**
     * ConfigureTcaTableApplier constructor.
     *
     * @param   \LaborDigital\T3BA\Core\VarFs\VarFs  $fs
     */
    public function __construct(VarFs $fs)
    {
        $this->cache = $fs->getCache();
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
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
        // Apply list positions
        $def = $this->state->get('tca.meta.backend.listPosition');
        if (is_string($def)) {
            ExtensionManagementUtility::addPageTSConfig($def);
        }

        // Apply tables on standard pages
        $list = $this->state->get('tca.meta.onStandardPages');
        if (is_array($list)) {
            array_map([ExtensionManagementUtility::class, 'allowTableOnStandardPages'], $list);
        }
    }

    /**
     * The first pass of table loading is done after TYPO3 loaded all TCA/yourTable.php files
     */
    public function onTcaLoad(): void
    {
        $this->getService(Loader::class)->loadTables();
    }

    /**
     * The second pass of table loading is done after TYPO3 loaded all TCA/Overrides/yourTable.php files
     */
    public function onTcaLoadOverride(): void
    {
        $loader = $this->getService(Loader::class);
        $loader->loadTableOverrides();
        $this->cache->set(static::TCA_META_CACHE_KEY, $loader->loadTableMeta());
        $this->applyDefaults();
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
                $classes[$class] = Arrays::merge($classes[$class] ?? [], $def, 'nn');
            }
        }
        $event->setClasses($classes);
    }

    /**
     * Applies the
     */
    protected function applyDefaults(): void
    {
        // Injects the tca.meta node into the global configuration object
        $this->state->mergeIntoArray('tca.meta', $this->cache->get(static::TCA_META_CACHE_KEY, []));

        // Prepares the NamingUtil class by injecting our stored class map into the the class->table map
        $list = $this->state->get('tca.meta.classNameMap');
        if (is_array($list)) {
            NamingUtil::$tcaTableClassNameMap = array_merge(NamingUtil::$tcaTableClassNameMap, $list);
        }
    }
}
