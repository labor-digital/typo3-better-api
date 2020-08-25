<?php
/**
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
 * Last modified: 2020.03.18 at 19:35
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\ListenerProvider;

use LaborDigital\Typo3BetterApi\Event\EventException;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventAdapterInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\EventBus\Dispatcher\EventBusListenerProvider;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class TypoListenerProvider extends EventBusListenerProvider
{
    
    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;
    
    /**
     * The list of core hook events that have been registered already
     *
     * @var array
     */
    protected $boundCoreHooks = [];
    
    /**
     * The container instance to create core hooks
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;
    
    
    /**
     * Is used to set the signal slot dispatcher and the container instances after they are created
     *
     * @param   \TYPO3\CMS\Extbase\SignalSlot\Dispatcher  $dispatcher
     * @param   \Psr\Container\ContainerInterface         $container
     */
    public function setHighLevelDependencies(Dispatcher $dispatcher, ContainerInterface $container): void
    {
        $this->signalSlotDispatcher = $dispatcher;
        $this->container            = $container;
    }
    
    /**
     * @inheritDoc
     */
    public function addListener(
        string $eventClassName,
        callable $listener,
        array $options = []
    ): string {
        if ($this->registerSpecialEvents($eventClassName, $listener)) {
            return parent::addListener($eventClassName, $listener, $options);
        }
        
        return md5($eventClassName . microtime() . mt_rand());
    }
    
    /**
     * Internal helper to register the special actions for an event class
     *
     * @param   string    $eventClass
     * @param   callable  $listener
     *
     * @return bool
     */
    protected function registerSpecialEvents(
        string $eventClass,
        callable $listener
    ): bool {
        if ($this->registerSignalSlotIfRequired($eventClass, $listener)) {
            return false;
        }
        $this->registerCoreHookEventIfRequired($eventClass);
        
        return true;
    }
    
    /**
     * Internal helper to call the bind method if the given $eventClass
     * implements the core hook event interface
     *
     * @param   string  $eventClass
     *
     * @throws \LaborDigital\Typo3BetterApi\Event\EventException
     * @see \LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface
     */
    protected function registerCoreHookEventIfRequired(string $eventClass): void
    {
        // Check if we got a hook class
        if (isset($this->boundCoreHooks[$eventClass])) {
            return;
        }
        if (! class_exists($eventClass)) {
            return;
        }
        if (! in_array(
            CoreHookEventInterface::class,
            class_implements($eventClass),
            true
        )) {
            return;
        }
        
        // Check if we got the container
        if (empty($this->container)) {
            throw new EventException('You can\'t register core hook events before the container instance is loaded!');
        }
        
        // Validate the adapter class
        $adapterClass = call_user_func([$eventClass, 'getAdapterClass']);
        if (! class_exists($adapterClass)) {
            throw new EventException("The class \"$eventClass\" returned \"$adapterClass\" as it's core hook adapter, but the class does not exist!");
        }
        if (! in_array(
            CoreHookEventAdapterInterface::class,
            class_implements($adapterClass),
            true
        )) {
            throw new EventException("The class \"$eventClass\" returned \"$adapterClass\" as it's core hook adapter, but the adapter does not implement the required interface: \""
                                     . CoreHookEventAdapterInterface::class
                                     . '"!');
        }
        if (isset($this->boundCoreHooks[$adapterClass])) {
            return;
        }
        
        // Bind the adapter
        call_user_func(
            [$adapterClass, 'prepare'],
            TypoEventBus::getInstance(),
            $this->container->get(TypoContext::class),
            $this->container
        );
        call_user_func([$adapterClass, 'bind']);
        $this->boundCoreHooks[$adapterClass] = true;
        $this->boundCoreHooks[$eventClass]   = true;
    }
    
    /**
     * Internal helper to register the signal in the extbase signal slot
     * dispatcher if we don't know it yet.
     *
     * @param   string    $eventClass  The class of the event to bind
     * @param   callable  $listener    The listener to bind to a signal
     *
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\Event\EventException
     */
    protected function registerSignalSlotIfRequired(
        string $eventClass,
        callable $listener
    ): bool {
        // Check if a valid "className.signal" event was given
        if (class_exists($eventClass) || strpos($eventClass, '.') === false
            || count(explode('.', $eventClass)) !== 2) {
            return false;
        }
        
        // Check if we got the signal slot dispatcher
        if (empty($this->signalSlotDispatcher)) {
            throw new EventException('You can\'t register signal slot events events before the dispatcher instance is loaded!');
        }
        
        // Unpack the selector and connect the signal
        [$class, $signal] = array_map('trim', explode('.', $eventClass));
        $this->signalSlotDispatcher->connect($class, $signal, $listener);
        
        // Done
        return true;
    }
}
