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


namespace LaborDigital\T3BA\Event\Di;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Class DiContainerBeingBuildEvent
 *
 * Dispatched when the TYPO3 / symfony container is being build.
 * This event can be used like your Services.php
 *
 * @package LaborDigital\T3BA\Core\Event
 */
class DiContainerBeingBuildEvent
{
    /**
     * The container configurator of the class being build
     *
     * @var \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator
     */
    protected $containerConfigurator;
    
    /**
     * The container builder instance
     *
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;
    
    /**
     * DiContainerBeingBuildEvent constructor.
     *
     * @param   \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator  $container
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder                           $containerBuilder
     */
    public function __construct(ContainerConfigurator $container, ContainerBuilder $containerBuilder)
    {
        $this->containerConfigurator = $container;
        $this->containerBuilder = $containerBuilder;
    }
    
    /**
     * Returns the container configurator of the class being build
     *
     * @return \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator
     */
    public function getContainerConfigurator(): ContainerConfigurator
    {
        return $this->containerConfigurator;
    }
    
    /**
     * Returns the container builder instance
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    public function getContainerBuilder(): ContainerBuilder
    {
        return $this->containerBuilder;
    }
    
    
}
