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
 * Last modified: 2020.10.18 at 16:51
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\DataHandler\ActionPostProcessorEvent;
use LaborDigital\T3BA\Event\DataHandler\SaveAfterDbOperationsEvent;
use LaborDigital\T3BA\Event\DataHandler\SaveFilterEvent;
use LaborDigital\T3BA\Event\DataHandler\SavePostProcessorEvent;
use LaborDigital\T3BA\Tool\Database\DbService;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\DataHook\Dispatcher;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class DataHookEventHandler implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Dispatcher
     */
    protected $dispatcher;

    /**
     * @var \LaborDigital\T3BA\Tool\Database\DbService
     */
    protected $dbService;

    /**
     * DataHookEventHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Dispatcher  $dispatcher
     * @param   \LaborDigital\T3BA\Tool\Database\DbService   $dbService
     */
    public function __construct(Dispatcher $dispatcher, DbService $dbService)
    {
        $this->dispatcher = $dispatcher;
        $this->dbService  = $dbService;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(SaveFilterEvent::class, 'onSaveFilter');
        $subscription->subscribe(SavePostProcessorEvent::class, 'onSavePostProcessor');
        $subscription->subscribe(SaveAfterDbOperationsEvent::class, 'onAfterDbOperations');
        $subscription->subscribe(ActionPostProcessorEvent::class, 'onActionPostProcessor');
    }

    /**
     * Run the save hook queue
     *
     * @param   \LaborDigital\T3BA\Event\DataHandler\SaveFilterEvent  $event
     */
    public function onSaveFilter(SaveFilterEvent $event): void
    {
        $this->dispatcher->dispatch(DataHookTypes::TYPE_SAVE,
            $event->getTableName(), $event->getRow(), $event)
                         ->runIfDirty(static function (array $data) use ($event) {
                             $event->setRow($data);
                         });
    }

    /**
     * Run the save post processor queue
     *
     * @param   \LaborDigital\T3BA\Event\DataHandler\SavePostProcessorEvent  $event
     */
    public function onSavePostProcessor(SavePostProcessorEvent $event): void
    {
        $this->dispatcher->dispatch(DataHookTypes::TYPE_SAVE_LATE,
            $event->getTableName(), $event->getRow(), $event)
                         ->runIfDirty(static function (array $data) use ($event) {
                             $event->setRow($data);
                         });
    }

    /**
     * Run the after db operations queue
     *
     * @param   \LaborDigital\T3BA\Event\DataHandler\SaveAfterDbOperationsEvent  $event
     */
    public function onAfterDbOperations(SaveAfterDbOperationsEvent $event): void
    {
        $this->dispatcher->dispatch(DataHookTypes::TYPE_SAVE_AFTER_DB,
            $event->getTableName(), $event->getRow(), $event);
    }

    /**
     * Run the action (copy, delete, ...) hook queue
     *
     * @param   \LaborDigital\T3BA\Event\DataHandler\ActionPostProcessorEvent  $event
     */
    public function onActionPostProcessor(ActionPostProcessorEvent $event): void
    {
        $row = $this->dbService->getQuery($event->getTableName(), true)
                               ->withWhere(['uid' => $event->getId()])
                               ->withVersionOverlay(false)
                               ->getFirst();

        $this->dispatcher->dispatch($event->getCommand(), $event->getTableName(), $row, $event);
    }
}
