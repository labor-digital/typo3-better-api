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
 * Last modified: 2020.03.20 at 18:04
 */

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides;

use LaborDigital\Typo3BetterApi\Event\Events\CacheClearedEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Core\Cache\BetterApiClassOverrideCopy__CacheManager;

class ExtendedCacheManager extends BetterApiClassOverrideCopy__CacheManager
{
    
    /**
     * @inheritDoc
     */
    public function flushCaches()
    {
        parent::flushCaches();
        $this->__emitFlushEvent(__FUNCTION__);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesInGroup($groupIdentifier)
    {
        parent::flushCachesInGroup($groupIdentifier);
        $this->__emitFlushEvent(__FUNCTION__, $groupIdentifier);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesInGroupByTag($groupIdentifier, $tag)
    {
        parent::flushCachesInGroupByTag($groupIdentifier, $tag);
        $this->__emitFlushEvent(__FUNCTION__, $groupIdentifier, $tag);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesByTag($tag)
    {
        parent::flushCachesByTag($tag);
        $this->__emitFlushEvent(__FUNCTION__, null, $tag);
    }
    
    /**
     * Internal helper to emit the clear cache event
     *
     * @param   string       $caller
     * @param   string|null  $group
     * @param   string|null  $tag
     */
    protected function __emitFlushEvent(string $caller, ?string $group = null, ?string $tag = null)
    {
        /** @noinspection PhpParamsInspection */
        TypoEventBus::getInstance()->dispatch(new CacheClearedEvent(
            $caller,
            empty($group) ? 'all' : $group,
            $tag,
            $this
        ));
    }
}
