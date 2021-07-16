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
 * Last modified: 2021.07.16 at 15:55
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Configuration;

/**
 * Class BackendRouteRegistrationEvent
 *
 * Is used to gather backend routes that will be injected into TYPO3s routing config.
 *
 * @package LaborDigital\T3ba\Event\Configuration
 */
class BackendRouteRegistrationEvent
{
    /**
     * The list of collected route configurations by their names
     *
     * @var array
     */
    protected $routes = [];
    
    /**
     * True if ajax routes are currently collected
     *
     * @var bool
     */
    protected $isAjax;
    
    public function __construct(bool $isAjax)
    {
        $this->isAjax = $isAjax;
    }
    
    /**
     * Returns true if ajax routes are currently collected
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return $this->isAjax;
    }
    
    /**
     * Returns the list of collected route configurations by their names
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
    /**
     * Updates the list of all currently gathered routes.
     * The format is equivalent to the default configuration format used by the TYPO3 core under Routes.php
     *
     * @param   array  $routes
     *
     * @return $this
     */
    public function setRoutes(array $routes): self
    {
        $this->routes = $routes;
        
        return $this;
    }
}