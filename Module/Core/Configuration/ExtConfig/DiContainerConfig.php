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
 * Last modified: 2020.08.23 at 17:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\Configuration\ExtConfig;


use LaborDigital\T3BA\Core\DependencyInjection\CompilerPass\EventBusListenerProviderPass;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\Core\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Core\ExtConfigHandler\DependencyInjection\ConfigureDependencyInjectionInterface;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\DependencyInjection\PublicServicePass;

class DiContainerConfig implements ConfigureDependencyInjectionInterface
{

    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void {
        // Enable auto wiring
        $services = $configurator
            ->services()
            ->defaults()
            ->autowire()
            ->autoconfigure();

        // Resolve the classes for the auto wiring
        $basePath = 'Module/Core/Classes/';
        $services
            ->load('LaborDigital\\T3BA\\Core\\', 'Module/Core/Classes/*')
            ->exclude([
                $basePath . '{Adapter,BootStage,CodeGeneration,DependencyInjection,Override,TempFs,ExtConfig}',
            ]);

        // LISTENER PROVIDER
        $containerBuilder->getDefinition(TypoListenerProvider::class)
                         ->setPublic(true)
                         ->setSynthetic(true);
        $containerBuilder->addCompilerPass(new EventBusListenerProviderPass(), PassConfig::TYPE_OPTIMIZE, -500);

        // EVENT SUBSCRIBER
        $containerBuilder->registerForAutoconfiguration(LazyEventSubscriberInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('t3ba.public');

        // PUBLIC SERVICE
        $containerBuilder->registerForAutoconfiguration(PublicServiceInterface::class)->addTag('t3ba.public');
        $containerBuilder->addCompilerPass(new PublicServicePass('t3ba.public'));

        // ALIASES
        $containerBuilder->setAlias(EventBusInterface::class, TypoEventBus::class)->setPublic(true);

    }

    /**
     * @inheritDoc
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void
    {
    }
}
