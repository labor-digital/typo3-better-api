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


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\Interfaces\DiBuildTimeHandlerInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class BuildTimeHandler extends AbstractExtConfigHandler implements DiBuildTimeHandlerInterface, NoDiInterface
{
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureDiInterface::class);
    }
    
    /**
     * Handles the container builder configuration
     *
     * @param   string  $class
     */
    public function handle(string $class): void
    {
        $packagePath = $this->context->getPackage()->getPackagePath();
        
        $loader = new ExtConfigLoader(
            $this->getInstance(ContainerBuilder::class),
            new FileLocator($packagePath)
        );
        
        $loader->runExtConfigLoad($class, $this->context, $packagePath, static function (
            string $class,
            ExtConfigContext $context,
            ContainerConfigurator $configurator,
            ContainerBuilder $containerBuilder
        ) {
            // Apply the default configuration
            if (method_exists($class, 'setAutoWiringDependencies')) {
                call_user_func([$class, 'setAutoWiringDependencies'], $configurator, $context);
            }
            
            // Call the real configuration class
            call_user_func([$class, 'configure'], $configurator, $containerBuilder, $context);
        });
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void { }
    
    /**
     * @inheritDoc
     */
    public function finish(): void { }
}
