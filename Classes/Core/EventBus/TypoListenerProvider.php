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
 * Last modified: 2020.08.22 at 21:56
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Core\EventBus;

use LaborDigital\T3BA\Core\Di\MiniContainer;
use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventAdapterInterface;
use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\Dispatcher\EventBusListenerProvider;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\EventDispatcher\ListenerProvider;

class TypoListenerProvider extends ListenerProvider
{

    /**
     * The list of core hook events that have been registered already
     *
     * @var array
     */
    protected $boundCoreHooks = [];

    /**
     * The real listener provider we use under the hood
     *
     * @var \Neunerlei\EventBus\Dispatcher\EventBusListenerProvider
     */
    protected $concreteListenerProvider;

    /**
     * The container instance to create core hooks
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * TypoListenerProvider constructor.
     */
    public function __construct()
    {
        parent::__construct(new MiniContainer());
        $this->concreteListenerProvider = new EventBusListenerProvider();
    }

    /**
     * Used to inject the PSR service container after it was created
     *
     * @param   \Psr\Container\ContainerInterface  $container
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function addListener(string $event, string $service, string $method = null, array $options = []): void
    {
        $this->listeners[$event][] = [
            'service' => $service,
            'method'  => $method,
        ];

        $this->registerCoreHookEventIfRequired($event);

        $this->concreteListenerProvider->addListener($event, function ($e) use ($service, $method) {
            $this->getCallable($service, $method)($e);
        }, $options);
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->concreteListenerProvider->getListenersForEvent($event);
    }

    /**
     * Registers a new listener for a certain event
     *
     * @param   string    $eventClassName  The name of the event class to bind this listener to
     * @param   callable  $listener        The listener callable to represent
     * @param   array     $options         The options to bind this listener with
     *
     * @return string A unique id for the registered listener
     *
     * @see \Neunerlei\EventBus\EventBusInterface::addListener() for details on the options
     */
    public function addCallableListener(
        string $eventClassName,
        callable $listener,
        array $options = []
    ): string {
        $this->registerCoreHookEventIfRequired($eventClassName);

        return $this->concreteListenerProvider->addListener($eventClassName, $listener, $options);
    }

    /**
     * Internal helper to call the bind method if the given $eventClass
     * implements the core hook event interface
     *
     * @param   string  $eventClass
     *
     * @throws \LaborDigital\T3BA\Core\EventBus\EventException
     * @see CoreHookEventInterface
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
        if (! in_array(CoreHookEventInterface::class, class_implements($eventClass), true)) {
            return;
        }

        // Validate the adapter class
        $adapterClass = call_user_func([$eventClass, 'getAdapterClass']);
        if (! class_exists($adapterClass)) {
            throw new EventException(
                'The class "' . $eventClass . '" returned "' . $adapterClass .
                '" as its core hook adapter, but the class does not exist!');
        }
        if (! in_array(
            CoreHookEventAdapterInterface::class,
            class_implements($adapterClass),
            true
        )) {
            throw new EventException(
                'The class "' . $eventClass . '" returned "' . $adapterClass .
                '" as its core hook adapter, but the adapter does not implement the required interface: "'
                . CoreHookEventAdapterInterface::class . '"!');
        }
        if (isset($this->boundCoreHooks[$adapterClass])) {
            return;
        }

        // Bind the adapter
        $context = TypoContext::getInstance();
        call_user_func(
            [$adapterClass, 'prepare'],
            $context->di()->cs()->eventBus,
            $context
        );
        call_user_func([$adapterClass, 'bind']);
        $this->boundCoreHooks[$adapterClass] = true;
        $this->boundCoreHooks[$eventClass]   = true;
    }
}
