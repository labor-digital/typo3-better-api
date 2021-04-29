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
 * Last modified: 2021.04.29 at 22:18
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\EventSubscriber;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;

/**
 * Class EventSubscriberBridge
 *
 * Internal helper to let the di container configure the static listeners.
 * This is only used to bind static event subscribers into the listener provider at runtime.
 * It should not be used in your code,
 *
 * @package LaborDigital\T3BA\ExtConfigHandler\EventSubscriber
 */
class EventSubscriberBridge
{
    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoListenerProvider
     */
    protected $provider;
    
    /**
     * EventSubscriberBridge constructor.
     *
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(TypoEventBus $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->provider = $eventBus->getConcreteListenerProvider();
    }
    
    public function addListener($event, $class, $method, $options): self
    {
        $this->provider->addListener($event, $class, $method, $options);
        
        return $this;
    }
    
    public function addSubscriber(EventSubscriberInterface $subscriber): self
    {
        $this->eventBus->addSubscriber($subscriber);
        
        return $this;
    }
}
