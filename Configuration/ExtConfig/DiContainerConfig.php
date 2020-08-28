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
 * Last modified: 2020.08.24 at 20:25
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\Core\DependencyInjection\CompilerPass\EventBusListenerProviderPass;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\ExtConfig\ExtConfigApplierInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfigHandler\DependencyInjection\ConfigureDependencyInjectionInterface;
use LaborDigital\T3BA\ExtConfigHandler\DependencyInjection\ConfigureDependencyInjectionTrait;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\EventBus\EventBusInterface;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\DependencyInjection\PublicServicePass;

class DiContainerConfig implements ConfigureDependencyInjectionInterface
{
    use ConfigureDependencyInjectionTrait;

    /**
     * @inheritDoc
     */
    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void {
        static::autoWire([
            'Classes/Core/{Adapter,BootStage,CodeGeneration,DependencyInjection,Override,TempFs}',
            'Classes/ExtConfig/ExtConfigService.php',
            'Classes/**/functions.php',
        ]);

        // LISTENER PROVIDER
        $containerBuilder->getDefinition(TypoListenerProvider::class)
                         ->setPublic(true)
                         ->setSynthetic(true);
        $containerBuilder->addCompilerPass(new EventBusListenerProviderPass(), PassConfig::TYPE_OPTIMIZE, -500);

        // PUBLIC EVENT SUBSCRIBER
        $containerBuilder->registerForAutoconfiguration(ExtConfigApplierInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(LazyEventSubscriberInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('t3ba.public');

        // PUBLIC SERVICE INTERFACE
        $containerBuilder->registerForAutoconfiguration(PublicServiceInterface::class)->addTag('t3ba.public');
        $containerBuilder->addCompilerPass(new PublicServicePass('t3ba.public'));

        // ALIASES
        $containerBuilder->setAlias(EventBusInterface::class, TypoEventBus::class)->setPublic(true);

        // SERVICES
        $containerBuilder->findDefinition(ExtConfigContext::class)
                         ->setPublic(true)->setSynthetic(true);
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
