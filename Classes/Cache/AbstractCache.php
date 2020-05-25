<?php
/**
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
 * Last modified: 2020.03.19 at 02:37
 */

namespace LaborDigital\Typo3BetterApi\Cache;

use Closure;
use DateInterval;
use LaborDigital\Typo3BetterApi\Tsfe\TsfeService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use SplStack;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;

abstract class AbstractCache implements SingletonInterface, SimpleTypoCacheInterface
{
    
    /**
     * THIS HAS TO BE SET to the key of the matching cache configuration.
     * If this is empty, the cache generation will fail.
     *
     * @var string
     */
    protected $cacheConfigKey;
    
    /**
     * A list of array keys set in the current cycle.
     * Those keys will be set when the current cycle set a value, so they are allowed to return their
     * values even if the cache is disabled.
     *
     * @var array
     */
    protected static $allowedCacheKeys = [];
    
    /**
     * Global holder for the cache frontend
     * Always use getTypoCache() instead of this property!
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    private $cache;
    
    /**
     * Contains either true or false once it was determined if the cache was enabled or not.
     * -> This can also be used for the logic in isCachedEnabled() like HTTP_CACHE_CONTROL and similar rules.
     * @var bool|null
     */
    protected $cacheEnabled;
    
    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    /**
     * @var TsfeService
     */
    protected $tsfe;
    
    /**
     * @var TypoContext
     */
    protected $typoContext;
    
    /**
     * Typo3 dependency injector
     *
     * @param \TYPO3\CMS\Core\Cache\CacheManager $cacheManager
     */
    public function injectCacheManager(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }
    
    /**
     * Typo3 dependency injector
     *
     * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext $typoContext
     */
    public function injectTypoContext(TypoContext $typoContext)
    {
        $this->typoContext = $typoContext;
    }
    
    /**
     * Typo3 dependency injector
     *
     * @param \LaborDigital\Typo3BetterApi\Tsfe\TsfeService $tsfe
     */
    public function injectTsfe(TsfeService $tsfe)
    {
        $this->tsfe = $tsfe;
    }
    
    /**
     * Wipes clean the entire cache"s keys.
     *
     * @return bool True on success and false on failure.
     * @throws NoSuchCacheException
     * @throws \InvalidArgumentException
     */
    public function clear()
    {
        $this->getTypoCache()->flush();
        return true;
    }
    
    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as
     *                  value.
     *
     * @throws \LaborDigital\Typo3BetterApi\Tsfe\TsfeNotLoadedException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function getMultiple($keys, $default = null)
    {
        $result = new SplStack();
        foreach ($keys as $key) {
            $result->add($key, $this->get($key, $default));
        }
        return $result;
    }
    
    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     * @throws \LaborDigital\Typo3BetterApi\Tsfe\TsfeNotLoadedException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function get($key, $default = null)
    {
        // Prepare key
        $key = $this->prepareKey($key);
        // Check if the cache is disabled
        if (!$this->isCacheEnabled()) {
            // Check if the key is allowed even without cache
            if (!Arrays::hasPath(static::$allowedCacheKeys, [$this->cacheConfigKey, $key])) {
                return $default;
            }
        }
        
        // Validate existence
        if ($this->getTypoCache()->has($key) === false) {
            return $default;
        }
        
        // Extract value
        return $this->getTypoCache()->get($key);
    }
    
    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     * @throws NoSuchCacheException
     * @throws \InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null)
    {
        $result = true;
        foreach ($values as $k => $v) {
            $result = $this->setInternal($k, $v, $ttl) && $result;
        }
        return $result;
    }
    
    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     * @throws NoSuchCacheException
     * @throws \InvalidArgumentException
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->setInternal($key, $value, $ttl);
    }
    
    /**
     * @inheritDoc
     */
    public function setWithTags($key, $value, array $tags, $ttl = null)
    {
        return $this->setInternal($key, $value, $ttl, $tags);
    }
    
    /**
     * @inheritDoc
     */
    public function clearTags(array $tags)
    {
        $this->getTypoCache()->flushByTags($tags);
        return true;
    }
    
    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws NoSuchCacheException
     * @throws \InvalidArgumentException
     */
    public function deleteMultiple($keys)
    {
        $result = true;
        foreach ($keys as $key) {
            $result = $this->delete($key) && $result;
        }
        return $result;
    }
    
    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws NoSuchCacheException
     * @throws \InvalidArgumentException
     */
    public function delete($key)
    {
        $this->getTypoCache()->remove($this->prepareKey($key));
        return true;
    }
    
    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \LaborDigital\Typo3BetterApi\Tsfe\TsfeNotLoadedException
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    public function has($key)
    {
        // Prepare key
        $key = $this->prepareKey($key);
        
        // Check if the cache is disabled
        if (!$this->isCacheEnabled()) {
            // Check if the key is allowed even without cache
            if (!Arrays::hasPath(static::$allowedCacheKeys, [$this->cacheConfigKey, $key])) {
                return false;
            }
        }
        
        // Done
        return $this->getTypoCache()->has($key);
    }
    
    /**
     * @inheritDoc
     */
    public function makeCacheKey(...$args): string
    {
        // Prepare caller based cache prefix
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $prefix = '';
        if (isset($trace[1])) {
            $action = isset($trace[1]['function']) ? $trace[1]['function'] : 'unknown';
            $prefix = substr(Inflector::toFile(__CLASS__ . ' ' . $action), 0, 100) . '-';
        }
        
        $hasher = function ($list, $hasher) {
            $props = [];
            if (is_iterable($list)) {
                foreach ($list as $k => $v) {
                    $props[$k] = $hasher($v, $hasher);
                }
            } elseif (is_object($list)) {
                if ($list instanceof LazyLoadingProxy) {
                    $list = $list->_loadRealInstance();
                }
                if ($list instanceof AbstractEntity) {
                    $list = $list->_getCleanProperties();
                }
                if (method_exists($list, 'getUid')) {
                    $props[] = get_class($list) . '.' . $list->getUid();
                } else {
                    foreach (get_class_vars($list) as $k => $v) {
                        $props[$k] = $hasher($v, $hasher);
                    }
                    if (empty($props)) {
                        $props[] = get_class($list) . ($list instanceof Closure ? 'closure' : '');
                    }
                }
            }
            if (empty($props)) {
                $props[] = json_encode($list);
            }
            sort($props);
            return hash('sha512', implode('-', $props));
        };
        
        return $prefix . $hasher($args, $hasher);
    }
    
    /**
     * This method can be used to retrieve / initialize the current cache"s caching framework adapter,
     * because all our caching classes are merely facades for the caching framework.
     *
     * Always use this method over $this->cache!
     *
     * @return mixed|\TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     * @throws \LaborDigital\Typo3BetterApi\Cache\CacheException
     */
    public function getTypoCache(): FrontendInterface
    {
        // Deliver directly when the cache is stored...
        if (isset($this->cache)) {
            return $this->cache;
        }
        
        // Validate that we have a key
        if (!is_string($this->cacheConfigKey) || empty($this->cacheConfigKey)) {
            throw new CacheException('The cache implementation: ' . get_called_class() . ' does not specify the required cacheConfigKey property!');
        }
        
        // Check if the cache exists (otherwise we are maybe to early?)
        if (!$this->cacheManager->hasCache($this->cacheConfigKey)) {
            // Check if we can register the cache manually
            $config = Arrays::getPath($GLOBALS, Arrays::mergePaths('TYPO3_CONF_VARS.SYS.caching.cacheConfigurations', [$this->cacheConfigKey]));
            
            // Check if we found cache configurations
            if (!empty($config)) {
                if (method_exists($this->cacheManager, '__injectRawCacheConfig')) {
                    $this->cacheManager->__injectRawCacheConfig($this->cacheConfigKey, $config);
                } else {
                    $this->cacheManager->setCacheConfigurations([$this->cacheConfigKey => $config]);
                }
            }
        }
        
        // Check if the cache is already active and registered
        if ($this->cacheManager->hasCache($this->cacheConfigKey)) {
            return $this->cache = $this->cacheManager->getCache($this->cacheConfigKey);
        }
        
        // Check if the cache class has its own definition
        $config = $this->defineCacheConfiguration();
        if (!empty($config)) {
            if (method_exists($this->cacheManager, '__injectRawCacheConfig')) {
                $this->cacheManager->__injectRawCacheConfig($this->cacheConfigKey, $config);
            } else {
                $this->cacheManager->setCacheConfigurations([$this->cacheConfigKey => $config]);
            }
            if ($this->cacheManager->hasCache($this->cacheConfigKey)) {
                return $this->cache = $this->cacheManager->getCache($this->cacheConfigKey);
            }
        }
        
        // Done with that...
        throw new CacheException('The cache of class ' . get_called_class() . ' could not be created!');
    }
    
    /**
     * Returns true if the cache should be used, or false if we should work without the cache
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\Tsfe\TsfeNotLoadedException
     * @throws \TYPO3\CMS\Core\Error\Http\ServiceUnavailableException
     */
    protected function isCacheEnabled(): bool
    {
        // Check if we tested already
        if (isset($this->cacheEnabled)) {
            return $this->cacheEnabled;
        }
        
        // Check if the cache is disabled
        $isFrontend = $this->typoContext->getEnvAspect()->isFrontend();
        $hasFrontend = $isFrontend && $this->tsfe->hasTsfe();
        $hasBeUser = isset($GLOBALS['BE_USER']);
        $userCanClearCache = $hasBeUser && $GLOBALS['BE_USER']->isAdmin();
        $disabledByFrontend = $userCanClearCache && $hasFrontend && (bool)$this->tsfe->getTsfe()->no_cache;
        $disabledByCacheControl = $userCanClearCache && isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] == 'no-cache';
        $disabledByCachePragma = $userCanClearCache && isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] == 'no-cache';
        
        // Check if cache is active
        return !$disabledByFrontend && !$disabledByCachePragma && !$disabledByCacheControl;
    }
    
    /**
     * Unifies any given key into a filesave format
     *
     * @param mixed $key
     *
     * @return string
     */
    protected function prepareKey($key): string
    {
        // Ignore double encoding
        if (is_string($key) && stripos($key, 'sc-') === 0) {
            return $key;
        }
        
        // Encode key
        $key = Inflector::toFile(serialize($key));
        $key = substr($key, 0, 100) . '-' . md5($key);
        return 'sc-' . $key;
    }
    
    /**
     * Internal set wrapper to allow the setting of tags without conflicting with the interface
     *
     * @param       $key
     * @param       $value
     * @param null  $ttl
     * @param array $tags
     *
     * @return bool
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    protected function setInternal($key, $value, $ttl = null, $tags = [])
    {
        $key = $this->prepareKey($key);
        static::$allowedCacheKeys[$this->cacheConfigKey][$key] = true;
        
        // Make sure we unpack lazy objects
        if (is_object($value)) {
            if ($value instanceof LazyObjectStorage) {
                $value = $value->toArray();
            }
            if ($value instanceof LazyLoadingProxy) {
                $value = $value->_loadRealInstance();
            }
        }
        
        $this->getTypoCache()->set($key, $value, $tags, $this->prepareTtl($ttl));
        return true;
    }
    
    /**
     * Converts any given interval to a number of seconds for the typo3 backends
     *
     * @param int|\DateInterval|null $ttl
     *
     * @return int|null
     */
    protected function prepareTtl($ttl)
    {
        if (is_object($ttl) && $ttl instanceof DateInterval) {
            $ttl = (($ttl->d * 24 * 60) + ($ttl->h * 60) + $ttl->i) * 60 + $ttl->s;
        } elseif (is_numeric($ttl)) {
            $ttl = (int)$ttl;
        } elseif (!is_null($ttl)) {
            $ttl = 0;
        }
        // Done
        return $ttl;
    }
    
    /**
     * Only used to define a hardcoded cache configuration
     * @return array
     * @internal
     */
    protected function defineCacheConfiguration(): array
    {
        return [];
    }
}
