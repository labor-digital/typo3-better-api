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


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\Core\CacheClearedEvent;
use LaborDigital\T3BA\Event\DataHandler\ActionPostProcessorEvent;
use LaborDigital\T3BA\Event\DataHandler\SavePostProcessorEvent;
use LaborDigital\T3BA\Tool\Cache\Util\CacheUtil;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Extbase\Event\Persistence\EntityPersistedEvent;

class CacheClearing implements LazyEventSubscriberInterface
{
    /**
     * The list of all cache identifiers that have been mapped to a implementation
     * in the dependency injection container.
     *
     * @var array
     */
    protected $cacheIdentifiers;
    
    /**
     * @var \TYPO3\CMS\Core\Cache\CacheManager
     */
    protected $cacheManager;
    
    public function __construct(array $cacheIdentifiers, CacheManager $cacheManager)
    {
        $this->cacheIdentifiers = $cacheIdentifiers;
        $this->cacheManager = $cacheManager;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(CacheClearedEvent::class, 'onCacheCleared');
        $subscription->subscribe([
            ActionPostProcessorEvent::class,
            SavePostProcessorEvent::class,
        ], 'onDataHandlerAction');
        $subscription->subscribe(EntityPersistedEvent::class, 'onExtBaseObjectPersisting');
    }
    
    /**
     * Flushes the caches for a single page if the "flash" icon is clicked on a page
     *
     * @param   \LaborDigital\T3BA\Event\Core\CacheClearedEvent  $event
     */
    public function onCacheCleared(CacheClearedEvent $event): void
    {
        // Ignore if this was triggered by a content element
        // This handler only listens for the "flash" icon on a page.
        if (in_array('tt_content', $event->getTags(), true)) {
            return;
        }
        
        $tags = [];
        foreach ($event->getTags() as $tag) {
            if (stripos($tag, 'pageId_') !== 0) {
                continue;
            }
            
            $id = (int)substr($tag, 7);
            $tags[] = 'page_' . $id;
        }
        $this->clearWithTags($tags);
    }
    
    /**
     * Clears the required cache entries if the data handler modified a record in the database
     *
     * @param   object  $event
     */
    public function onDataHandlerAction(object $event): void
    {
        $table = $event->getTableName();
        $id = $event->getId();
        if (! is_numeric($id)) {
            return;
        }
        $this->clearWithTags([$table . '_' . $id]);
    }
    
    /**
     * Clears the required cache entries if extbase persisted an object
     *
     * @param   \TYPO3\CMS\Extbase\Event\Persistence\EntityPersistedEvent  $event
     */
    public function onExtBaseObjectPersisting(EntityPersistedEvent $event): void
    {
        $this->clearWithTags(CacheUtil::stringifyTag($event->getObject()));
    }
    
    /**
     * Internal helper to flush all caches that have been used in implementations
     *
     * @param   array  $tags
     */
    protected function clearWithTags(array $tags): void
    {
        if (empty($tags)) {
            return;
        }
        
        foreach ($this->cacheIdentifiers as $identifier) {
            if ($this->cacheManager->hasCache($identifier)) {
                $this->cacheManager->getCache($identifier)->flushByTags(array_unique($tags));
            }
        }
    }
}
