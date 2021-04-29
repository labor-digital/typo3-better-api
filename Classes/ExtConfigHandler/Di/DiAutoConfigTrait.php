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
 * Last modified: 2020.08.25 at 09:16
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Di;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;

trait DiAutoConfigTrait
{
    /**
     * @var ContainerConfigurator
     */
    protected static $configurator;
    
    /**
     * @var ExtConfigContext
     */
    protected static $context;
    
    /**
     * Injects the dependencies required to perform the service auto wiring
     *
     * @param   ContainerConfigurator  $configurator
     * @param   ExtConfigContext       $context
     */
    public static function setAutoWiringDependencies(
        ContainerConfigurator $configurator,
        ExtConfigContext $context
    ): void
    {
        static::$configurator = $configurator;
        static::$context = $context;
    }
    
    /**
     * Automatically defines all classes in the registered PSR-4 auto loading definitions of your composer.json
     * (that point to a /Classes directory) as resources (auto-wired and auto-configured) for the symfony container.
     *
     * @param   array  $excludePaths       Additional path definitions to exclude when loading the resources
     * @param   array  $additionalNsPaths  Additional Namespace => Directory maps to load in addition to the PSR-4
     *                                     definitions in your composer.json. The Syntax is the same to the one you use
     *                                     in the composer file: ["Your\Namespace\" => "Classes"]
     *
     * @return \Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator
     * @see https://symfony.com/doc/current/service_container.html#service-container-services-load-example
     */
    protected static function autoWire(array $excludePaths = [], array $additionalNsPaths = []): ServicesConfigurator
    {
        $services = static::$configurator->services();
        $defaults = $services->defaults()->autowire()->autoconfigure();
        $namespaceMap = static::$context->getExtConfigService()->getExtKeyNamespaceMap();
        $namespaceMap = array_merge($namespaceMap, $additionalNsPaths);
        foreach ($namespaceMap[static::$context->getExtKey()] ?? [] as $namespace => $dir) {
            try {
                $defaults
                    ->load($namespace, $dir . '/*')
                    ->exclude(array_merge([$dir . '/Event', '{Tests,Test}'], $excludePaths));
            } catch (FileLocatorFileNotFoundException $e) {
            }
        }
        
        return $services;
    }
}
