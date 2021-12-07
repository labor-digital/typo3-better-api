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


namespace LaborDigital\T3ba\Core;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\BootStage\BootStageInterface;
use LaborDigital\T3ba\Core\BootStage\ClassOverrideStage;
use LaborDigital\T3ba\Core\BootStage\DbgConfigurationStage;
use LaborDigital\T3ba\Core\BootStage\DiConfigurationStage;
use LaborDigital\T3ba\Core\BootStage\EnsureExtLocalConfOnTcaLoadStage;
use LaborDigital\T3ba\Core\BootStage\ErrorHandlerAdapterRegistrationStage;
use LaborDigital\T3ba\Core\BootStage\ErrorHandlerDevStage;
use LaborDigital\T3ba\Core\BootStage\FailsafeWrapperPreparationStage;
use LaborDigital\T3ba\Core\BootStage\HookPackageRegistrationStage;
use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\Di\MiniContainer;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3ba\Core\Exception\KernelNotInitializedException;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Event\KernelBootEvent;
use Neunerlei\EventBus\Dispatcher\EventListenerListItem;
use Neunerlei\EventBus\EventBusInterface;
use Psr\SimpleCache\CacheInterface;

class Kernel
{
    /**
     * The instance after the kernel was created
     *
     * @var self
     */
    protected static $instance;
    
    /**
     * A list of low level event listeners to register on the event dispatcher
     *
     * @var array
     */
    protected static $lowLevelListeners = [];
    
    /**
     * The boot stage instances to be run when the package hooks itself into the TYPO3 core
     *
     * @var BootStageInterface[]
     */
    protected static $bootStages = [];
    
    /**
     * A list of callables that get executed when the kernel gets initialized, but BEFORE any action is taken.
     *
     * @var callable[]
     */
    protected static $onInitHooks = [];
    
    /**
     * The event bus instance
     *
     * @var TypoEventBus
     */
    protected $eventBus;
    
    /**
     * The file system we write our internal files with
     *
     * @var VarFs
     */
    protected $fs;
    
    /**
     * The internal, delegate container that holds the early services
     *
     * @var DelegateContainer
     */
    protected $container;
    
    /**
     * Initializes the better api kernel and prepares the boot stages
     * to hook the extension into the TYPO3 core
     *
     * @param   \Composer\Autoload\ClassLoader  $composerClassLoader
     */
    public static function init(ClassLoader $composerClassLoader): void
    {
        // Don't reinitialize
        if (static::$instance instanceof static) {
            return;
        }
        
        // Create a new instance
        static::$instance = $i = new static();
        
        foreach (static::$onInitHooks as $hook) {
            $hook();
        }
        
        // @todo we should detect phpUnit tests and store the value here
        // str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'phpunit')
        
        // Build our internal container
        // @todo move the container creation to its own method
        $container = new DelegateContainer();
        $i->container = $container;
        DelegateContainer::setInstance($container);
        $container->setContainer(DelegateContainer::TYPE_INTERNAL, new MiniContainer());
        $container->set(VarFs::class, $i->fs = new VarFs());
        $container->set(CacheInterface::class, $i->fs->getCache());
        $container->set(static::class, $i);
        $container->set(ClassLoader::class, $composerClassLoader);
        $container->set(TypoEventBus::class, $i->eventBus = $i->makeEventBus());
        $container->set(EventBusInterface::class, $i->eventBus);
        
        // Create the default boot stages
        $defaultBootStages = [
            new DbgConfigurationStage(),
            new ClassOverrideStage(),
            new EnsureExtLocalConfOnTcaLoadStage(),
            new HookPackageRegistrationStage(),
            new FailsafeWrapperPreparationStage(),
            new DiConfigurationStage(),
            new ErrorHandlerAdapterRegistrationStage(),
            new ErrorHandlerDevStage(),
        ];
        
        // Prepare the boot stages
        foreach (array_merge($defaultBootStages, static::$bootStages) as $bootStage) {
            /** @var BootStageInterface $bootStage */
            $bootStage->prepare($i->eventBus, $i);
        }
        
        $container->set('@listenerProviderBackup', clone $i->eventBus->getConcreteListenerProvider());
        
        // Dispatch the boot event
        $i->eventBus->dispatch(new KernelBootEvent($i));
    }
    
    /**
     * Registers a callable to be executed when the kernel gets initialized, but BEFORE any action is taken.
     *
     * @param   callable  $callable
     */
    public static function addOnInitHook(callable $callable): void
    {
        static::$onInitHooks[] = $callable;
    }
    
    /**
     * Registers a new boot stage instance to be executed, while the better api package
     * hooks itself into the TYPO3 core.
     *
     * @param   \LaborDigital\T3ba\Core\BootStage\BootStageInterface  $stage
     */
    public static function addBootStage(BootStageInterface $stage): void
    {
        static::$bootStages[get_class($stage)] = $stage;
    }
    
    /**
     * Adds a new, really early event listener that applies even inside the bootstrap of the kernel.
     * This would be a real edge-case and you would have to do some black composer magic (autoload file)
     * in order to register your event listener, but it's possible nevertheless.
     *
     * @param   string    $event
     * @param   callable  $listener
     * @param   array     $options
     *
     * @see TypoEventBus::addListener() See the eventbus implementation to learn more about the parameters
     */
    public static function addLowLevelEventListener(string $event, callable $listener, array $options = []): void
    {
        static::$lowLevelListeners[] = func_get_args();
    }
    
    /**
     * Returns the singleton instance of the kernel, after it was initialized in using the init() method
     *
     * @return static
     * @throws \LaborDigital\T3ba\Core\Exception\KernelNotInitializedException
     */
    public static function getInstance(): self
    {
        if (! static::$instance instanceof static) {
            throw new KernelNotInitializedException('The T3BA kernel was not correctly initialized!');
        }
        
        return static::$instance;
    }
    
    /**
     * Returns the composer class loader instance
     *
     * @return \Composer\Autoload\ClassLoader
     */
    public function getClassLoader(): ClassLoader
    {
        return $this->container->get(ClassLoader::class);
    }
    
    /**
     * Returns the event bus instance
     *
     * @return \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    public function getEventBus(): TypoEventBus
    {
        return $this->eventBus;
    }
    
    /**
     * Returns the file system instance
     *
     * @return \LaborDigital\T3ba\Core\VarFs\VarFs
     */
    public function getFs(): VarFs
    {
        return $this->fs;
    }
    
    /**
     * Returns the delegate container that is shared in this application
     *
     * @return \LaborDigital\T3ba\Core\Di\DelegateContainer
     */
    public function getContainer(): DelegateContainer
    {
        return $this->container;
    }
    
    /**
     * Creates the typo event bus instance
     *
     * @return \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected function makeEventBus(): TypoEventBus
    {
        if ($this->eventBus instanceof TypoEventBus) {
            return $this->eventBus;
        }
        
        // Create the eventbus instance
        $eventBus = TypoEventBus::setInstance(new TypoEventBus());
        $listenerProvider = new TypoListenerProvider();
        $listenerProvider->setContainer($this->container);
        $eventBus->setContainer($this->container);
        $eventBus->setConcreteListenerProvider($listenerProvider);
        $eventBus->setProviderAdapter(TypoListenerProvider::class, static function (
            TypoListenerProvider $provider,
            string $event,
            EventListenerListItem $item,
            array $options
        ) {
            $provider->addCallableListener($event, $item->listener, $options);
        }, true);
        
        // Register low level events
        foreach (static::$lowLevelListeners as $listener) {
            $eventBus->addListener(...$listener);
        }
        
        return $eventBus;
    }
}
