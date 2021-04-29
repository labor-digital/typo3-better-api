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
 * Last modified: 2020.08.23 at 16:46
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Di;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

interface ConfigureDiInterface
{
    
    /**
     * Basically the Services.php api of the TYPO3 core, but neatly packed into a class interface
     *
     * @param   ContainerConfigurator  $configurator
     * @param   ContainerBuilder       $containerBuilder
     * @param   ExtConfigContext       $context
     *
     * @see https://symfony.com/doc/current/service_container.html
     * @see https://usetypo3.com/dependency-injection.html
     */
    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void;
    
    /**
     * Allows you to configure the container instance at runtime
     * This method is executed every time when TYPO3 boots, and allows you to inject
     * dynamic/synthetic services into the container if required
     *
     * @param   \Symfony\Component\DependencyInjection\Container  $container
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext     $context
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void;
    
}
