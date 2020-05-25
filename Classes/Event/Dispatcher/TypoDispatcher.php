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
 * Last modified: 2020.03.18 at 18:58
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Dispatcher;

use LaborDigital\Typo3BetterApi\CoreModding\FailsafeWrapper;
use LaborDigital\Typo3BetterApi\Event\EventException;
use LaborDigital\Typo3BetterApi\Event\Events\SignalSlotEvent;
use Neunerlei\EventBus\Dispatcher\EventBusDispatcher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

class TypoDispatcher implements EventDispatcherInterface
{
    
    /**
     * @var EventDispatcherInterface
     */
    protected $lowLevelDispatcher;
    
    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     */
    protected $signalSlotDispatcher;
    
    /**
     * TypoDispatcher constructor.
     *
     * @param   \Psr\EventDispatcher\ListenerProviderInterface  $listenerProvider
     */
    public function __construct(ListenerProviderInterface $listenerProvider)
    {
        $this->lowLevelDispatcher = new EventBusDispatcher($listenerProvider);
    }
    
    /**
     * @inheritDoc
     */
    public function dispatch(object $event)
    {
        return FailsafeWrapper::handle(function () use ($event) {
            
            // Check if we got a signal slot event
            if ($event instanceof SignalSlotEvent) {
                // Fail if we don't have the dispatcher yet
                if (empty($this->signalSlotDispatcher)) {
                    throw new EventException('You can\'t emit the signal slot event when the signal slot dispatcher is not loaded!');
                }
                
                // Emit the event using the dispatcher
                $args
                    = $this->signalSlotDispatcher->dispatch(
                        $event->getClassName(),
                        $event->getSignalName(),
                        $event->getArgs()
                    );
                $event->setArgs($args);
                
                return $event;
            }
            
            // Default handling
            return $this->lowLevelDispatcher->dispatch($event);
        });
    }
    
    /**
     * Used to inject the signal slot dispatcher after it was instantiated
     *
     * @param   \TYPO3\CMS\Extbase\SignalSlot\Dispatcher  $dispatcher
     */
    public function setSignalSlotDispatcher(Dispatcher $dispatcher): void
    {
        $this->signalSlotDispatcher = $dispatcher;
    }
}
