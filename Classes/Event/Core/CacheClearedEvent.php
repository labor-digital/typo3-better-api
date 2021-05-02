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

namespace LaborDigital\T3ba\Event\Core;

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Class CacheClearedEvent
 *
 * Dispatched when the caches are cleared
 * There are multiple variants of this event, basically one for each vanilla cache group, they are called in
 * addition to the main event, which is called every time the cache is cleared.
 *
 * @package LaborDigital\T3ba\Event\Core
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
     * The tags that should be flushed
     *
     * @var array
     */
    protected $tags;
    
    /**
     * The cache manager instance
     *
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    /**
     * CacheClearedEvent constructor.
     *
     * @param   string        $method
     * @param   string|null   $group
     * @param   array         $tags
     * @param   CacheManager  $cacheManager
     */
    public function __construct(string $method, ?string $group, array $tags, CacheManager $cacheManager)
    {
        $this->method = $method;
        $this->group = $group;
        $this->tags = $tags;
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
     * Returns the tags that should be flushed
     *
     * @return array
     */
    public function getTags(): array
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
