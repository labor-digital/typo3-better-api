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
 * Last modified: 2020.03.19 at 02:38
 */

namespace LaborDigital\Typo3BetterApi\Cache;

class PageBasedCache extends AbstractCache
{
    protected $cacheConfigKey = 'ba_cache_pagebased';

    /**
     * Contains the current page id after it was once acquired
     *
     * @var int|null
     */
    protected $pageId;

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->setInternal($key, $value, $ttl, $this->getTags());
    }

    /**
     * Removes all cache entries for a given page
     *
     * @param   int  $pid
     *
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function clearForPage(int $pid)
    {
        $this->getTypoCache()->flushByTag('pageId_' . $pid);
    }

    /**
     * Prepares the key with an additional prefix and formats it so it is save for all applications
     *
     * @param $key
     *
     * @return string
     * @throws \Exception
     */
    protected function prepareKey($key): string
    {
        // Prepare page based prefix
        $prefix = $this->typoContext->getEnvAspect()->isFrontend() ? 'fe' : 'be';
        $prefix .= '-' . $this->getPageId();
        if ($this->tsfe->hasTsfe()) {
            $prefix .= '-' . $this->typoContext->getLanguageAspect()->getCurrentFrontendLanguage()->getLanguageId();
            $prefix .= '-' . $this->tsfe->getTsfe()->newHash;
        }

        // Build combined key
        $key = parent::prepareKey(is_string($key) ? $key : md5(serialize($key)));
        $key = $prefix . '-' . $key;

        return $key;
    }

    /**
     * Returns either the current page id or 0 if no page id was found
     *
     * @return int
     * @throws \Exception
     */
    protected function getPageId(): int
    {
        // Check if we know the page id already
        if (isset($this->pageId)) {
            return $this->pageId;
        }

        // Extract pid
        return $this->pageId = $this->typoContext->getPidAspect()->getCurrentPid();
    }

    /**
     * Returns the pagebased tags for all created cache entries
     *
     * @return array
     * @throws \Exception
     */
    protected function getTags(): array
    {
        return ['pageId_' . $this->getPageId()];
    }

    /**
     * @inheritDoc
     */
    protected function prepareTtl($ttl)
    {
        // Use default method
        $ttl = parent::prepareTtl($ttl);

        // Check if we have to add the page"s cache time
        if ($ttl === null && $this->typoContext->getEnvAspect()->isFrontend()) {
            if ($this->tsfe->hasTsfe() && is_array($this->tsfe->getTsfe()->page)) {
                $ttl = (int)$this->tsfe->getTsfe()->page['cache_timeout'];
            }
        }

        // Done
        return $ttl;
    }
}
