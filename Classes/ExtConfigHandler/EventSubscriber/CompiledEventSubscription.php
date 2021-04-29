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
 * Last modified: 2020.08.24 at 09:01
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\EventSubscriber;


use Composer\EventDispatcher\EventSubscriberInterface;
use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class CompiledEventSubscription implements EventSubscriptionInterface
{
    /**
     * The handler class to configure
     *
     * @var string
     */
    protected $class;
    
    /**
     * The list of subscribers
     *
     * @var array
     */
    protected $subscribers = [];
    
    /**
     * Internal helper to set the handler class to configure
     *
     * @param   string  $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }
    
    /**
     * @inheritDoc
     */
    public function subscribe($events, string $method, array $options = []): EventSubscriptionInterface
    {
        // Handle lists
        if (is_iterable($events)) {
            foreach ($events as $event) {
                $this->subscribe($event, $method, $options);
            }
            
            return $this;
        }
        
        $this->subscribers[] = [$events, $this->class, $method, $options];
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getBus(): EventBusInterface
    {
        throw new NotImplementedException(
            'This method does not work for lazy event subscribers! Please use the '
            . EventSubscriberInterface::class . ' instead, to add listeners directly to the bus!');
    }
    
    /**
     * Internal helper to extract the registered subscribers
     *
     * @return array
     */
    public function getSubscribers(): array
    {
        return $this->subscribers;
    }
    
}
