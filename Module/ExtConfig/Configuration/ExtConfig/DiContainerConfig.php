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
 * Last modified: 2020.08.24 at 19:36
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Configuration\ExtConfig;


use LaborDigital\T3BA\Core\ExtConfigHandler\DependencyInjection\ConfigureDependencyInjectionInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use Neunerlei\Configuration\State\ConfigState;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class DiContainerConfig implements ConfigureDependencyInjectionInterface
{
    /**
     * @inheritDoc
     */
    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void {
        // Enable auto wiring
        $services = $configurator->services()->defaults()->autowire()->autoconfigure();
        $basePath = 'Module/ExtConfig/Classes/';
        $services->load('LaborDigital\\T3BA\\ExtConfig\\', $basePath . '*')
                 ->exclude([
                     $basePath . 'ExtConfigService.php',
                 ]);

        // Services
        $containerBuilder->findDefinition(ExtConfigContext::class)->setPublic(true)->setSynthetic(true);
        $containerBuilder->setDefinition(ExtConfigService::class, new Definition(ExtConfigService::class))
                         ->setPublic(true)->setSynthetic(true);
        $containerBuilder->setDefinition(ConfigState::class, new Definition(ConfigState::class))
                         ->setPublic(true)->setSynthetic(true);
    }

    /**
     * @inheritDoc
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void
    {
    }

}
