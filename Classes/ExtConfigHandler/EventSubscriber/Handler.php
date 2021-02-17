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
 * Last modified: 2020.08.24 at 08:56
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\EventSubscriber;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\Interfaces\StandAloneHandlerInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Handler extends AbstractExtConfigHandler implements StandAloneHandlerInterface
{
    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\EventSubscriber\CompiledEventSubscription
     */
    protected $lazySubscription;

    /**
     * A list of runtime event subscriber classes
     *
     * @var array
     */
    protected $runtimeSubscribers = [];

    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;

    /**
     * ConfigureEventSubscribersHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(TypoEventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/EventHandler');
        $configurator->registerLocation('Classes/ExtConfigHandler/**');
        $configurator->registerInterface(LazyEventSubscriberInterface::class);
        $configurator->registerInterface(EventSubscriberInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        $this->lazySubscription = GeneralUtility::makeInstance(CompiledEventSubscription::class);
    }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        if (in_array(EventSubscriberInterface::class, class_implements($class), true)) {
            $this->runtimeSubscribers[] = $class;
        } else {
            $this->lazySubscription->setClass($class);
            call_user_func([$class, 'subscribeToEvents'], $this->lazySubscription);
        }
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()
                      ->set('subscribers.lazy', $this->lazySubscription->getSubscribers())
                      ->set('subscribers.runtime', $this->runtimeSubscribers);
    }

}
