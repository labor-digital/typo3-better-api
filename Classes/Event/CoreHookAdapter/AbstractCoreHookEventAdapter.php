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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\CoreHookAdapter;

use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractCoreHookEventAdapter implements CoreHookEventAdapterInterface
{

    /**
     * @var EventBusInterface
     */
    protected static $bus;

    /**
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * @var TypoContext
     */
    protected static $context;

    /**
     * @inheritDoc
     */
    public static function prepare(
        EventBusInterface $bus,
        TypoContext $context,
        ContainerInterface $container
    ): void {
        static::$bus       = $bus;
        static::$container = $container;
        static::$context   = $context;
    }
}
