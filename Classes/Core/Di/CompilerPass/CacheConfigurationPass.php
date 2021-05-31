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
 * Last modified: 2021.05.31 at 20:34
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Di\CompilerPass;


use LaborDigital\T3ba\EventHandler\CacheClearing;
use LaborDigital\T3ba\Tool\Cache\CacheFactory;
use LaborDigital\T3ba\Tool\Cache\CacheInterface;
use LaborDigital\T3ba\Tool\Cache\Implementation\GenericCache;
use LaborDigital\T3ba\Tool\Cache\KeyGenerator\EnvironmentCacheKeyGenerator;
use LaborDigital\T3ba\Tool\OddsAndEnds\ReflectionUtil;
use Neunerlei\Arrays\Arrays;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

class CacheConfigurationPass implements CompilerPassInterface
{
    
    protected const TRACKED_INTERFACES
        = [
            CacheInterface::class,
            \Psr\SimpleCache\CacheInterface::class,
            FrontendInterface::class,
        ];
    
    /**
     * Holds the ids of the already generated cache provider services.
     *
     * @var array
     */
    protected $generatedCacheServices = [];
    
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $definitions = $this->getImplementationDefinitions($container);
        
        $this->autoWireCacheArguments($container, $definitions);
        
        $container->getDefinition(CacheFactory::class)
                  ->setArgument('$implementations', array_keys($definitions));
        
        $container->getDefinition(CacheClearing::class)
                  ->setArgument('$cacheIdentifiers', array_unique(Arrays::getList($definitions, 'cache')));
        
        $this->configureEnvironmentCacheKeyEnhancers($container);
    }
    
    /**
     * Extracts and prepares the list of classes that have been tagged using the "t3ba.cache" tag.
     * Those classes are static implementations which provide additional features above the normal
     * cache implementations.
     *
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder  $container
     *
     * @return array
     */
    protected function getImplementationDefinitions(ContainerBuilder $container): array
    {
        $definitions = [];
        foreach ($container->findTaggedServiceIds('t3ba.cache') as $serviceId => $serviceConfig) {
            foreach ($serviceConfig as $config) {
                if (empty($config['identifier'])) {
                    throw new RuntimeException(
                        'The service ' . $serviceId
                        . ' is tagged as t3ba.cache, but does not have an "identifier" configured');
                }
                
                if (is_string($config['identifier'])) {
                    $config['identifier'] = array_map('trim', explode(',', $config['identifier']));
                }
                if (! is_array($config['identifier'])) {
                    throw new RuntimeException(
                        'The service ' . $serviceId
                        . ' has an invalid "identifier" configured, only strings and arrays of strings are allowed');
                }
                
                if (empty($config['cacheIdentifier']) || ! is_string($config['cacheIdentifier'])) {
                    $config['cacheIdentifier'] = null;
                }
                
                foreach ($config['identifier'] as $identifier) {
                    $definitions[$identifier] = [
                        'service' => $serviceId,
                        'cache' => $config['cacheIdentifier'],
                    ];
                }
            }
        }
        
        return $definitions;
    }
    
    /**
     * Iterates all services that have been tagged as "t3ba.cacheConsumer", scans the constructor,
     * and "inject" methods and automatically generates the auto-wiring to the inflected cache instance.
     *
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder  $container
     * @param   array                                                    $definitions
     */
    protected function autoWireCacheArguments(ContainerBuilder $container, array $definitions): void
    {
        foreach ($container->findTaggedServiceIds('t3ba.cacheConsumer') as $serviceId => $_) {
            $ref = $container->getReflectionClass($serviceId);
            if (! $ref) {
                continue;
            }
            
            $def = $container->getDefinition($serviceId);
            
            if ($ref->getConstructor()) {
                /** @noinspection NullPointerExceptionInspection */
                $this->autoWireSingleMethod($container, $def, $ref->getConstructor(), $definitions);
            }
            
            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if (stripos($method->getName(), 'inject') === 0) {
                    $this->autoWireSingleMethod($container, $def, $method, $definitions);
                }
            }
        }
    }
    
    /**
     * Handles the auto wiring of a single method in autoWireCacheArguments()
     *
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder  $container
     * @param   \Symfony\Component\DependencyInjection\Definition        $definition
     * @param   \ReflectionMethod                                        $reflectionMethod
     * @param   array                                                    $definitions
     */
    protected function autoWireSingleMethod(
        ContainerBuilder $container,
        Definition $definition,
        ReflectionMethod $reflectionMethod,
        array $definitions
    ): void
    {
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $types = ReflectionUtil::parseType($parameter);
            
            if (empty(array_intersect($types, static::TRACKED_INTERFACES))) {
                continue;
            }
            
            $identifier = preg_replace('~(Cache)$~', '', lcfirst($parameter->getName()));
            
            $providerServiceId = $this->getProviderServiceId($container, $identifier, $definitions);
            
            $definition->setArgument(
                '$' . $parameter->getName(),
                new Reference($providerServiceId)
            );
        }
    }
    
    /**
     * Calculates the cache provider, service id and if its a new one,
     * automatically registers it into the service container
     *
     * @param   ContainerBuilder  $container
     * @param   string            $identifier
     * @param   array             $definitions
     *
     * @return string
     */
    protected function getProviderServiceId(
        ContainerBuilder $container,
        string $identifier,
        array $definitions
    ): string
    {
        $cacheIdentifier = $identifier;
        $serviceName = GenericCache::class;
        
        if (isset($definitions[$identifier])) {
            $serviceName = $definitions[$identifier]['service'];
            $cacheIdentifier = $definitions[$identifier]['cache'];
        }
        
        $className = $container->getDefinition($serviceName)->getClass();
        if (! is_string($className)) {
            throw new \RuntimeException('Cache provider for identifier ' . $identifier
                                        . ' could not be created, because it could not be resolved into a class name');
        }
        
        $cacheServiceId = 't3ba_cache_' . md5($serviceName . '.' . $cacheIdentifier);
        
        if (! isset($this->generatedCacheServices[$cacheServiceId])) {
            $def = new Definition(CacheInterface::class);
            $def->setFactory([new Reference(CacheFactory::class), 'makeCacheImplementation']);
            $def->setArguments([$className, $cacheIdentifier]);
            $container->setDefinition($cacheServiceId, $def);
        }
        
        return $cacheServiceId;
    }
    
    /**
     * Configures the environment cache key generator by providing the list of all registered cache key enhancer
     * instances to it
     *
     * @param   \Symfony\Component\DependencyInjection\ContainerBuilder  $container
     */
    protected function configureEnvironmentCacheKeyEnhancers(ContainerBuilder $container): void
    {
        $enhancers = [];
        foreach ($container->findTaggedServiceIds('t3ba.cacheKeyEnhancer') as $serviceId => $_) {
            $enhancers = new Reference($serviceId);
        }
        
        $container->getDefinition(EnvironmentCacheKeyGenerator::class)
                  ->setArgument('$enhancers', $enhancers);
    }
}
