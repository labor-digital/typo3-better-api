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
 * Last modified: 2020.03.20 at 16:49
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\Temporary;

use TYPO3\CMS\Core\Cache\CacheManager;

/**
 * Class CacheManagerCreatedEvent
 *
 * This event is triggered when the bootstrap creates a new event manager instance.
 * Note, this is an internal, temporary event which will probably be removed in v10
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class CacheManagerCreatedEvent
{
    
    /**
     * The cache manager instance that was created
     *
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    /**
     * True if the caching is disabled
     *
     * @var bool
     */
    protected $disableCaching;
    
    /**
     * CacheManagerCreatedEvent constructor.
     *
     * @param   \TYPO3\CMS\Core\Cache\CacheManager  $cacheManager
     * @param   bool                                $disableCaching
     */
    public function __construct(CacheManager $cacheManager, bool $disableCaching)
    {
        $this->cacheManager   = $cacheManager;
        $this->disableCaching = $disableCaching;
    }
    
    /**
     * Returns the cache manager instance that was created
     *
     * @return \TYPO3\CMS\Core\Cache\CacheManager
     */
    public function getCacheManager(): CacheManager
    {
        return $this->cacheManager;
    }
    
    /**
     * Returns true if the caching is disabled
     *
     * @return bool
     */
    public function isDisableCaching(): bool
    {
        return $this->disableCaching;
    }
}
