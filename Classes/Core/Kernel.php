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
 * Last modified: 2020.08.24 at 16:06
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3BA\Core\BootStage\BootStageInterface;
use LaborDigital\T3BA\Core\BootStage\ClassOverrideStage;
use LaborDigital\T3BA\Core\BootStage\DbgConfigurationStage;
use LaborDigital\T3BA\Core\BootStage\DiConfigurationStage;
use LaborDigital\T3BA\Core\BootStage\EnsureExtLocalConfOnTcaLoadStage;
use LaborDigital\T3BA\Core\BootStage\ErrorHandlerAdapterRegistrationStage;
use LaborDigital\T3BA\Core\BootStage\ErrorHandlerDevStage;
use LaborDigital\T3BA\Core\BootStage\FailsafeWrapperPreparationStage;
use LaborDigital\T3BA\Core\BootStage\HookPackageRegistrationStage;
use LaborDigital\T3BA\Core\Di\DelegateContainer;
use LaborDigital\T3BA\Core\Di\MiniContainer;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\Core\Exception\KernelNotInitializedException;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\Event\KernelBootEvent;
use Neunerlei\EventBus\Dispatcher\EventListenerListItem;
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

        // Build our internal container
        $container = $i->container = DelegateContainer::setInstance(new DelegateContainer());
        $container->setContainer('internal', new MiniContainer());
        $container->set(VarFs::class, $i->fs = new VarFs());
        $container->set(CacheInterface::class, $i->fs->getCache());
        $container->set(static::class, $i);
        $container->set(ClassLoader::class, $composerClassLoader);
        $container->set(TypoEventBus::class, $i->eventBus = $i->makeEventBus());

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
     * Registers a new boot stage instance to be executed, while the better api package
     * hooks itself into the TYPO3 core.
     *
     * @param   \LaborDigital\T3BA\Core\BootStage\BootStageInterface  $stage
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
     * @throws \LaborDigital\T3BA\Core\Exception\KernelNotInitializedException
     */
    public static function getInstance(): self
    {
        if (! static::$instance instanceof static) {
            throw new KernelNotInitializedException('The better api kernel was not correctly initialized!');
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
     * @return \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    public function getEventBus(): TypoEventBus
    {
        return $this->eventBus;
    }

    /**
     * Returns the file system instance
     *
     * @return \LaborDigital\T3BA\Core\VarFs\VarFs
     */
    public function getFs(): VarFs
    {
        return $this->fs;
    }

    /**
     * Returns the delegate container that is shared in this application
     *
     * @return \LaborDigital\T3BA\Core\Di\DelegateContainer
     */
    public function getContainer(): DelegateContainer
    {
        return $this->container;
    }

    /**
     * Creates the typo event bus instance
     *
     * @return \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected function makeEventBus(): TypoEventBus
    {
        if ($this->eventBus instanceof TypoEventBus) {
            return $this->eventBus;
        }

        // Create the eventbus instance
        $eventBus         = TypoEventBus::setInstance(new TypoEventBus());
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
