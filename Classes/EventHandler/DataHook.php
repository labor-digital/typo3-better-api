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


namespace LaborDigital\T3ba\EventHandler;


use LaborDigital\T3ba\Event\DataHandler\ActionPostProcessorEvent;
use LaborDigital\T3ba\Event\DataHandler\SaveAfterDbOperationsEvent;
use LaborDigital\T3ba\Event\DataHandler\SaveFilterEvent;
use LaborDigital\T3ba\Event\DataHandler\SavePostProcessorEvent;
use LaborDigital\T3ba\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\DataHook\DataHookTypes;
use LaborDigital\T3ba\Tool\DataHook\Dispatcher;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class DataHook implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\DataHook\Dispatcher
     */
    protected $dispatcher;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected $dbService;
    
    /**
     * DataHookEventHandler constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\DataHook\Dispatcher  $dispatcher
     * @param   \LaborDigital\T3ba\Tool\Database\DbService   $dbService
     */
    public function __construct(Dispatcher $dispatcher, DbService $dbService)
    {
        $this->dispatcher = $dispatcher;
        $this->dbService = $dbService;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(SaveFilterEvent::class, 'onSaveFilter');
        $subscription->subscribe(SavePostProcessorEvent::class, 'onSavePostProcessor');
        $subscription->subscribe(SaveAfterDbOperationsEvent::class, 'onAfterDbOperations');
        $subscription->subscribe(ActionPostProcessorEvent::class, 'onActionPostProcessor');
        $subscription->subscribe(FormFilterEvent::class, 'onFormFilter');
    }
    
    /**
     * Run the save hook queue
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\SaveFilterEvent  $event
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
     * @param   \LaborDigital\T3ba\Event\DataHandler\SavePostProcessorEvent  $event
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
     * @param   \LaborDigital\T3ba\Event\DataHandler\SaveAfterDbOperationsEvent  $event
     */
    public function onAfterDbOperations(SaveAfterDbOperationsEvent $event): void
    {
        $this->dispatcher->dispatch(DataHookTypes::TYPE_SAVE_AFTER_DB,
            $event->getTableName(), $event->getRow(), $event);
    }
    
    /**
     * Run the action (copy, delete, ...) hook queue
     *
     * @param   \LaborDigital\T3ba\Event\DataHandler\ActionPostProcessorEvent  $event
     */
    public function onActionPostProcessor(ActionPostProcessorEvent $event): void
    {
        $row = $this->dbService->getQuery($event->getTableName(), true)
                               ->withWhere(['uid' => $event->getId()])
                               ->withVersionOverlay(false)
                               ->getFirst();
        
        $this->dispatcher->dispatch($event->getCommand(), $event->getTableName(), $row, $event);
    }
    
    /**
     * Run the form filter hook queue
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\FormFilterEvent  $event
     */
    public function onFormFilter(FormFilterEvent $event): void
    {
        $this->dispatcher->dispatch(DataHookTypes::TYPE_FORM,
            $event->getTableName(), $event->getData()['databaseRow'], $event)
                         ->runIfDirty(static function (array $row) use ($event) {
                             $data = $event->getData();
                             $data['databaseRow'] = $row;
                             $event->setData($data);
                         });
    }
}
