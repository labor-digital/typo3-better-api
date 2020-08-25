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
 * Last modified: 2020.03.20 at 16:51
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\Temporary;

use Psr\Container\ContainerInterface;

class BootstrapContainerFilterEvent
{
    
    /**
     * The Better Api TypoContainer wrapper
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;
    
    /**
     * True if the app is running in failsafe mode, false if not
     *
     * @var bool
     */
    protected $failsafe;
    
    /**
     * BootstrapContainerFilterEvent constructor.
     *
     * @param   \Psr\Container\ContainerInterface  $container
     * @param   bool                               $failsafe
     */
    public function __construct(ContainerInterface $container, bool $failsafe)
    {
        $this->container = $container;
        $this->failsafe  = $failsafe;
    }
    
    /**
     * Returns the typo3 core container instance
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
    
    /**
     * Can be used to modify the typo3 core container instance
     *
     * @param   \Psr\Container\ContainerInterface  $container
     *
     * @return BootstrapContainerFilterEvent
     */
    public function setContainer(ContainerInterface $container): BootstrapContainerFilterEvent
    {
        $this->container = $container;
        
        return $this;
    }
    
    /**
     * Returns true if the app is running in failsafe mode, false if not
     *
     * @return bool
     */
    public function isFailsafe(): bool
    {
        return $this->failsafe;
    }
}
