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
 * Last modified: 2020.08.23 at 16:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\DependencyInjection;

use LaborDigital\T3BA\ExtConfig\AbstractExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\StandAloneHandlerInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class ConfigureDependencyInjectionHandler extends AbstractExtConfigHandler implements StandAloneHandlerInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $containerBuilder;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * Configures the handler to process the container builder configuration
     *
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder  $containerBuilder
     *
     * @return $this
     */
    public function configureForContainerBuilder(ContainerBuilder $containerBuilder): self
    {
        $this->containerBuilder = $containerBuilder;

        return $this;
    }

    /**
     * Configures the handler to process the runtime container configuration
     *
     * @param   \Symfony\Component\DependencyInjection\Container  $container
     *
     * @return $this
     */
    public function configureRuntimeContainer(Container $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureDependencyInjectionInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        if (isset($this->containerBuilder)) {
            $this->handleContainerBuilder($class);
        } else {
            $this->handleRuntimeContainer($class);
        }
    }

    /**
     * Handles the container builder configuration
     *
     * @param   string  $class
     */
    protected function handleContainerBuilder(string $class): void
    {
        $packagePath = $this->context->getPackage()->getPackagePath();
        $context     = $this->context;
        $loader      = new ExtConfigLoader($this->containerBuilder, new FileLocator($packagePath));

        $loader->load(static function (
            ContainerConfigurator $configurator,
            ContainerBuilder $containerBuilder
        ) use ($class, $context) {
            // Apply the default configuration, if the
            if (method_exists($class, 'setAutoWiringDependencies')) {
                call_user_func([$class, 'setAutoWiringDependencies'], $configurator, $context);
            }

            // Call the real configuration class
            call_user_func([$class, 'configure'], $configurator, $containerBuilder, $context);

        }, $this->context->getPackage()->getPackagePath());
    }

    /**
     * Handles the runtime container configuration
     *
     * @param   string  $class
     */
    protected function handleRuntimeContainer(string $class): void
    {
        call_user_func([$class, 'configureRuntime'], $this->container, $this->context);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
    }

}
