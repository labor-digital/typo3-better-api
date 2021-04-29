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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\CoreHookAdapter;

use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;

abstract class AbstractCoreHookEventAdapter implements CoreHookEventAdapterInterface
{
    /**
     * @var TypoEventBus
     */
    protected static $bus;
    
    /**
     * @var TypoContext
     */
    protected static $context;
    
    /**
     * @inheritDoc
     */
    public static function prepare(
        TypoEventBus $bus,
        TypoContext $context
    ): void
    {
        static::$bus = $bus;
        static::$context = $context;
    }
    
    /**
     * @return \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    public function EventBus(): TypoEventBus
    {
        return static::$bus;
    }
    
    /**
     * @return \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    public function TypoContext(): TypoContext
    {
        return static::$context;
    }
}
