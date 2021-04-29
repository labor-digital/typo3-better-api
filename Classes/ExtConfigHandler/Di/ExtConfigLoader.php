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

namespace LaborDigital\T3BA\ExtConfigHandler\Di;


use Closure;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ExtConfigLoader extends PhpFileLoader
{
    
    // todo this has changed since I implemented it, what does it do, and how can we fix it reliably?
    /**
     * @inheritDoc
     *
     * Symfony is not compatible with itself here -.- So we have to disable the inspections
     *
     * @noinspection PhpHierarchyChecksInspection
     * @noinspection PhpSignatureMismatchDuringInheritanceInspection
     */
    public function load($callback, $path = null)
    {
        $this->setCurrentDir($path);
        $this->container->fileExists($path);
        $hookExtensionServicesPhp = dirname(__DIR__, 5) . '/HookExtension/T3BA_hook/Configuration/Services.php';
        
        try {
            $callback(
                new ContainerConfigurator($this->container, $this, $this->instanceof, $path, $hookExtensionServicesPhp),
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
