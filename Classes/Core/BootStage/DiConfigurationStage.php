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
 * Last modified: 2020.08.24 at 15:54
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3BA\Core\Di\FailsafeDelegateContainer;
use LaborDigital\T3BA\Core\Di\MiniContainer;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\Core\PackageManagerCreatedEvent;
use LaborDigital\T3BA\Event\Di\DiContainerBeingBuildEvent;
use LaborDigital\T3BA\Event\Di\DiContainerFilterEvent;
use LaborDigital\T3BA\Event\InternalCreateDependencyInjectionContainerEvent;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfigHandler\Di\Handler;
use LaborDigital\T3BA\ExtConfigHandler\EventSubscriber\Handler as EventSubHandler;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\DependencyInjection\Container;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DiConfigurationStage implements BootStageInterface
{
    /**
     * @var MiniContainer
     */
    protected $container;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var Container
     */
    protected $symfonyContainer;

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $this->kernel = $kernel;

        // Register local event listeners
        $eventBus->addListener(PackageManagerCreatedEvent::class, [$this, 'onPackageManagerCreated']);
        $eventBus->addListener(DiContainerBeingBuildEvent::class, [$this, 'onDiContainerBeingBuild']);
        $eventBus->addListener(InternalCreateDependencyInjectionContainerEvent::class,
            [$this, 'onDiContainerBeingInstantiated']);
    }

    /**
     * Stores the package manager reference and registers the composer autoload
     * capabilities for the Configuration directories of each activated package.
     *
     * @param   \LaborDigital\T3BA\Event\Core\PackageManagerCreatedEvent  $event
     */
    public function onPackageManagerCreated(PackageManagerCreatedEvent $event): void
    {
        $this->initializeLocalContainer($event->getPackageManager());
        $this->registerConfigAutoloader();
    }

    /**
     * Executes the configuration for the di container builder and related stuff, like event registration and so on.
     *
     * @param   \LaborDigital\T3BA\Event\Di\DiContainerBeingBuildEvent  $event
     */
    public function onDiContainerBeingBuild(DiContainerBeingBuildEvent $event): void
    {
        // Execute the di container builder configuration
        $this->runDiConfigLoader(static function (Handler $handler) use ($event) {
            $handler->configureForContainerBuilder($event->getContainerBuilder());
        });
    }

    /**
     * Injects the early dependencies into the real di container instance
     * and runs the "runtime" container configuration handler
     *
     * @param   \LaborDigital\T3BA\Event\InternalCreateDependencyInjectionContainerEvent  $event
     */
    public function onDiContainerBeingInstantiated(InternalCreateDependencyInjectionContainerEvent $event): void
    {
        // Skip if the failsafe container is required and we have already a prepared one in store
        if (isset($this->symfonyContainer) && $event->isFailsafe()) {
            $event->setNormalContainer($this->symfonyContainer);

            return;
        }

        // Try to extract the inner container
        $realContainer = $event->getContainer();
        if ($realContainer instanceof FailsafeDelegateContainer) {
            $realContainer = $realContainer->getContainer();
        }
        if (! $realContainer instanceof Container) {
            return;
        }

        // Inject the container into the event bus
        /** @var TypoEventBus $eventBus */
        $eventBus = $this->container->get(TypoEventBus::class);
        $eventBus->setContainer($realContainer);
        /** @var TypoListenerProvider $listenerProvider */
        $listenerProvider = $eventBus->getConcreteListenerProvider();
        $listenerProvider->setContainer($realContainer);

        // Inject the listener provider into the container
        $realContainer->set(ListenerProviderInterface::class, $listenerProvider);
        $realContainer->set(TypoListenerProvider::class, $listenerProvider);
        $realContainer->get(ListenerProvider::class);

        // Prepare the Typo context instance
        $context = TypoContext::setInstance(new TypoContext());
        $context->setContainer($realContainer);
        $realContainer->set(TypoContext::class, $context);

        // Inject our early services into the container
        $realContainer->set(VarFs::class, $this->container->get(VarFs::class));
        $realContainer->set(EventBusInterface::class, $eventBus);
        $realContainer->set(TypoEventBus::class, $eventBus);
        $realContainer->set(ExtConfigContext::class,
            $this->container->get(ExtConfigContext::class)->setTypoContext($context));
        $realContainer->set(ExtConfigService::class, $this->container->get(ExtConfigService::class));

        // Provide the container to the general utility a bit early
        $this->symfonyContainer = $realContainer;
        GeneralUtility::setContainer($realContainer);

        // Run the "runtime" container configuration
        $this->runDiConfigLoader(static function (Handler $handler) use ($realContainer) {
            $handler->configureRuntimeContainer($realContainer);
        });

        // Allow global filtering
        $eventBus->dispatch(new DiContainerFilterEvent($realContainer));
    }

    /**
     * Creates a configuration loader instance for the dependency injection
     *
     * @param   callable  $handlerConfigurator  A configurator to prepare the handler instance for the current usecase
     */
    protected function runDiConfigLoader(callable $handlerConfigurator): void
    {
        /** @var ExtConfigService $extConfigService */
        $extConfigService = $this->container->get(ExtConfigService::class);
        $loader           = $extConfigService->makeLoader(ExtConfigService::DI_LOADER_KEY);
        $loader->setContainer($this->container);
        $loader->clearHandlerLocations();
        $handler = new Handler();
        $handlerConfigurator($handler);
        $loader->registerHandler($handler);
        $loader->load(true);
    }

    /**
     * Creates the local, mini container instance that is used until the real TYPO3 container
     * is up and running.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager  $packageManager
     */
    protected function initializeLocalContainer(PackageManager $packageManager): void
    {
        $fs = $this->kernel->getFs();

        $container = new MiniContainer();
        $container->set(VarFs::class, $fs);
        $container->set(Kernel::class, $this->kernel);
        $container->set(PackageManager::class, $packageManager);
        $container->set(TypoEventBus::class, $this->kernel->getEventBus());
        $container->set(CacheInterface::class, $fs->getCache());
        $container->set(ClassLoader::class, $this->kernel->getClassLoader());
        $container->set(EventSubHandler::class, GeneralUtility::makeInstance(
            EventSubHandler::class,
            $this->kernel->getEventBus()
        ));
        $container->set(ExtConfigService::class, GeneralUtility::makeInstance(
            ExtConfigService::class,
            $packageManager,
            $this->kernel->getEventBus(),
            $fs
        ));
        $container->set(ExtConfigContext::class, GeneralUtility::makeInstance(
            ExtConfigContext::class,
            $container->get(ExtConfigService::class)
        ));

        $this->container = $container;
    }

    /**
     *  Register config auto-loaders for all packages
     */
    protected function registerConfigAutoloader(): void
    {
        /** @var ExtConfigService $extConfigService */
        $extConfigService = $this->container->get(ExtConfigService::class);
        $classLoader      = $this->kernel->getClassLoader();
        foreach ($extConfigService->getAutoloaderMap() as $namespace => $directory) {
            $classLoader->setPsr4($namespace, $directory);
        }
    }
}
