<?php
/*
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
 * Last modified: 2020.10.18 at 17:53
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\Di;

use Psr\Container\ContainerInterface;

/**
 * Class DiContainerFilterEvent
 *
 * Emitted after the dependency injection container was instantiated (not build!)
 * This is emitted every time TYPO3 boots up
 *
 * @package LaborDigital\T3BA\Core\Event
 */
class DiContainerFilterEvent
{
    /**
     * The container instance that was instantiated
     *
     * @var ContainerInterface
     */
    protected $container;
    
    /**
     * DiContainerFilterEvent constructor.
     *
     * @param   \Psr\Container\ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Returns the container instance that was instantiated
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
    
}
