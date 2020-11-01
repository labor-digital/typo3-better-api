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

namespace LaborDigital\T3BA\Event\DataHook;

use LaborDigital\T3BA\Tool\DataHook\DataHookContext;

/**
 * Class PostProcessorEvent
 *
 * Triggered once for every data hook handler that was processed by the data hook dispatcher
 *
 * @package LaborDigital\T3BA\Event\DataHook
 */
class PostProcessorEvent
{
    /**
     * The instance of the context that was used by the applied callbacks
     *
     * @var object
     */
    protected $context;

    /**
     * BackendFormActionPostProcessorEvent constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\DataHookContext  $context
     */
    public function __construct(DataHookContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the instance of the context that was used by the applied data hook
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\DataHookContext
     */
    public function getContext(): DataHookContext
    {
        return $this->context;
    }

    /**
     * Returns the type of hook that was processed
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->context->getType();
    }
}
