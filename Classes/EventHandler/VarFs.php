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
 * Last modified: 2020.08.24 at 15:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\Core\CacheClearedEvent;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class VarFs implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3BA\Core\VarFs\VarFs
     */
    protected $fs;

    /**
     * VarFsEventHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Core\VarFs\VarFs  $fs
     */
    public function __construct(\LaborDigital\T3BA\Core\VarFs\VarFs $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(CacheClearedEvent::class, 'onCacheClear');
    }

    /**
     * Flushes all temp fs data from the drive when all caches were cleared
     *
     * @param   \LaborDigital\T3BA\Event\Core\CacheClearedEvent  $event
     */
    public function onCacheClear(CacheClearedEvent $event): void
    {
        if ($event->getGroup() !== 'all') {
            return;
        }

        $this->fs->flush();
    }
}
