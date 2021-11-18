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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Table;


use Closure;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Event\Configuration\ExtBasePersistenceRegistrationEvent;
use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Core\ExtTablesLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\ExtConfigHandler\Table\ContentType\Loader as ContentTypeLoader;
use LaborDigital\T3ba\ExtConfigHandler\Table\Loader as TableLoader;
use LaborDigital\T3ba\ExtConfigHandler\Table\PostProcessor\TcaPostProcessor;
use LaborDigital\T3ba\ExtConfigHandler\Table\Util\PersistenceConfigReloader;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TableApplier extends AbstractExtConfigApplier
{
    use ContainerAwareTrait;
    
    /**
     * @deprecated will be removed in v11 without replacement
     */
    protected const TCA_META_CACHE_KEY = 'extConfig.tca.meta';
    
    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    protected $cache;
    
    /**
     * Cache to store the processed TCA, so we don't have to do it multiple times in an install tool context
     *
     * @var array
     */
    protected $tcaCache = [];
    
    /**
     * True if the ext base persistence mapping was loaded while or before
     * the TCA was completely built. In that case we have to forcefully reload it.
     *
     * @var bool
     */
    protected $reloadExtBaseMapping = false;
    
    /**
     * ConfigureTcaTableApplier constructor.
     *
     * @param   \LaborDigital\T3ba\Core\VarFs\VarFs  $fs
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
        $this->applyMeta();
        $this->provideTableClassNameMap();
    }
    
    /**
     * Applies the tca meta information to the system after the ext_tables.php files have been loaded
     */
    public function onExtTablesLoaded(): void
    {
        // @todo remove this in v11
        $def = $this->state->get('tca.meta.tsConfig', '');
        $def .= PHP_EOL . PHP_EOL . $this->state->get('tca.meta.backend.listPosition', '');
        if (! empty(trim($def))) {
            ExtensionManagementUtility::addPageTSConfig($def);
        }
        
        // Apply tables on standard pages
        $list = array_unique($this->tempMerge('tca.meta.onStandardPages', 'tca.allowOnStandardPages'));
        if (is_array($list)) {
            array_map([ExtensionManagementUtility::class, 'allowTableOnStandardPages'], $list);
        }
        
        // Apply table csh files
        $list = array_unique($this->tempMerge('tca.meta.cshLabels', 'tca.cshLabels'));
        if (is_array($list)) {
            foreach ($list as $args) {
                ExtensionManagementUtility::addLLrefForTCAdescr(...$args);
            }
        }
    }
    
    /**
     * The first pass of table loading is done after TYPO3 loaded all TCA/yourTable.php files
     */
    public function onTcaLoad(): void
    {
        $this->runAndCacheTca(__FUNCTION__, function () {
            $this->getService(ContentTypeLoader::class)->provideDefaultTcaType();
            $this->getService(TableLoader::class)->loadTables();
        });
    }
    
    /**
     * The second pass of table loading is done after TYPO3 loaded all TCA/Overrides/yourTable.php files
     */
    public function onTcaLoadOverride(): void
    {
        $this->runAndCacheTca(__FUNCTION__, function () {
            $this->getService(TableLoader::class)->loadTableOverrides();
            $this->getService(ContentTypeLoader::class)->load();
            
            $meta = $this->makeInstance(TcaPostProcessor::class)->process($this->state);
            $this->getService(ExtConfigService::class)->persistState($this->state);
            
            // @todo remove this in v11
            $this->cache->set(static::TCA_META_CACHE_KEY, $meta);
            $this->applyMeta();
            
            $this->provideTableClassNameMap();
            
            if ($this->reloadExtBaseMapping) {
                $this->reloadExtBaseMapping = false;
                $this->getService(PersistenceConfigReloader::class)->reload();
            }
        });
    }
    
    /**
     * Injects the persistence configuration into the extbase domain mapper
     *
     * @param   \LaborDigital\T3ba\Event\Configuration\ExtBasePersistenceRegistrationEvent  $event
     */
    public function onPersistenceRegistration(ExtBasePersistenceRegistrationEvent $event): void
    {
        $this->reloadExtBaseMapping = true;
        
        $classes = $event->getClasses();
        $list = $this->tempMerge('tca.meta.extbase.persistence', 'typo.extBase.persistence');
        if (is_array($list)) {
            foreach ($list as $class => $def) {
                $classes[$class] = Arrays::merge($classes[$class] ?? [], $def, 'nn');
            }
        }
        $event->setClasses($classes);
    }
    
    /**
     * Runs the given callback and caches the TCA after the execution,
     * so if the method is called multiple times the tca can be reset to the state
     * after the first execution of the callback
     *
     * @param   string    $key
     * @param   \Closure  $callback
     */
    protected function runAndCacheTca(string $key, Closure $callback): void
    {
        if ($this->tcaCache[$key]) {
            $GLOBALS['TCA'] = $this->tcaCache[$key];
            
            return;
        }
        
        $callback();
        
        $this->tcaCache[$key] = $GLOBALS['TCA'];
    }
    
    /**
     * Prepares the NamingUtil class by injecting our stored class map into the class->table map
     */
    protected function provideTableClassNameMap(): void
    {
        $list = $this->tempMerge('tca.meta.classNameMap', 'tca.classNameMap');
        if (is_array($list)) {
            NamingUtil::$tcaTableClassNameMap = array_merge(NamingUtil::$tcaTableClassNameMap, $list);
        }
    }
    
    /**
     * Applies meta information that was generated alongside the TCA to services that can handle them
     *
     * @deprecated will be removed in the next major release
     */
    protected function applyMeta(): void
    {
        $this->state->mergeIntoArray('tca.meta', $this->cache->get(static::TCA_META_CACHE_KEY, []));
    }
    
    /**
     * @param   array|null  $a
     * @param   array|null  $b
     *
     * @return array
     * @deprecated temporary helper until the next major version
     */
    private function tempMerge(string $oldKey, string $newKey): array
    {
        $a = $this->state->get($oldKey, []);
        $b = $this->state->get($newKey, []);
        
        return array_merge(is_array($a) ? $a : [], is_array($b) ? $b : []);
    }
}
