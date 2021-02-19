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
use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
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
use LaborDigital\T3BA\ExtConfigHandler\EventSubscriber\EventSubscriberBridge;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DiConfigurationStage implements BootStageInterface
{
    use ContainerAwareTrait;

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
        $this->getContainer()->set(ContainerBuilder::class, $event->getContainerBuilder());
        $this->getService(ExtConfigService::class)->getDiLoader()->loadForBuildTime();
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

        $realContainer = $event->getContainer();
        if ($realContainer instanceof FailsafeDelegateContainer) {
            $realContainer = $realContainer->getContainer();
        }

        if (! $realContainer instanceof Container) {
            return;
        }

        // Provide the container to the general utility a bit earlier that it normally would
        $this->symfonyContainer = $realContainer;
        GeneralUtility::setContainer($realContainer);

        $eventBus = $this->getService(TypoEventBus::class);
        $eventBus->setContainer($realContainer);

        $realContainer->set(VarFs::class, $this->getService(VarFs::class));
        $realContainer->set(EventBusInterface::class, $eventBus);
        $realContainer->set(TypoEventBus::class, $eventBus);

        /** @var TypoListenerProvider $listenerProvider */
        $listenerProvider = $eventBus->getConcreteListenerProvider();
        $listenerProvider->setContainer($realContainer);
        $realContainer->set(ListenerProviderInterface::class, $listenerProvider);
        $realContainer->set(TypoListenerProvider::class, $listenerProvider);

        $context = TypoContext::setInstance(new TypoContext());
        $context->setContainer($realContainer);
        $realContainer->set(TypoContext::class, $context);

        // This is required to link the new event bus into the TYPO3 core and to load the registered event handlers
        // This MUST be executed after the typo context class is set up correctly
        $realContainer->get(ListenerProvider::class);
        $realContainer->get(EventSubscriberBridge::class);

        $extConfigService = $this->getService(ExtConfigService::class);
        $realContainer->set(ExtConfigService::class, $extConfigService);
        $extConfigService->getContext()->setTypoContext($context);
        $extConfigService->setContainer($realContainer);
        $extConfigService->getDiLoader()->loadForRuntime();

        $eventBus->dispatch(new DiContainerFilterEvent($realContainer));

        $this->caServices = [];
    }

    /**
     * Creates the local, mini container instance that is used until the real TYPO3 container
     * is up and running.
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager  $packageManager
     */
    protected function initializeLocalContainer(PackageManager $packageManager): void
    {
        $container = new MiniContainer([
            Kernel::class         => $this->kernel,
            VarFs::class          => $this->kernel->getFs(),
            PackageManager::class => $packageManager,
            TypoEventBus::class   => $this->kernel->getEventBus(),
            CacheInterface::class => $this->kernel->getFs()->getCache(),
            ClassLoader::class    => $this->kernel->getClassLoader(),
        ]);

        $configService = $this->makeInstance(
            ExtConfigService::class,
            [$packageManager, $this->kernel->getEventBus(), $this->kernel->getFs(), $container]
        );
        $container->set(ExtConfigService::class, $configService);
        $container->set(ExtConfigContext::class, $configService->getContext());

        $this->setContainer($container);
    }

    /**
     *  Register config auto-loaders for all packages
     */
    protected function registerConfigAutoloader(): void
    {
        $classLoader = $this->kernel->getClassLoader();
        foreach ($this->getService(ExtConfigService::class)->getAutoloaderMap() as $namespace => $directory) {
            $classLoader->setPsr4($namespace, $directory);
        }
    }
}
