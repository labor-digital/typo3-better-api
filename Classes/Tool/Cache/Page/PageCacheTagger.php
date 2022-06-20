<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.06.20 at 15:07
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Cache\Page;


use LaborDigital\T3ba\Tool\Cache\Util\CacheUtil;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This utility can be used to automatically add cache tags to the page cache.
 * This allows TYPO3 to automatically remove cached pages when the required records have been changed by the backend
 * or via extbase.
 */
class PageCacheTagger implements SingletonInterface
{
    /**
     * Allows the outside world to set the cache timeout for the currently generated page
     *
     * @param   int  $seconds
     *
     * @return $this
     */
    public function setCacheTimeout(int $seconds): self
    {
        $tsfe = $this->getTsfe();
        if ($tsfe && is_array($tsfe->page)) {
            $tsfe->page['cache_timeout'] = $seconds;
            $this->flushTimeoutCache();
        }
        
        return $this;
    }
    
    /**
     * Returns the cache timeout for the currently generated page.
     * NOTE: This takes the page content timestamps into account!
     *
     * @return int
     */
    public function getCacheTimeout(): int
    {
        $tsfe = $this->getTsfe();
        if (! $tsfe) {
            return 0;
        }
        
        return $tsfe->get_cache_timeout();
    }
    
    /**
     * Allows the outside world to mark the page to be cleared at midnight.
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function setClearAtMidnight(bool $state): self
    {
        $tsfe = $this->getTsfe();
        if ($tsfe && is_array($tsfe->config)) {
            $tsfe->config['config']['cache_clearAtMidnight'] = $state ? '1' : '';
            $this->flushTimeoutCache();
        }
        
        return $this;
    }
    
    /**
     * Returns true if the cache for the current page should be cleared at midnight.
     *
     * @return bool
     */
    public function doClearAtMidnight(): bool
    {
        $tsfe = $this->getTsfe();
        
        return $tsfe && is_array($tsfe->config) && ! empty($tsfe->config['config']['cache_clearAtMidnight']);
    }
    
    /**
     * Adds a new item as a page cache tag
     *
     * @param   mixed  $item      The item to add as a cache tag {@see CacheUtil::stringifyTag()}
     * @param   int    $maxDepth  The maximum depth of properties to traverse when generating the tags
     *
     * @return mixed Returns the provided element, to allow easy integration into existing flows
     */
    public function addTag($item, int $maxDepth = 2)
    {
        return $this->addTags([$item], $maxDepth)[0];
    }
    
    /**
     * Adds a given list of item as page cache tags
     *
     * @param   iterable  $items     The list of items to add as cache tags {@see CacheUtil::stringifyTag()}
     * @param   int       $maxDepth  The maximum depth of properties to traverse when generating the tags
     *
     * @return iterable Returns the provided list, to allow easy integration into existing flows
     */
    public function addTags(iterable $items, int $maxDepth = 2): iterable
    {
        $tsfe = $this->getTsfe();
        if (! $tsfe) {
            return $items;
        }
        
        $tags = [];
        foreach ($items as $item) {
            $tags[] = CacheUtil::stringifyTag($item, $maxDepth);
        }
        
        $tsfe->addCacheTags(array_diff(array_unique(array_merge(...$tags)), $this->getTags()));
        
        return $items;
    }
    
    /**
     * Shortcut to {@link TypoScriptFrontendController::getPageCacheTags()}
     *
     * @return array
     */
    public function getTags(): array
    {
        $tsfe = $this->getTsfe();
        if (! $tsfe) {
            return [];
        }
        
        return $tsfe->getPageCacheTags();
    }
    
    /**
     * Resolves the TSFE or returns null if it can't be found
     *
     * @return \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController|null
     */
    protected function getTsfe(): ?TypoScriptFrontendController
    {
        if (! isset($GLOBALS['TSFE']) || ! $GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            return null;
        }
        
        return $GLOBALS['TSFE'];
    }
    
    /**
     * Flushes the timeout runtime cache after we modified the values at runtime
     *
     * @return void
     */
    protected function flushTimeoutCache(): void
    {
        $runtimeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('runtime');
        $runtimeCache->remove('core-tslib_fe-get_cache_timeout');
    }
}