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
 * Last modified: 2020.03.21 at 16:42
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class ExtConfigExtendableFeatureFilterEvent
 *
 * Dispatched when the ext config service collects the configuration objects.
 * Allows to filter the extendable features
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ExtConfigExtendableFeatureFilterEvent
{
    /**
     * The list of all registered ext config extendable feature definition classes
     *
     * @var array
     */
    protected $registeredExtensions;
    
    /**
     * The list of registered extension handlers by their extension type
     *
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface[]
     */
    protected $handlers;
    
    /**
     * ExtConfigExtendableFeatureFilterEvent constructor.
     *
     * @param   array  $registeredExtensions
     * @param   array  $handlers
     */
    public function __construct(array $registeredExtensions, array $handlers)
    {
        $this->registeredExtensions = $registeredExtensions;
        $this->handlers             = $handlers;
    }
    
    /**
     * Returns the list of all registered ext config extendable feature definition classes
     *
     * @return array
     */
    public function getRegisteredExtensions(): array
    {
        return $this->registeredExtensions;
    }
    
    /**
     * Can be used to update the list of all registered ext config extendable feature definition classes
     *
     * @param   array  $registeredExtensions
     *
     * @return ExtConfigExtendableFeatureFilterEvent
     */
    public function setRegisteredExtensions(array $registeredExtensions): ExtConfigExtendableFeatureFilterEvent
    {
        $this->registeredExtensions = $registeredExtensions;
        
        return $this;
    }
    
    /**
     * Returns the list of registered extension handlers by their extension type
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
    
    /**
     * Can be used to modify the list of registered extension handlers by their extension type
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface[]  $handlers
     *
     * @return ExtConfigExtendableFeatureFilterEvent
     */
    public function setHandlers(array $handlers): ExtConfigExtendableFeatureFilterEvent
    {
        $this->handlers = $handlers;
        
        return $this;
    }
}
