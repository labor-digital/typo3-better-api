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
 * Last modified: 2020.03.17 at 09:30
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class ClassOverrideStackFilterEvent
 * Can be used to filter the class override generator stack
 * right before the override is build
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ClassOverrideStackFilterEvent
{
    /**
     * @var array
     */
    protected $stack;
    
    /**
     * ClassOverrideStackFilterEvent constructor.
     *
     * @param array $stack
     */
    public function __construct(array $stack)
    {
        $this->stack = $stack;
    }
    
    /**
     * Returns the list of steps that are required to resolve a class through
     * all it's overrides.
     * @return array
     */
    public function getStack(): array
    {
        return $this->stack;
    }
    
    /**
     * Can be used to set the list of steps that are required to resolve a class through
     * all it's overrides.
     *
     * @param array $stack
     *
     * @return ClassOverrideStackFilterEvent
     */
    public function setStack(array $stack): ClassOverrideStackFilterEvent
    {
        $this->stack = $stack;
        return $this;
    }
}
