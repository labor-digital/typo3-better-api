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


namespace LaborDigital\T3ba\Core\BootStage;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3ba\Core\Kernel;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Event\Core\PackageManagerCreatedEvent;
use LaborDigital\T3ba\Event\CreateDiContainerEvent;
use LaborDigital\T3ba\Event\Di\DiContainerBeingBuildEvent;
use LaborDigital\T3ba\Event\Di\DiContainerFilterEvent;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\ExtConfigHandler\EventSubscriber\EventSubscriberBridge;
use LaborDigital\T3ba\Tool\TypoContext\FacetProvider;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\EventBus\EventBusInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DiConfigurationStage implements BootStageInterface
{
    protected const STAGE_CONTAINER_BUILD = 1;
    protected const STAGE_CONTAINER_INSTANTIATE = 2;
    
    protected $stage = 0;
    
    /**
     * @var DelegateContainer
     */
    protected $delegate;
    
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $this->delegate = $kernel->getContainer();
        
        GeneralUtility::setContainer($this->delegate);
        
        $eventBus->addListener(PackageManagerCreatedEvent::class, [$this, 'onPackageManagerCreated']);
        $eventBus->addListener(DiContainerBeingBuildEvent::class, [$this, 'onDiContainerBeingBuild']);
        $eventBus->addListener(CreateDiContainerEvent::class, [$this, 'onDiContainerBeingInstantiated']);
    }
    
    /**
     * Stores the package manager reference and registers the composer autoload
     * capabilities for the Configuration directories of each activated package.
     *
     * @param   \LaborDigital\T3ba\Event\Core\PackageManagerCreatedEvent  $event
     */
    public function onPackageManagerCreated(PackageManagerCreatedEvent $event): void
    {
        $this->delegate->set(PackageManager::class, $event->getPackageManager());
        $this->initializeExtConfigService();
        $this->registerConfigNamespace();
    }
    
    /**
     * Executes the configuration for the di container builder and related stuff, like event registration and so on.
     *
     * @param   \LaborDigital\T3ba\Event\Di\DiContainerBeingBuildEvent  $event
     */
    public function onDiContainerBeingBuild(DiContainerBeingBuildEvent $event): void
    {
        $this->setStage(static::STAGE_CONTAINER_BUILD);
        $this->delegate->set(ContainerConfigurator::class, $event->getContainerConfigurator());
        $this->delegate->set(ContainerBuilder::class, $event->getContainerBuilder());
        
        // We re-inject the delegate, because in tests the container gets flushed away...
        GeneralUtility::setContainer($this->delegate);
        
        $extConfigService = $this->delegate->get(ExtConfigService::class);
        $extConfigService->getDiLoader()->loadForBuildTime();
    }
    
    /**
     * Injects the early dependencies into the real di container instance
     * and runs the "runtime" container configuration handler
     *
     * @param   \LaborDigital\T3ba\Event\CreateDiContainerEvent  $event
     */
    public function onDiContainerBeingInstantiated(CreateDiContainerEvent $event): void
    {
        if ($event->isFailsafe()) {
            $this->delegate->setContainer(DelegateContainer::TYPE_FAILSAFE, $event->getContainer());
            
            return;
        }
        
        // The install-tool extension has a functionality called the "LateBootService"
        // which allows the di container to be recreated for some reason.
        // If that is happening (like in upgrade wizards) some issues arose,
        // like event listeners being re-registered multiple times.
        // To prevent that issue, we check if the current stage is already instantiated
        // and handle the creation differently if we re-instantiate an existing container.
        $reinstantiate = $this->stage === static::STAGE_CONTAINER_INSTANTIATE;
        
        $this->setStage(static::STAGE_CONTAINER_INSTANTIATE);
        
        $symfony = $event->getContainer();
        if (! $symfony instanceof Container) {
            return;
        }
        
        $miniContainer = $this->delegate->getInternal();
        $symfony->set(VarFs::class, $miniContainer->get(VarFs::class));
        
        if (! $reinstantiate) {
            $this->delegate->setContainer(DelegateContainer::TYPE_SYMFONY, $symfony);
        }
        
        /** @var TypoEventBus $eventBus */
        $eventBus = $miniContainer->get(TypoEventBus::class);
        $symfony->set(EventBusInterface::class, $eventBus);
        $symfony->set(TypoEventBus::class, $eventBus);
        
        if ($reinstantiate) {
            /** @var TypoListenerProvider $listenerProvider */
            $listenerProvider = clone $miniContainer->get('@listenerProviderBackup');
            $eventBus->setConcreteListenerProvider($listenerProvider);
        } else {
            $listenerProvider = $eventBus->getConcreteListenerProvider();
        }
        
        $symfony->set(ListenerProviderInterface::class, $listenerProvider);
        $symfony->set(TypoListenerProvider::class, $listenerProvider);
        
        $extConfigService = $miniContainer->get(ExtConfigService::class);
        $extConfigContext = $extConfigService->getContext();
        $symfony->set(ExtConfigContext::class, $extConfigContext);
        $symfony->set(ExtConfigService::class, $extConfigService);
        
        $context = TypoContext::setInstance(new TypoContext(
            $symfony->get(FacetProvider::class)->getAll()
        ));
        $symfony->set(TypoContext::class, $context);
        
        // This is required to link the new event bus into the TYPO3 core and to load the registered event handlers
        // This MUST be executed after the typo context class is set up correctly
        if (! $reinstantiate) {
            $symfony->get(ListenerProvider::class);
            $symfony->get(EventSubscriberBridge::class);
        }
        
        $symfony->set(ConfigState::class, $extConfigContext->getState());
        $extConfigContext->setTypoContext($context);
        $extConfigService->getDiLoader()->loadForRuntime();
        
        $eventBus->dispatch(new DiContainerFilterEvent($symfony));
    }
    
    /**
     * Helper to detect degeneration in the state and automatically re-register the configuration namespace
     *
     * @param   int  $stage
     */
    protected function setStage(int $stage): void
    {
        if ($stage < $this->stage) {
            $this->registerConfigNamespace();
        }
        $this->stage = $stage;
    }
    
    /**
     * Initializes and registers a new and empty instance of the ext config service
     *
     * @return void
     */
    protected function initializeExtConfigService(): void
    {
        $configService = GeneralUtility::makeInstance(
            ExtConfigService::class,
            $this->delegate->get(PackageManager::class),
            $this->delegate->get(TypoEventBus::class),
            $this->delegate->get(VarFs::class),
            $this->delegate
        );
        
        $this->delegate->set(ExtConfigService::class, $configService);
        $this->delegate->set(ExtConfigContext::class, $configService->getContext());
    }
    
    protected function registerConfigNamespace(): void
    {
        $configService = $this->delegate->get(ExtConfigService::class);
        $classLoader = $this->delegate->get(ClassLoader::class);
        $configService->reset();
        foreach ($configService->getAutoloaderMap() as $namespace => $directory) {
            $classLoader->setPsr4($namespace, $directory);
        }
    }
    
}
