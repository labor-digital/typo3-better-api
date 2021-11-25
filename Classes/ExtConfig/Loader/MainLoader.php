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
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\ExtConfig\MainExtConfigGeneratedEvent;
use LaborDigital\T3ba\Event\ExtConfig\SiteBasedExtConfigGeneratedEvent;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\StandAloneHandlerInterface;
use LaborDigital\T3ba\ExtConfig\SiteBased\ConfigFinder;
use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Core\Handler;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\Event\BeforeConfigLoadEvent;
use Neunerlei\Configuration\Event\BeforeStateCachingEvent;
use Neunerlei\Configuration\Finder\FilteredHandlerFinder;
use Neunerlei\Configuration\State\ConfigState;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Site\Entity\NullSite;

class MainLoader
{
    use TypoContextAwareTrait;
    use ContainerAwareTrait;
    
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
    }
    
    /**
     * Runs the main ext config loader and injects the generated state into the container
     * It will also automatically inject the configuration of typo.globals into the GLOBALS array
     */
    public function load(): void
    {
        $state = $this->getService(ConfigState::class);
        
        $loader = $this->extConfigService->makeLoader(ExtConfigService::MAIN_LOADER_KEY);
        
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
        
        // Ensure the config context is in sync with the global state
        $configContext = $this->extConfigService->getContext();
        $configContext->initialize($configContext->getLoaderContext(), $state);
        
        // Merge the globals into the globals and then remove them from the state (save a bit of memory)
        $GLOBALS = Arrays::merge($GLOBALS, $state->get('typo.globals', []), 'nn');
        
        // Reset the log manager so our log configurations are applied correctly
        $this->getContainer()->get(LogManager::class)->reset();
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
