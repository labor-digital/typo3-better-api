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


namespace LaborDigital\T3BA\Core\BootStage;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3BA\Core\Di\DelegateContainer;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\Core\PackageManagerCreatedEvent;
use LaborDigital\T3BA\Event\CreateDiContainerEvent;
use LaborDigital\T3BA\Event\Di\DiContainerBeingBuildEvent;
use LaborDigital\T3BA\Event\Di\DiContainerFilterEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfigHandler\EventSubscriber\EventSubscriberBridge;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
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
        
        $eventBus->addListener(PackageManagerCreatedEvent::class, [$this, 'onPackageManagerCreated']);
        $eventBus->addListener(DiContainerBeingBuildEvent::class, [$this, 'onDiContainerBeingBuild']);
        $eventBus->addListener(CreateDiContainerEvent::class, [$this, 'onDiContainerBeingInstantiated']);
    }
    
    /**
     * Stores the package manager reference and registers the composer autoload
     * capabilities for the Configuration directories of each activated package.
     *
     * @param   \LaborDigital\T3BA\Event\Core\PackageManagerCreatedEvent  $event
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
     * @param   \LaborDigital\T3BA\Event\Di\DiContainerBeingBuildEvent  $event
     */
    public function onDiContainerBeingBuild(DiContainerBeingBuildEvent $event): void
    {
        $this->setStage(static::STAGE_CONTAINER_BUILD);
        $this->delegate->set(ContainerConfigurator::class, $event->getContainerConfigurator());
        $this->delegate->set(ContainerBuilder::class, $event->getContainerBuilder());
        
        $extConfigService = $this->delegate->get(ExtConfigService::class);
        $extConfigService->getDiLoader()->loadForBuildTime();
    }
    
    /**
     * Injects the early dependencies into the real di container instance
     * and runs the "runtime" container configuration handler
     *
     * @param   \LaborDigital\T3BA\Event\CreateDiContainerEvent  $event
     */
    public function onDiContainerBeingInstantiated(CreateDiContainerEvent $event): void
    {
        if ($event->isFailsafe()) {
            $this->delegate->setContainer('failsafe', $event->getContainer());
            
            return;
        }
        
        $this->setStage(static::STAGE_CONTAINER_INSTANTIATE);
        
        $symfony = $event->getContainer();
        if (! $symfony instanceof Container) {
            return;
        }
        
        $miniContainer = $this->delegate->getInternal();
        $this->delegate->setContainer('symfony', $symfony);
        
        $symfony->set(VarFs::class, $miniContainer->get(VarFs::class));
        
        $eventBus = $miniContainer->get(TypoEventBus::class);
        $symfony->set(EventBusInterface::class, $eventBus);
        $symfony->set(TypoEventBus::class, $eventBus);
        
        /** @var TypoListenerProvider $listenerProvider */
        $listenerProvider = clone $miniContainer->get('@listenerProviderBackup');
        $eventBus->setConcreteListenerProvider($listenerProvider);
        $symfony->set(ListenerProviderInterface::class, $listenerProvider);
        $symfony->set(TypoListenerProvider::class, $listenerProvider);
        
        $context = TypoContext::setInstance(new TypoContext());
        $symfony->set(TypoContext::class, $context);
        
        // This is required to link the new event bus into the TYPO3 core and to load the registered event handlers
        // This MUST be executed after the typo context class is set up correctly
        $symfony->get(ListenerProvider::class);
        $symfony->get(EventSubscriberBridge::class);
        
        $extConfigService = $miniContainer->get(ExtConfigService::class);
        $symfony->set(ExtConfigService::class, $extConfigService);
        $symfony->set(ConfigState::class, new ConfigState([]));
        $extConfigService->getContext()->setTypoContext($context);
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

//    /**
//     * Creates the local, mini container instance that is used until the real TYPO3 container
//     * is up and running.
//     *
//     * @param   \TYPO3\CMS\Core\Package\PackageManager  $packageManager
//     */
//    protected function initializeLocalContainer(PackageManager $packageManager): void
//    {
//        $this->services = new MiniContainer([
//            Kernel::class         => $this->kernel,
//            VarFs::class          => $this->kernel->getFs(),
//            PackageManager::class => $packageManager,
//            TypoEventBus::class   => $this->kernel->getEventBus(),
//            CacheInterface::class => $this->kernel->getFs()->getCache(),
//            ClassLoader::class    => $this->kernel->getClassLoader(),
//        ]);
//
//        $configService = GeneralUtility::makeInstance(
//            ExtConfigService::class,
//            $packageManager, $this->kernel->getEventBus(), $this->kernel->getFs(), $this->services
//        );
//
//        $configService = $this->makeInstance(
//            ExtConfigService::class,
//            [$packageManager, $this->kernel->getEventBus(), $this->kernel->getFs(), $this->services]
//        );
//        $container->set(ExtConfigService::class, $configService);
//        $container->set(ExtConfigContext::class, $configService->getContext());
//
//        $this->delegate = new DelegateContainer();
//        $this->delegate->add($container);
//
//        $this->setContainer($this->delegate);
//    }

}
