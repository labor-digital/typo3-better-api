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


namespace LaborDigital\T3BA\Tool\DataHook\Definition\Traverser;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\DataHook\DataHookException;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookHandlerDefinition;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;

abstract class AbstractTraverser
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    protected $definition;
    
    /**
     * Prepares the traverser with the required data to traverse
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition  $definition
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\Traverser\AbstractTraverser
     */
    public function initialize(DataHookDefinition $definition): self
    {
        $this->definition = $definition;
        
        return $this;
    }
    
    /**
     * Traverses the data structure set with calling the initialize() method
     */
    abstract public function traverse(): void;
    
    /**
     * Registers all matching handler definitions for the given node on the hook definition
     *
     * @param   string  $nodeKey  The unique key of the node which gets traversed.
     *                            Either the name of the table or a specific field
     * @param   array   $tca      The TCA of the current table/field that gets traversed
     * @param   array   $path     The path through the multi-dimensional data structure to select the value with
     *                            This MUST be empty if the handlers for a whole table are registered
     */
    protected function registerHandlerDefinitions(string $nodeKey, array $tca, array $path = []): void
    {
        // Ignore if there is nothing relevant for us here
        if (! is_array($tca) || ! is_array($tca[DataHookTypes::TCA_DATA_HOOK_KEY])
            || ! is_array($tca[DataHookTypes::TCA_DATA_HOOK_KEY][$this->definition->type])) {
            return;
        }
        
        foreach ($tca[DataHookTypes::TCA_DATA_HOOK_KEY][$this->definition->type] as $handler) {
            // Resolve a string like class->method into a handler
            // or an array containing a typical php callable
            if (is_string($handler) || is_array($handler) && count($handler) === 2 && is_callable($handler)) {
                $handler = [$handler, []];
            }
            if (is_array($handler)) {
                $handler = [
                    $handler[0],
                    $handler[1] ?? [],
                ];
            } else {
                throw new DataHookException(
                    'Invalid data hook handler for node: ' . $nodeKey
                    . ' given! Only strings and arrays are allowed, given type: ' . gettype($handler));
            }
            
            // Check if the handler matches the constraints
            if (! empty($handler[1]) && is_array($handler[1]['constraints'])
                && count(array_intersect_assoc($handler[1]['constraints'], $this->definition->data))
                   !== count($handler[1]['constraints'])) {
                continue;
            }
            
            $handlerDefinition = $this->makeInstance(DataHookHandlerDefinition::class);
            $handlerDefinition->handler = NamingUtil::resolveCallable($handler[0]);
            $handlerDefinition->key = $nodeKey;
            $handlerDefinition->path = $path;
            $handlerDefinition->appliesToTable = empty($path);
            $handlerDefinition->tca = $tca;
            $handlerDefinition->options = is_array($handler[1]) ? $handler[1] : [];
            
            if (! empty($path)) {
                $handlerDefinition->data = Arrays::getPath($this->definition->data, $path);
            }
            
            $this->definition->handlers[] = $handlerDefinition;
        }
    }
}
