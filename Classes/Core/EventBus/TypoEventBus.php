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

use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use Neunerlei\EventBus\EventBus;
use Neunerlei\EventBus\EventBusInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class TypoEventBus
 *
 * The extended event bus instance to use the TYPO3 singleton interface
 *
 * @package LaborDigital\Typo3BetterApi\Event
 */
class TypoEventBus extends EventBus implements SingletonInterface, PublicServiceInterface
{
    /**
     * @var EventBusInterface
     */
    protected static $eventBus;

    /**
     * Returns the event bus instance
     *
     * @return \Neunerlei\EventBus\EventBusInterface
     * @throws \LaborDigital\T3BA\Core\EventBus\EventBusNotInitializedException
     */
    public static function getInstance(): EventBusInterface
    {
        if (empty(static::$eventBus)) {
            throw new EventBusNotInitializedException('The event bus instance was not injected using setInstance()');
        }

        return static::$eventBus;
    }

    /**
     * Internal helper to inject the event bus instance into the class
     *
     * @param   \Neunerlei\EventBus\EventBusInterface  $eventBus
     *
     * @internal
     */
    public static function setInstance(EventBusInterface $eventBus): void
    {
        static::$eventBus = $eventBus;
    }
}