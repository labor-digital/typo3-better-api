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

namespace LaborDigital\T3ba\Event\Core;

/**
 * Class SiteConfigFilterEvent
 *
 * Dispatched when TYPO3 builds it's site configuration and before it is passed down
 * to other core internals.
 *
 * @package LaborDigital\T3ba\Event\Core
 */
class SiteConfigFilterEvent
{
    /**
     * The site configuration array
     *
     * @var array
     */
    protected $config;
    
    /**
     * SiteConfigFilterEvent constructor.
     *
     * @param   array  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Returns the site configuration array
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
    
    /**
     * Used to update the site configuration array
     *
     * @param   array  $config
     *
     * @return SiteConfigFilterEvent
     */
    public function setConfig(array $config): SiteConfigFilterEvent
    {
        $this->config = $config;
        
        return $this;
    }
}
