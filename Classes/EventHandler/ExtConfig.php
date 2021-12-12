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


use LaborDigital\T3ba\Event\Core\ExtConfigLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Loader\MainLoader;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class ExtConfig implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\ExtConfig\Loader\MainLoader
     */
    protected $loader;
    
    /**
     * ExtConfigEventHandler constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfig\Loader\MainLoader  $loader
     */
    public function __construct(MainLoader $loader)
    {
        $this->loader = $loader;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtConfigLoadedEvent::class, 'onExtConfigLoaded', ['priority' => 100]);
    }
    
    /**
     * Executes the ext config loader
     */
    public function onExtConfigLoaded(): void
    {
        $this->loader->load();
    }
}
