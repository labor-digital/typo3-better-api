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
 * Last modified: 2020.03.19 at 13:05
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Class CacheClearedEvent
 *
 * Dispatched when the caches are cleared
 * There are multiple variants of this event, basically one for each vanilla cache group, they are called in
 * addition to the main event, which is called every time the cache is cleared.
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class CacheClearedEvent
{
    /**
     * The method that lead to the cache flushing
     *
     * @var string
     */
    protected $method;

    /**
     * The group that should be flushed
     *
     * @var string|null
     */
    protected $group;

    /**
     * The list of tags that should be flushed in the group
     *
     * @var array|null
     */
    protected $tags;

    /**
     * The tag that should be flushed in the group
     *
     * @var string|null
     * @deprecated will be removed in v10 -> use $tags instead
     */
    protected $tag;

    /**
     * The cache manager instance
     *
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;

    /**
     * CacheClearedEvent constructor.
     *
     * @param   string                              $method
     * @param   string|null                         $group
     * @param   array|null                          $tags
     * @param   \TYPO3\CMS\Core\Cache\CacheManager  $cacheManager
     */
    public function __construct(string $method, ?string $group, ?array $tags, CacheManager $cacheManager)
    {
        $this->method       = $method;
        $this->group        = $group;
        $this->tags         = $tags;
        $this->tag          = is_array($tags) ? reset($tags) : null;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Returns the method that lead to the cache flushing
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Returns the group that should be flushed
     *
     * @return string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * Returns the tag that should be flushed in the group
     *
     * @return string|null
     * @deprecated will be removed in v10 -> use getTags instead
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Returns the list of tags that should be flushed in the group
     *
     * @return array|null
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * Returns the cache manager instance
     *
     * @return \TYPO3\CMS\Core\Cache\CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }
}
