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

use LaborDigital\Typo3BetterApi\Cache\Internals\ExtendedSimpleFileBackendInterface;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;

abstract class AbstractFileBasedCache extends AbstractCache
{
    
    /**
     * Returns the name of a given cache file inside the file cache directory
     *
     * @param   string  $key       The key to get the filename for
     * @param   bool    $absolute  True to return the absolute path for the file
     *
     * @return string|bool
     * @throws \LaborDigital\Typo3BetterApi\Cache\InvalidFileBasedCacheException
     */
    public function getFileName($key, bool $absolute = false)
    {
        // Prepare key
        $key = $this->prepareKey($key);
        
        // Check if the cache is disabled
        if (! $this->isCacheEnabled()) {
            // Check if the key is allowed even without cache
            if (! Arrays::hasPath(static::$allowedCacheKeys, [$this->cacheConfigKey, $key])) {
                return false;
            }
        }
        
        // Get caching frontend
        $cache = $this->getTypoCache();
        
        // Check if the backend exists
        if (! method_exists($cache, 'getBackend')) {
            throw new InvalidFileBasedCacheException('The current cache does not have a getBackend() method!');
        }
        
        // Check if the backend implements our interface
        $backend = $cache->getBackend();
        if (! $backend instanceof ExtendedSimpleFileBackendInterface) {
            if ($backend instanceof NullBackend) {
                return '';
            }
            throw new InvalidFileBasedCacheException('The given cache backend does not implement the required interface: '
                                                     .
                                                     ExtendedSimpleFileBackendInterface::class . '!');
        }
        
        // Resolve the file
        return ! $absolute ? basename($backend->getFilenameForKey($key)) : $backend->getFilenameForKey($key);
    }
}
