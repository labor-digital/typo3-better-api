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


namespace LaborDigital\T3BA\Tool\DataHook;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\DataHook\PostProcessorEvent;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;
use LaborDigital\T3BA\Tool\DataHook\Definition\DefinitionResolver;
use Neunerlei\Arrays\Arrays;

class Dispatcher implements PublicServiceInterface
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\DefinitionResolver
     */
    protected $definitionResolver;
    
    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * Dispatcher constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\DataHook\Definition\DefinitionResolver  $definitionResolver
     */
    public function __construct(DefinitionResolver $definitionResolver, TypoEventBus $eventBus)
    {
        $this->definitionResolver = $definitionResolver;
        $this->eventBus = $eventBus;
    }
    
    /**
     * Dispatches a data hook event by executing all registered handlers for the given $type that can be
     * found in the TCA/flexform configuration of the table.
     *
     * This class is considered internal only, as the implementation of types is hard-wired with the matching
     * event handlers. BUT, you could also use it to implement your own hook types. So it is still considered part of
     * the public API.
     *
     * @param   string  $type       The type of hook to execute. This should be one of DataHookTypes::TYPE_
     * @param   string  $tableName  The name of the database table the hook should be executed for
     * @param   array   $data       The array containing the record to be processed by the hook
     * @param   object  $event      The original PSR-14 event which was used to trigger this dispatcher
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     * @throws \LaborDigital\T3BA\Tool\DataHook\DataHookException
     * @see \LaborDigital\T3BA\Tool\DataHook\DataHookTypes
     */
    public function dispatch(string $type, string $tableName, array $data, object $event): DataHookDefinition
    {
        $definition = $this->definitionResolver->resolve($type, $tableName, $data);
        
        if (empty($definition->handlers)) {
            return $definition;
        }
        
        foreach ($definition->handlers as $handlerDefinition) {
            // Create the context object
            $contextClass = $handlerDefinition->options['contextClass'] ?? DataHookContext::class;
            if ($contextClass !== DataHookContext::class
                && ! in_array(DataHookContext::class, class_parents($contextClass), true)) {
                throw new DataHookException('Invalid data hook context class given: ' . $contextClass
                                            . ' The class has to extend the ' . DataHookContext::class . ' class!');
            }
            /** @var \LaborDigital\T3BA\Tool\DataHook\DataHookContext $context */
            $context = $this->makeInstance($contextClass, [$definition, $handlerDefinition, $event]);
            
            // Execute the handler
            call_user_func($handlerDefinition->handler, $context);
            
            $this->eventBus->dispatch(new PostProcessorEvent($context));
            
            // Check if some changes were applied
            if ($context->isDirty()) {
                if ($handlerDefinition->appliesToTable) {
                    $definition->data = $context->getData();
                    $definition->dirtyFields[] = '@table';
                } else {
                    $definition->data = Arrays::setPath($definition->data, $handlerDefinition->path,
                        $context->getData());
                    $definition->dirtyFields[] = reset($handlerDefinition->path);
                }
            }
        }
        
        $definition->dirtyFields = array_unique($definition->dirtyFields);
        
        return $definition;
    }
    
}
