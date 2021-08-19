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


namespace LaborDigital\T3ba\Configuration\ExtConfig;


use LaborDigital\T3ba\Core\Di\CommonServices;
use LaborDigital\T3ba\Core\Di\CompilerPass\CacheConfigurationPass;
use LaborDigital\T3ba\Core\Di\CompilerPass\EventBusListenerProviderPass;
use LaborDigital\T3ba\Core\Di\CompilerPass\NoDiPass;
use LaborDigital\T3ba\Core\Di\CompilerPass\NonSharedServicePass;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Core\Di\NonSharedServiceInterface;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Core\Di\ServiceFactory;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\EventBus\TypoListenerProvider;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtConfigApplierInterface;
use LaborDigital\T3ba\ExtConfig\Loader\DiLoader;
use LaborDigital\T3ba\ExtConfig\Loader\MainLoader;
use LaborDigital\T3ba\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\Di\ConfigureDiInterface;
use LaborDigital\T3ba\ExtConfigHandler\Di\DiAutoConfigTrait;
use LaborDigital\T3ba\ExtConfigHandler\Di\DiCommonConfigTrait;
use LaborDigital\T3ba\Tool\Cache\CacheConsumerInterface;
use LaborDigital\T3ba\Tool\Cache\Implementation\FrontendCache;
use LaborDigital\T3ba\Tool\Cache\Implementation\PageCache;
use LaborDigital\T3ba\Tool\Cache\Implementation\SystemCache;
use LaborDigital\T3ba\Tool\Cache\KeyGenerator\EnvironmentCacheKeyEnhancerInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
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
    ): void
    {
        static::autoWire([
            'Classes/Core/Adapter',
            'Classes/Core/BootStage',
            'Classes/Core/CodeGeneration',
            'Classes/Core/Di',
            'Classes/Core/Exception',
            'Classes/Core/Kint',
            'Classes/Core/Override',
            'Classes/Core/VarFs',
            'Classes/Core/Util',
            'Classes/Core/Kernel.php',
            'Classes/Event',
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
                             'identifier' => 'page,pageBased,pageAware',
                             'cacheIdentifier' => 't3ba_frontend',
                         ]);
        $containerBuilder->addCompilerPass(new CacheConfigurationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 50);
        
        // CACHES
        static::registerCache($configurator, 't3ba_system');
        static::registerCache($configurator, 't3ba_frontend');
        
        // PUBLIC EVENT SUBSCRIBER
        $containerBuilder->registerForAutoconfiguration(ExtConfigApplierInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(LazyEventSubscriberInterface::class)->addTag('t3ba.public');
        $containerBuilder->registerForAutoconfiguration(EventSubscriberInterface::class)->addTag('t3ba.public');
        
        // PUBLIC SERVICE INTERFACE
        $containerBuilder->registerForAutoconfiguration(PublicServiceInterface::class)->addTag('t3ba.public');
        $containerBuilder->addCompilerPass(new PublicServicePass('t3ba.public'));
        
        // NON SHARED SERVICE INTERFACE
        $containerBuilder->registerForAutoconfiguration(NonSharedServiceInterface::class)->addTag('t3ba.nonShared');
        $containerBuilder->addCompilerPass(new NonSharedServicePass());
        
        // NO DI INTERFACE
        $containerBuilder->registerForAutoconfiguration(NoDiInterface::class)->addTag('t3ba.noDi');
        $containerBuilder->addCompilerPass(new NoDiPass());
        
        // ALIASES
        $containerBuilder->setAlias(EventBusInterface::class, TypoEventBus::class)->setPublic(true);
        
        // FACTORIES
        foreach (
            [
                MainLoader::class => [ServiceFactory::class, 'getMainExtConfigLoader'],
                DiLoader::class => [ServiceFactory::class, 'getDiConfigLoader'],
            ] as $id => $factory
        ) {
            $containerBuilder->findDefinition($id)->setFactory($factory);
        }
        
        // SYNTHETICS
        foreach (
            [
                ExtConfigService::class,
                ExtConfigContext::class,
                SiteConfigContext::class,
                ConfigState::class,
                VarFs::class,
                TypoContext::class,
                TypoListenerProvider::class,
            ] as $service
        ) {
            $containerBuilder->removeDefinition($service);
            $containerBuilder->setDefinition($service, new Definition($service))->setPublic(true)->setSynthetic(true);
        }
        
        // FORCED SERVICES
        $configurator->services()->set(CommonServices::class)->public()->autoconfigure()->autowire();
    }
    
    /**
     * @inheritDoc
     */
    public static function configureRuntime(Container $container, ExtConfigContext $context): void
    {
    }
    
}
