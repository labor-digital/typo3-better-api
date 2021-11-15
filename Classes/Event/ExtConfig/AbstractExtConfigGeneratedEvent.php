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
 * Last modified: 2021.11.15 at 12:42
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\ExtConfig;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use Neunerlei\Configuration\State\ConfigState;

abstract class AbstractExtConfigGeneratedEvent
{
    /**
     * The currently active config context
     *
     * @var \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The state object to be filtered
     *
     * @var \Neunerlei\Configuration\State\ConfigState
     */
    protected $state;
    
    public function __construct(ExtConfigContext $context, ConfigState $state)
    {
        $this->context = $context;
        $this->state = $state;
    }
    
    /**
     * Returns the currently active config context
     *
     * @return \LaborDigital\T3ba\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        return $this->context;
    }
    
    /**
     * Returns the state object to be filtered
     *
     * @return \Neunerlei\Configuration\State\ConfigState
     */
    public function getState(): ConfigState
    {
        return $this->state;
    }
}