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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Cache;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Cache\KeyGenerator\EnvironmentCacheKeyGenerator;
use LaborDigital\T3BA\Tool\Cache\Util\CacheManagerAdapter;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Cache\CacheManager;

class CacheFactory
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Cache\CacheInterface[]
     */
    protected $instances = [];
    
    /**
     * A list of implementation names that can be used instead of a cache key
     *
     * @var array
     */
    protected $implementations;
    
    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Cache\KeyGenerator\EnvironmentCacheKeyGenerator
     */
    protected $environmentCacheKeyGenerator;
    
    public function __construct(
        array $implementations,
        CacheManager $cacheManager,
        EnvironmentCacheKeyGenerator $environmentCacheKeyGenerator
    )
    {
        $this->implementations = $implementations;
        $this->cacheManager = $cacheManager;
        $this->environmentCacheKeyGenerator = $environmentCacheKeyGenerator;
    }
    
    /**
     * Internal factory method to create a cache implementation when a CacheConsumerInterface child
     * requires a CacheInterface
     *
     * @param   string  $class       The name of the implementation class that should be created.
     *                               The class must extend the AbstractExtendedCache class
     * @param   string  $identifier  The TYPO3 cache identifier that should be injected into the implementaion class
     *
     * @return \LaborDigital\T3BA\Tool\Cache\CacheInterface
     */
    public function makeCacheImplementation(string $class, string $identifier): CacheInterface
    {
        $key = $class . '-' . $identifier;
        if (isset($this->instances[$key])) {
            return $this->instances[$key];
        }
        
        if (! $this->cacheManager->hasCache($identifier)) {
            $identifiers = CacheManagerAdapter::getAllCacheIdentifiers($this->cacheManager);
            $identifierNormalized = Inflector::toCamelBack($identifier);
            
            if (! $this->cacheManager->hasCache($identifierNormalized)) {
                $identifiersNormalized = array_map(static function (string $identifier): string {
                    $normalized = Inflector::toCamelBack($identifier);
                    if ($normalized === $identifier) {
                        return $identifier;
                    }
                    
                    return $identifier . ' (or ' . $normalized . ')';
                }, $identifiers);
                
                throw new InvalidArgumentException(
                    'The given $identifier "' . $identifier
                    . '" is not registered as a TYPO3 cache. You can use one of those: '
                    . implode(', ', $identifiersNormalized)
                    . '. Alternatively those values are registered as static implementations: '
                    . implode(', ', $this->implementations)
                    . '. Use one of those values as variable name in your method definition, to map a specific cache.');
            }
            
            $identifier = $identifierNormalized;
            
            $key = $class . '-' . $identifier;
            if (isset($this->instances[$key])) {
                return $this->instances[$key];
            }
        }
        
        return $this->instances[$key] = $this->makeInstance(
            $class, [$this->cacheManager->getCache($identifier), $this->environmentCacheKeyGenerator]
        );
    }
}
