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
 * Last modified: 2020.08.23 at 20:03
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\ExtConfigHandler\DependencyInjection;


use LaborDigital\T3BA\Core\ExtConfig\ExtConfigContext;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

trait DefaultDependencyInjectionConfigurationTrait
{

    /**
     * Used in the config handler in order to apply the default symfony container configuration to set
     * up the auto-wiring and auto configuration
     *
     * @param   \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator  $configurator
     * @param   \LaborDigital\T3BA\Core\ExtConfig\ExtConfigContext                                $context
     */
    public static function configureDefaults(
        ContainerConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        // Enable auto wiring
        $services = $configurator
            ->services()
            ->defaults()
            ->autowire()
            ->autoconfigure();

        // Register the services
        $namespaceMap = $context->getExtConfigService()->getExtKeyNamespaceMap();
        foreach ($namespaceMap[$context->getExtKey()] ?? [] as $namespace => $dir) {
            try {
                $services->load($namespace, $dir . '/*')->exclude($dir . '/{Tests,Test}');
            } catch (FileLocatorFileNotFoundException $e) {
            }
        }
    }
}
