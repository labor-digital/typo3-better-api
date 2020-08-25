<?php
/**
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
 * Last modified: 2020.03.21 at 20:48
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionHandlerInterface as BackendActionHandlerInterfaceAlias;

/**
 * Class BackendFormActionPostProcessorEvent
 *
 * Triggered in the backend action handler.
 * Can be used to modify the given context object of every record that is saved using the data handler
 *
 * @package    LaborDigital\Typo3BetterApi\Event\Events
 * @deprecated will be renamed to DataHandlerActionPostProcessorEvent in v10
 */
class BackendFormActionPostProcessorEvent
{
    
    /**
     * The instance of the context that was used by the applied callbacks
     *
     * @var object
     */
    protected $context;
    
    /**
     * The instance of the backend action handler class that was applied
     *
     * @var object
     */
    protected $handler;
    
    /**
     * The type of stack we should find the handlers for
     *
     * @var string
     */
    protected $stackType;
    
    /**
     * BackendFormActionPostProcessorEvent constructor.
     *
     * @param   object  $context
     * @param   object  $handler
     * @param   string  $stackType
     */
    public function __construct(object $context, object $handler, string $stackType)
    {
        $this->context   = $context;
        $this->handler   = $handler;
        $this->stackType = $stackType;
    }
    
    /**
     * Returns the instance of the context that was used by the applied callbacks
     *
     * @return object
     */
    public function getContext(): object
    {
        return $this->context;
    }
    
    /**
     * Returns the instance of the backend action handler class that was applied
     *
     * @return BackendActionHandlerInterfaceAlias
     */
    public function getHandler(): object
    {
        return $this->handler;
    }
    
    /**
     * Returns the type of stack we should find the handlers for
     *
     * @return string
     */
    public function getStackType(): string
    {
        return $this->stackType;
    }
}
