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
 * Last modified: 2020.03.18 at 15:06
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class BackendFormActionContextFilterEvent
 *
 * Triggered in the backend action handler.
 * It allows other scripts to change the context class that will be transferred to the
 * backend action handlers
 *
 * @package    LaborDigital\Typo3BetterApi\Event\Events
 * @deprecated will be renamed to DataHandlerActionContextFilterEvent in v10
 */
class BackendFormActionContextFilterEvent
{
    /**
     * The name of the class that is used as a context in the filter callbacks
     *
     * @var string
     */
    protected $contextClass;
    
    /**
     * The context configuration options as array
     *
     * @var array
     */
    protected $config;
    
    /**
     * The type of stack we should find the handlers for
     *
     * @var string
     */
    protected $stackType;
    
    /**
     * BackendFormActionContextFilterEvent constructor.
     *
     * @param   string  $contextClass
     * @param   array   $config
     * @param   string  $stackType
     */
    public function __construct(string $contextClass, array $config, string $stackType)
    {
        $this->contextClass = $contextClass;
        $this->config       = $config;
        $this->stackType    = $stackType;
    }
    
    /**
     * Returns the name of the class that is used as a context in the filter callbacks
     *
     * @return string
     */
    public function getContextClass(): string
    {
        return $this->contextClass;
    }
    
    /**
     * Can be used to update the name of the class that is used as a context in the filter callbacks
     *
     * @param   string  $contextClass
     *
     * @return BackendFormActionContextFilterEvent
     */
    public function setContextClass(string $contextClass): BackendFormActionContextFilterEvent
    {
        $this->contextClass = $contextClass;
        
        return $this;
    }
    
    /**
     * Returns the context configuration options as array
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
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
