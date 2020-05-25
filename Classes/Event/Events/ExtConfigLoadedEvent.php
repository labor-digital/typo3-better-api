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
 * Last modified: 2020.03.19 at 13:05
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

/**
 * Class ExtConfigLoadedEvent
 *
 * Dispatched after the ext config classes have been processed
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Event
 */
class ExtConfigLoadedEvent
{
    /**
     * The context instance that is passed between the ext config classes
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * ExtConfigLoadedEvent constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     */
    public function __construct(ExtConfigContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * Returns the context instance that is passed between the ext config classes
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        return $this->context;
    }
}
