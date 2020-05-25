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
 * Last modified: 2020.03.19 at 11:25
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class ExtConfigBeforeLoadEvent
 *
 * Dispatched before the ext config classes are sorted.
 * Can be used to manually add additional configurations into the stack before it is processed.
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Event
 */
class ExtConfigBeforeLoadEvent
{
    /**
     * The raw list of all registered ext config classes.
     * Contains a numeric list of arrays like: [$extKeyWithVendor, $configurationClass, $options]
     * @var array
     */
    protected $rawConfigList;
    
    /**
     * ExtConfigBeforeLoadEvent constructor.
     *
     * @param array $rawConfigList
     */
    public function __construct(array $rawConfigList)
    {
        $this->rawConfigList = $rawConfigList;
    }
    
    /**
     * Return the raw list of all registered ext config classes
     * @return array
     */
    public function getRawConfigList(): array
    {
        return $this->rawConfigList;
    }
    
    /**
     * Sets the raw list of all registered ext config classes
     *
     * @param array $rawConfigList
     *
     * @return ExtConfigBeforeLoadEvent
     */
    public function setRawConfigList(array $rawConfigList): ExtConfigBeforeLoadEvent
    {
        $this->rawConfigList = $rawConfigList;
        return $this;
    }
}
