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

namespace LaborDigital\T3ba\Event\CoreHookAdapter;

use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;

interface CoreHookEventAdapterInterface
{
    /**
     * Provides some dependencies that are likely to be required in the event adapter implementation
     *
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus    $bus
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $context
     *
     * @return void
     */
    public static function prepare(
        TypoEventBus $bus,
        TypoContext $context
    ): void;
    
    /**
     * This method is called as soon as the first handler is registered for the matched event
     *
     * @return void
     */
    public static function bind(): void;
}
