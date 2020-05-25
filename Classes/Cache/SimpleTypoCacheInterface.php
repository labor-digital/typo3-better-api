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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\Cache;

use Psr\SimpleCache\CacheInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

interface SimpleTypoCacheInterface extends CacheInterface
{
    
    /**
     * Is a helper to generate a cache key based on the given arguments.
     * If no arguments are given, a cache key will be automatically calculated from the current state of the controller
     *
     * @param array ...$args
     *
     * @return string
     */
    public function makeCacheKey(...$args): string;
    
    /**
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param array                  $tags  A list of tags that should be linked to this entry
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return mixed
     */
    public function setWithTags($key, $value, array $tags, $ttl = null);
    
    /**
     * This method can be used to retrieve / initialize the current cache"s caching framework adapter,
     * because all our caching classes are merely facades for the caching framework.
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     * @throws \LaborDigital\Typo3BetterApi\Cache\CacheException
     */
    public function getTypoCache(): FrontendInterface;
    
    /**
     * Wipes clean the entire cache's keys, that are linked to one or multiple tags.
     *
     * @param array $tags
     *
     * @return bool True on success and false on failure.
     */
    public function clearTags(array $tags);
}
