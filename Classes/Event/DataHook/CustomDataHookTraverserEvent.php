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
 * Last modified: 2020.10.19 at 11:37
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\DataHook;


use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;

/**
 * Class CustomDataHookTraverserEvent
 *
 * Allows you to build your own, custom data hook traverser which should traverse the TCA to find data hook handlers.
 *
 * @package LaborDigital\T3BA\Event\DataHook
 */
class CustomDataHookTraverserEvent
{
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    protected $definition;

    /**
     * @var callable
     */
    protected $registerHook;

    /**
     * CustomDataHookTraverserEvent constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     * @param   callable                                                        $registerHook
     */
    public function __construct(DataHookDefinition $definition, callable $registerHook)
    {
        $this->definition   = $definition;
        $this->registerHook = $registerHook;
    }

    /**
     * Returns the definition of the data hook we should traverse the TCA for
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    public function getDefinition(): DataHookDefinition
    {
        return $this->definition;
    }

    /**
     * Registers all matching handler definitions for the given node on the hook definition.
     *
     * NOTE: This is a link into the TcaTraverser which dispatches the event.
     *
     * @param   string  $nodeKey  The unique key of the node which gets traversed.
     *                            Either the name of the table or a specific field
     * @param   array   $tca      The TCA of the current table/field that gets traversed
     * @param   array   $path     The path through the multi-dimensional data structure to select the value with
     *                            This MUST be empty if the handlers for a whole table are registered
     *
     * @see \LaborDigital\T3BA\Tool\DataHook\Definition\Traverser\AbstractTraverser::registerHandlerDefinitions()
     */
    public function registerHandlerDefinitions(string $nodeKey, array $tca, array $path = []): void
    {
        call_user_func($this->registerHook, $nodeKey, $tca, $path);
    }
}
