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


namespace LaborDigital\T3ba\Tool\DataHook\Definition\Traverser;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\DataHook\CustomDataHookTraverserEvent;

class TcaTraverser extends AbstractTraverser
{
    /**
     * @var \LaborDigital\T3ba\Tool\DataHook\Definition\Traverser\FlexFormTraverser
     */
    protected $flexFormTraverser;
    
    /**
     * @var \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * TcaTraverser constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\DataHook\Definition\Traverser\FlexFormTraverser  $flexFormTraverser
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus                            $eventBus
     */
    public function __construct(FlexFormTraverser $flexFormTraverser, TypoEventBus $eventBus)
    {
        $this->flexFormTraverser = $flexFormTraverser;
        $this->eventBus = $eventBus;
    }
    
    /**
     * Traverses the tca in order to find the registered handler definitions
     */
    public function traverse(): void
    {
        // Register data hooks on the table
        $this->registerHandlerDefinitions($this->definition->tableName, $this->definition->tca);
        
        // Register data hooks on the types and fields
        $this->traverseTypes();
        $this->traverseFields();
        
        // Allow externals
        $this->eventBus->dispatch(new CustomDataHookTraverserEvent($this->definition, function () {
            $this->registerHandlerDefinitions(...func_get_args());
        }));
    }
    
    /**
     * Traverses the type array of a TCA to find possible data hooks
     */
    protected function traverseTypes(): void
    {
        if (isset($this->definition->tca['types']) && is_array($this->definition->tca['types'])) {
            foreach ($this->definition->tca['types'] as $type => $def) {
                $this->registerHandlerDefinitions($this->definition->tableName, $def);
            }
        }
    }
    
    /**
     * Iterates the TCA columns for all fields inside data to find the registered handler definitions
     *
     * @todo We currently can't detect handlers if the field was not passed to the dispatcher.
     *       Meaning we can't apply hooks to empty fields in order to create defaults. Should/can we change this?
     */
    protected function traverseFields(): void
    {
        $columns = $this->definition->tca['columns'] ?? [];
        foreach ($this->definition->data as $fieldName => $value) {
            if (! is_array($columns[$fieldName])) {
                continue;
            }
            
            $this->registerHandlerDefinitions($fieldName, $columns[$fieldName], [$fieldName]);
            
            // Handle flex form fields
            if (isset($columns[$fieldName]['config']['type']) && $columns[$fieldName]['config']['type'] === 'flex') {
                $this->flexFormTraverser->initialize($this->definition, [$fieldName])->traverse();
            }
        }
    }
}
