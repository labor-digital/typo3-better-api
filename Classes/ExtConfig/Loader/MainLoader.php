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


namespace LaborDigital\T3ba\ExtConfig\Loader;

use Closure;
use LaborDigital\T3ba\Core\BootStage\EnsureExtLocalConfOnTcaLoadStage;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\Locking\LockerTrait;
use LaborDigital\T3ba\Event\ExtConfig\MainExtConfigGeneratedEvent;
use LaborDigital\T3ba\Event\ExtConfig\SiteBasedExtConfigGeneratedEvent;
use LaborDigital\T3ba\ExtConfig\Adapter\ConfigStateAdapter;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\StandAloneHandlerInterface;
use LaborDigital\T3ba\ExtConfig\SiteBased\ConfigFinder;
use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Core\Handler;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\Event\BeforeConfigLoadEvent;
use Neunerlei\Configuration\Event\BeforeStateCachingEvent;
use Neunerlei\Configuration\Finder\FilteredHandlerFinder;
use Neunerlei\Configuration\State\ConfigState;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class MainLoader implements LoggerAwareInterface, SingletonInterface
{
    use TypoContextAwareTrait;
    use ContainerAwareTrait;
    use LoggerAwareTrait;
    use LockerTrait;
    
    protected const STATE_CACHE_KEY = 't3ba.config.state';
    protected const STATE_CACHE_KEY_WITH_TCA_EXTENSION = 't3ba.config.state.tca';
    
    /**
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigService
     */
    protected $extConfigService;
    
    /**
     * @var \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * MainConfigLoader constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigService  $extConfigService
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(
        ExtConfigService $extConfigService,
        TypoEventBus $eventBus
    )
    {
        $this->extConfigService = $extConfigService;
        $this->eventBus = $eventBus;
        $this->waitForLockLoops = 200;
    }
    
    public function __destruct()
    {
        $this->releaseLock();
    }
    
    /**
     * Runs the main ext config loader and injects the generated state into the container
     * It will also automatically inject the configuration of typo.globals into the GLOBALS array
     */
    public function load(): void
    {
        $state = $this->makeInstance(ConfigState::class);
        
        if ($this->checkIfCachedStateIsAvailable()) {
            $this->restoreStoredState($state);
        } else {
            $this->acquireLock();
            
            // Recheck if the cached state is available, after we waited for our lock
            if ($this->checkIfCachedStateIsAvailable()) {
                $this->restoreStoredState($state);
            } else {
                $this->generateStoredState($state);
            }
            
            $isLocked = true;
        }
        
        // Ensure the config context is in sync with the global state
        $configContext = $this->extConfigService->getContext();
        $configContext->initialize($configContext->getLoaderContext(), $state);
        
        // Merge the globals into the globals
        $GLOBALS = Arrays::merge($GLOBALS, $state->get('typo.globals', []), 'nn');
        
        // Reset the log manager so our log configurations are applied correctly
        $this->getContainer()->get(LogManager::class)->reset();
        
        if (isset($isLocked)) {
            // IF the script is running normally (not-yet-cached) we automatically load the TCA files here,
            // in order to block the execution while the heavy TCA class lifting is being processed...
            $this->loadBaseTcaIfPossible();
            $this->releaseLock();
        }
    }
    
    /**
     * Internal helper to restore the config state before the TCA extensions were applied.
     * This is done in the TableApplier class.
     *
     * @return void
     * @internal Currently a method in testing, you should not rely on it, yet.
     * @todo     if this logic works in v12 remove the internal annotation
     * @see      \LaborDigital\T3ba\ExtConfigHandler\Table\TableApplier::onTcaLoad()
     */
    public function restoreGlobalStateWithoutTcaExtensions(): void
    {
        ConfigStateAdapter::resetState(
            $this->makeInstance(ConfigState::class),
            $this->getStoredState(false)
        );
    }
    
    /**
     * Internal helper to persist the config state after the TCA extensions were applied.
     * This is done in the TableApplier class.
     *
     * @return void
     * @internal Currently a method in testing, you should not rely on it, yet.
     * @todo     if this logic works in v12 remove the internal annotation
     * @see      \LaborDigital\T3ba\ExtConfigHandler\Table\TableApplier::onTcaLoadOverride()
     */
    public function persistGlobalStateWithTcaExtension(): void
    {
        $this->storeState(static::STATE_CACHE_KEY_WITH_TCA_EXTENSION, $this->makeInstance(ConfigState::class));
    }
    
    /**
     * Returns a NEW config state instance directly hydrated from the cached value.
     * This is NOT the global state, stored in the di container!
     * You can either require the state after the main loader was executed, or with the included TCA extension,
     * after the TCA tables were loaded
     *
     * @param   bool  $withTcaExtension
     *
     * @return \Neunerlei\Configuration\State\ConfigState
     * @internal Currently a method in testing, you should not rely on it, yet.
     * @todo     if this logic works in v12 remove the internal annotation
     */
    protected function getStoredState(bool $withTcaExtension = true): ConfigState
    {
        $cache = $this->extConfigService->getFs()->getCache();
        $cacheKey = $withTcaExtension ? static::STATE_CACHE_KEY_WITH_TCA_EXTENSION : static::STATE_CACHE_KEY;
        
        if (! $cache->has($cacheKey)) {
            $this->logger->emergency('Requesting stored state, but the cache key: "' . $cacheKey . '" does not exist! Falling back to global state!');
            
            return clone $this->makeInstance(ConfigState::class);
        }
        
        return $this->makeInstance(
            ConfigState::class,
            [
                SerializerUtil::unserializeJson(
                    $cache->get($cacheKey)
                ),
            ]
        );
    }
    
    /**
     * Internal helper to persist a state object into the cache
     *
     * @param   string                                      $cacheKey
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     *
     * @return void
     */
    protected function storeState(string $cacheKey, ConfigState $state): void
    {
        $cache = $this->extConfigService->getFs()->getCache();
        $cache->set($cacheKey, SerializerUtil::serializeJson($state->getAll()));
    }
    
    /**
     * Creates an internal event dispatcher proxy we use, to keep track of the loading stages internally
     *
     * @param   \Closure  $eventMapper
     *
     * @return \Psr\EventDispatcher\EventDispatcherInterface
     * @todo in v11 the event dispatcher proxy should be extracted into a real class
     */
    protected function makeEventDispatcherProxy(Closure $eventMapper): EventDispatcherInterface
    {
        return new class($this->eventBus, $eventMapper) implements EventDispatcherInterface {
            protected $eventBus;
            protected $mapper;
            
            public function __construct(TypoEventBus $eventBus, callable $mapper)
            {
                $this->eventBus = $eventBus;
                $this->mapper = $mapper;
            }
            
            public function dispatch(object $event): void
            {
                call_user_func($this->mapper, $event);
                $this->eventBus->dispatch($event);
            }
        };
    }
    
    /**
     * Populates the provided config state object with the actual content
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     *
     * @return void
     */
    protected function generateStoredState(ConfigState $state): void
    {
        // @todo the loader generation should be extracted into a "LoaderFactory" class
        $loader = $this->extConfigService->makeLoader(ExtConfigService::MAIN_LOADER_KEY);
        
        // As we do cache the state content externally, we don't need the loader to do that for us, too.
        $loader->setCache(null);
        
        $loader->setEventDispatcher(
            $this->makeEventDispatcherProxy(
                function (object $event) {
                    if ($event instanceof BeforeStateCachingEvent) {
                        $event->getState()->set(ExtConfigService::PERSISTABLE_STATE_PATH, $event->getCacheKey());
                        
                        $this->loadSiteBasedConfig($event->getState());
                        
                        $configContext = $event->getLoaderContext()->configContext;
                        if ($configContext instanceof ExtConfigContext) {
                            $this->eventBus->dispatch(
                                new MainExtConfigGeneratedEvent($configContext, $event->getState())
                            );
                        }
                    }
                }
            )
        );
        
        // @todo this can be removed in v11, in order to set the EXT_CONFIG_V11_SITE_BASED_CONFIG
        // at the first possible location, I need to inject the handler manually.
        // This allows all other site-based handler to participate on the early access program,
        // without additional configuration
        $loader->registerHandler($this->makeInstance(Handler::class));
        
        $loader->setHandlerFinder(
            $this->makeInstance(
                FilteredHandlerFinder:: class,
                [
                    [StandAloneHandlerInterface::class],
                    [],
                ]
            )
        );
        
        $loader->load(false, $state);
        $this->storeState(static::STATE_CACHE_KEY, $state);
    }
    
    /**
     * Checks if both the state and tca extension state caches exist
     *
     * @return bool
     */
    protected function checkIfCachedStateIsAvailable(): bool
    {
        $cache = $this->extConfigService->getFs()->getCache();
        $hasStateCache = $cache->has(static::STATE_CACHE_KEY);
        $hasStateCacheWithTcaExtension = $cache->has(static::STATE_CACHE_KEY_WITH_TCA_EXTENSION);
        
        return $hasStateCache && $hasStateCacheWithTcaExtension;
    }
    
    /**
     * Restores the global config state back to the object provided
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     *
     * @return void
     */
    protected function restoreStoredState(ConfigState $state): void
    {
        ConfigStateAdapter::resetState($state, $this->getStoredState());
    }
    
    /**
     * Tries to (loaded from a cacheable "loadExtLocalconf" method, inside single (uncached) ext_localconf.php files)
     * load the TCA a bit early. This allows us to keep our "lock" for the period of generating the TCA array alive.
     * If we are not 100% sure to be called in the correct context this method does nothing.
     *
     * @return void
     */
    protected function loadBaseTcaIfPossible(): void
    {
        $inSingleExtLocalConf = false;
        $inLoadExtLocalConf = false;
        
        /** @var FrontendInterface|null $cache */
        $cache = null;
        foreach (debug_backtrace() as $frame) {
            if (str_ends_with($frame['file'], 'ext_localconf.php') && $frame['class'] === ExtensionManagementUtility::class) {
                $inSingleExtLocalConf = true;
                continue;
            }
            
            if ($frame['function'] === 'loadExtLocalconf' && ! empty($frame['args'][0])) {
                if (! isset($frame['args'][1]) || ! $frame['args'][1] instanceof FrontendInterface) {
                    break;
                }
                $cache = $frame['args'][1];
                $inLoadExtLocalConf = true;
                break;
            }
        }
        
        if ($inSingleExtLocalConf && $inLoadExtLocalConf) {
            EnsureExtLocalConfOnTcaLoadStage::$enabled = false;
            Bootstrap::unsetReservedGlobalVariables();
            ExtensionManagementUtility::loadBaseTca(true, $cache);
            EnsureExtLocalConfOnTcaLoadStage::$enabled = true;
        }
    }
    
    /**
     * Injects the empty config shell into the container, so handler can use them as injected property
     *
     * @param   \Neunerlei\Configuration\Event\BeforeConfigLoadEvent  $event
     */
    protected function injectEmptyConfigShell(BeforeConfigLoadEvent $event): void
    {
        $this->getContainer()->set(ConfigState::class, $event->getLoaderContext()->configContext->getState());
    }
    
    /**
     * Executed when the main loader is done gathering the main configuration state.
     * It will create a new child loader that will execute the handlers inside the Configuration/SiteConfig directory.
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     *
     * @deprecated will be removed in v11
     */
    protected function loadSiteBasedConfig(ConfigState $state): void
    {
        if ($this->getTypoContext()->config()->isFeatureEnabled(
            T3baFeatureToggles::EXT_CONFIG_V11_SITE_BASED_CONFIG
        )) {
            return;
        }
        
        $loader = $this->extConfigService->makeLoader(ExtConfigService::SITE_BASED_LOADER_KEY);
        $container = $this->getContainer();
        
        $typoContext = $this->getTypoContext();
        $configContext = $this->makeInstance(SiteConfigContext::class, [
            $this->extConfigService,
            $typoContext,
        ]);
        $configContext->initializeSite('null', new NullSite());
        
        $container->set(SiteConfigContext::class, $configContext);
        
        $loader->setConfigContextClass(SiteConfigContext::class);
        $loader->setCache(null);
        $loader->setContainer($container);
        
        $loader->setEventDispatcher(
            $this->makeEventDispatcherProxy(static function (object $event) use ($state) {
                if ($event instanceof BeforeConfigLoadEvent) {
                    $ctx = $event->getLoaderContext();
                    $ctx->configContext->initialize($ctx, $state);
                }
            })
        );
        
        $loader->setHandlerFinder(
            $this->makeInstance(
                FilteredHandlerFinder::class,
                [
                    [StandAloneHandlerInterface::class],
                    [SiteBasedHandlerInterface::class],
                ]
            )
        );
        
        $loader->setConfigFinder(
            $this->makeInstance(
                ConfigFinder::class,
                [
                    $typoContext->site()->getAll(false),
                ]
            )
        );
        
        $loader->load();
        
        $this->eventBus->dispatch(
            new SiteBasedExtConfigGeneratedEvent($configContext, $state)
        );
    }
    
}
