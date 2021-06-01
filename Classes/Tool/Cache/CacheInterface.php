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
 * Last modified: 2021.05.31 at 21:33
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Cache;

use LaborDigital\T3ba\Tool\Cache\KeyGenerator\CacheKeyGeneratorInterface;
use TYPO3\CMS\Core\Cache\Backend\BackendInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

interface CacheInterface extends \Psr\SimpleCache\CacheInterface
{
    
    /**
     * Generates a cache key, the same way remember() does.
     *
     * @param   array|CacheKeyGeneratorInterface  $keyArgsOrGenerator  Either a cache key generator, or a list of
     *                                                                 arguments that should be converted into a key
     * @param   bool                              $withEnvironment     By default the environment will be taken into
     *                                                                 account. If you set this to false, only the key
     *                                                                 generator is used.
     *
     * @return string
     */
    public function getCacheKey($keyArgsOrGenerator, ?bool $withEnvironment = null): string;
    
    /**
     * Removes all cache entries of this cache which are tagged by the specified tag.
     *
     * @param   string  $tag  The tag the entries must have
     */
    public function flushByTag($tag): bool;
    
    /**
     * Removes all cache entries of this cache which are tagged by any of the specified tags.
     *
     * @param   string[]  $tags  List of tags
     */
    public function flushByTags(array $tags): bool;
    
    /**
     * Returns the backend used by this cache
     *
     * @return \TYPO3\CMS\Core\Cache\Backend\BackendInterface The backend used by this cache
     */
    public function getBackend(): BackendInterface;
    
    /**
     * Returns the TYPO3 cache object that gets wrapped by this instance
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    public function getFrontend(): FrontendInterface;
    
    /**
     * Returns this cache's identifier
     *
     * @return string The identifier for this cache
     */
    public function getIdentifier(): string;
    
    /**
     * Removes all cache entries of this cache.
     */
    public function flush(): bool;
    
    /**
     * Checks the validity of a tag. Returns TRUE if it's valid.
     *
     * @param   string  $tag  A tag to be checked for validity
     *
     * @return bool
     */
    public function isValidTag($tag): bool;
    
    /**
     * Helper to make sure the cache key is no longer than 128 characters
     *
     * @param   mixed  $entryIdentifier
     *
     * @return string|mixed
     */
    public function prepareIdentifier($entryIdentifier);
    
    /**
     * The given $callback is called once and then cached. All subsequent calls
     * will then first try to serve the cached value instead of calling $callback again.
     *
     * The execution of remember() can be nested in order to build cached data trees.
     * This also means that outer executions will inherit the cache options like ttl, tags and "enabled" state
     * from the inner executions.
     *
     * @param   callable    $callback  The callable to generate the value to be cached
     * @param   array|null  $keyArgs   Allows you to pass key arguments to generate the cache key with
     *                                 You can omit this parameter if you are supplying your own keyGenerator
     *                                 implementation in the options
     * @param   array       $options   Additional options
     *                                 - lifetime int|callable: The numeric value in seconds for how long the cache entry
     *                                 should be stored. Can be a callable which receives the $callback result,
     *                                 to create a ttl based on the output. Is inherited to outer scopes.
     *                                 - enabled bool|callable (true): Allows you to dynamically disable the cache
     *                                 for this execution. Can be a callable which receives the $callback result,
     *                                 to enable/disable the cache based on the output. Is inherited to outer scopes.
     *                                 - keyGenerator CacheKeyGeneratorInterface: The generator instance
     *                                 which is used to generate a cache key for this entry.
     *                                 - useEnvironment bool: Determines if the environment should be taken into account
     *                                 when a cache key is generated. The "environment" is the current language,
     *                                 the current site(not page!), user groups, mount point or page types.
     *                                 The flag is automatically set depending by your implementation.
     *                                 - tags array: A list of tags that should be added to this cache entry.
     *                                 The tags will be inherited to outer scopes.
     *                                 - onFreeze callable: A callback to execute before the result of $callback is
     *                                 written into the cache. Allows you to perform additional post processing on the
     *                                 fly. The callback receives the result as parameter.
     *                                 - onWarmup callable: A callback to execute when the cached value is read from
     *                                 the caching system. Allows you to rehydrate objects on the fly. The callback
     *                                 receives the value as parameter.
     *
     * @return false|mixed
     */
    public function remember(callable $callback, ?array $keyArgs = null, array $options = []);
    
    /**+
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param   string          $entryIdentifier          The key of the item to store.
     * @param   mixed           $data                     The value of the item to store, must be serializable.
     * @param   array|int|null  $tagsOrLifetime           Either an array of tags to set for the cache entry, or the $lifetime as numeric value
     * @param   int|null        $lifetime                 Optional. The TTL value of this item. If no value is sent and
     *                                                    the driver supports TTL then the library may set a default value
     *                                                    for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($entryIdentifier, $data, $tagsOrLifetime = null, $lifetime = null): bool;
}
