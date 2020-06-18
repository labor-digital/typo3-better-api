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

namespace LaborDigital\Typo3BetterApi\Cache\Internals;

use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;

class ExtendedSimpleFileBackend extends SimpleFileBackend implements ExtendedSimpleFileBackendInterface
{
    /**
     * Returns the filename for a cache key stored in this cache's directory
     *
     * @param   string  $key  The key to look up
     *
     * @return mixed Either the filepath or false if no file was found for this key
     */
    public function getFilenameForKey(string $key)
    {
        $result = $this->findCacheFilesByIdentifier($key);
        if (is_array($result)) {
            return array_shift($result);
        }
        
        return $result;
    }
}
