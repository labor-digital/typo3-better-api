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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Di;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Class DefaultDependencyInjectionConfig
 *
 * Automatically sets up the auto wiring for your dependency injection container, this is perfect if you don't
 * want to to anything with the container.
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\DependencyInjection
 */
abstract class DefaultDiConfig implements ConfigureDiInterface
{
    use DiAutoConfigTrait;
    
    /**
     * @inheritDoc
     */
    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void
    {
        static::autoWire();
    }
    
    /**
     * @inheritDoc
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void
    {
        // Silence
    }
}
