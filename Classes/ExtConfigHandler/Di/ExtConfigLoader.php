<?php /*
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

/** @noinspection PhpMissingStrictTypesDeclarationInspection */

namespace LaborDigital\T3ba\ExtConfigHandler\Di;


use Closure;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ExtConfigLoader extends PhpFileLoader
{
    /**
     * Wraps around the ext config loading process
     *
     * @param   string                                         $class
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext  $context
     * @param   string                                         $packagePath
     * @param   callable                                       $callback
     */
    public function runExtConfigLoad(string $class, ExtConfigContext $context, string $packagePath, callable $callback)
    {
        $this->setCurrentDir($packagePath);
        $this->container->fileExists($packagePath);
        $hookExtensionServicesPhp = dirname(__DIR__, 5) . '/HookExtension/t3ba_hook/Configuration/Services.php';
        
        try {
            $callback(
                $class, $context,
                new ContainerConfigurator($this->container, $this, $this->instanceof, $packagePath, $hookExtensionServicesPhp),
                $this->container);
        } finally {
            $this->instanceof = [];
            $this->registerAliasesForSinglyImplementedInterfaces();
        }
    }
    
    /**
     * @inheritDoc
     */
    public function supports($resource, string $type = null): bool
    {
        return $resource instanceof Closure;
    }
    
}
