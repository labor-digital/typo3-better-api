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
 * Last modified: 2021.04.29 at 22:13
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\Core\Di\CompilerPass\CacheConfigurationPass;
use LaborDigital\T3BA\Core\Di\CompilerPass\EventBusListenerProviderPass;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Core\Di\ServiceFactory;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3BA\Core\VarFs\VarFs;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\Interfaces\ExtConfigApplierInterface;
use LaborDigital\T3BA\ExtConfig\Loader\DiLoader;
use LaborDigital\T3BA\ExtConfig\Loader\MainLoader;
use LaborDigital\T3BA\ExtConfigHandler\Di\ConfigureDiInterface;
use LaborDigital\T3BA\ExtConfigHandler\Di\DiAutoConfigTrait;
use LaborDigital\T3BA\ExtConfigHandler\Di\DiCommonConfigTrait;
use LaborDigital\T3BA\Tool\Cache\CacheConsumerInterface;
use LaborDigital\T3BA\Tool\Cache\Implementation\FrontendCache;
use LaborDigital\T3BA\Tool\Cache\Implementation\PageCache;
use LaborDigital\T3BA\Tool\Cache\Implementation\SystemCache;
use LaborDigital\T3BA\Tool\Cache\KeyGenerator\EnvironmentCacheKeyEnhancerInterface;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
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

class Di implements ConfigureDiInterface
{
    use DiAutoConfigTrait;
    use DiCommonConfigTrait;

    /**
     * @inheritDoc
     */
    public static function configure(
        ContainerConfigurator $configurator,
        ContainerBuilder $containerBuilder,
        ExtConfigContext $context
    ): void {
        static::autoWire([
            'Classes/Core/{Adapter,BootStage,CodeGeneration,DependencyInjection,Override,VarFs,Event}',
            'Classes/ExtConfig/ExtConfigService.php',
            'Classes/Tool/Cache/off',
            'Classes/**/functions.php',
        ]);

        // CUSTOM COMPILER PASSES
        $containerBuilder->addCompilerPass(new EventBusListenerProviderPass(), PassConfig::TYPE_OPTIMIZE, -500);

        // CACHE PROVIDER
        $containerBuilder->registerForAutoconfiguration(EnvironmentCacheKeyEnhancerInterface::class)
                         ->addTag('t3ba.cacheKeyEnhancer');
        $containerBuilder->registerForAutoconfiguration(CacheConsumerInterface::class)
                         ->addTag('t3ba.cacheConsumer');
        $containerBuilder->getDefinition(SystemCache::class)
                         ->addTag('t3ba.cache', ['identifier' => 'system', 'cacheIdentifier' => 't3ba_system']);
        $containerBuilder->getDefinition(FrontendCache::class)
                         ->addTag('t3ba.cache', ['identifier' => 'frontend', 'cacheIdentifier' => 't3ba_frontend']);
        $containerBuilder->getDefinition(PageCache::class)
                         ->addTag('t3ba.cache', [
                             'identifier'      => 'page,pageBased,pageAware',
                             'cacheIdentifier' => 't3ba_frontend',
                         ]);
        $containerBuilder->addCompilerPass(new CacheConfigurationPass());

        // PUBLIC EVENT SUBSCRIBER
        $containerBuilder->registerForAutoconfiguration(ExtConfigApplierInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(LazyEventSubscriberInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('t3ba.public');

        // PUBLIC SERVICE INTERFACE
        $containerBuilder->registerForAutoconfiguration(PublicServiceInterface::class)->addTag('t3ba.public');
        $containerBuilder->addCompilerPass(new PublicServicePass('t3ba.public'));

        // ALIASES
        $containerBuilder->setAlias(EventBusInterface::class, TypoEventBus::class)->setPublic(true);

        // FACTORIES
        foreach (
            [
                MainLoader::class       => [ServiceFactory::class, 'getMainExtConfigLoader'],
                DiLoader::class         => [ServiceFactory::class, 'getDiConfigLoader'],
                ExtConfigContext::class => [ServiceFactory::class, 'getExtConfigContext'],
            ] as $id => $factory
        ) {
            $containerBuilder->findDefinition($id)->setFactory($factory);
        }

        // CACHES
        static::registerCache($configurator, 't3ba_system');
        static::registerCache($configurator, 't3ba_frontend');

        // SYNTHETICS
        foreach (
            [
                ExtConfigService::class,
                ConfigState::class,
                VarFs::class,
                TypoContext::class,
                TypoListenerProvider::class,
            ] as $service
        ) {
            $containerBuilder->removeDefinition($service);
            $containerBuilder->setDefinition($service, new Definition($service))->setPublic(true)->setSynthetic(true);
        }
    }

    /**
     * @inheritDoc
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void
    {
    }

}
