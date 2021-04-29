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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Loader;

use Closure;
use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\ExtConfig\Adapter\CachelessSiteConfigurationAdapter;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3BA\ExtConfig\Interfaces\StandAloneHandlerInterface;
use LaborDigital\T3BA\ExtConfig\SiteBased\ConfigFinder;
use LaborDigital\T3BA\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\Event\BeforeConfigLoadEvent;
use Neunerlei\Configuration\Event\BeforeStateCachingEvent;
use Neunerlei\Configuration\Finder\FilteredHandlerFinder;
use Neunerlei\Configuration\State\ConfigState;
use Psr\EventDispatcher\EventDispatcherInterface;

class MainLoader
{
    use TypoContextAwareTrait;
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigService
     */
    protected $extConfigService;
    
    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * MainConfigLoader constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigService  $extConfigService
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
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
        $loader = $this->extConfigService->makeLoader(ExtConfigService::MAIN_LOADER_KEY);
        
        $loader->setEventDispatcher(
            $this->makeEventDispatcherProxy(
                function (object $event) {
                    if ($event instanceof BeforeConfigLoadEvent) {
                        $this->injectEmptyConfigShell($event);
                    } elseif ($event instanceof BeforeStateCachingEvent) {
                        $this->loadSiteBasedConfig($event->getState());
                    }
                }
            )
        );
        
        $loader->setHandlerFinder(
            $this->makeInstance(
                FilteredHandlerFinder:: class,
                [
                    [StandAloneHandlerInterface::class, SiteBasedHandlerInterface::class],
                    [],
                ]
            )
        );
        
        $loader->setContainer($this->getContainer());
        $state = $loader->load();
        
        $this->getContainer()->set(ConfigState::class, $state);
        
        // Merge the globals into the globals and then remove them from the state (save a bit of memory)
        $GLOBALS = Arrays::merge($GLOBALS, $state->get('typo.globals', []), 'nn');
        $state->set('typo.globals', null);
    }
    
    /**
     * Creates an internal event dispatcher proxy we use to keep track of the loading stages internally
     *
     * @param   \Closure  $eventMapper
     *
     * @return \Psr\EventDispatcher\EventDispatcherInterface
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
            
            public function dispatch(object $event)
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
     */
    protected function loadSiteBasedConfig(ConfigState $state): void
    {
        $loader = $this->extConfigService->makeLoader(ExtConfigService::SITE_BASED_LOADER_KEY);
        $loader->setConfigContextClass(SiteConfigContext::class);
        $loader->setCache(null);
        $loader->setContainer($this->getContainer());
        
        $loader->setEventDispatcher(
            $this->makeEventDispatcherProxy(static function (object $event) use ($state) {
                if ($event instanceof BeforeConfigLoadEvent) {
                    $event->getLoaderContext()->configContext
                        ->initialize($event->getLoaderContext(), $state);
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
                    CachelessSiteConfigurationAdapter::makeInstance()->getAllExistingSites(false),
                ]
            )
        );
        
        $loader->load();
    }
    
}
