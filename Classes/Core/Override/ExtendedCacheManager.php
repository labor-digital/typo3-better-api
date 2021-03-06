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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);
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

namespace LaborDigital\T3ba\Core\Override;

use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\Core\CacheClearedEvent;
use TYPO3\CMS\Core\Cache\T3BaCopyCacheManager;

class ExtendedCacheManager extends T3BaCopyCacheManager
{
    
    /**
     * @inheritDoc
     */
    public function flushCaches(): void
    {
        parent::flushCaches();
        $this->emitFlushEvent(__FUNCTION__);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesInGroup($groupIdentifier): void
    {
        parent::flushCachesInGroup($groupIdentifier);
        $this->emitFlushEvent(__FUNCTION__, $groupIdentifier);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesInGroupByTag($groupIdentifier, $tag): void
    {
        parent::flushCachesInGroupByTag($groupIdentifier, $tag);
        $this->emitFlushEvent(__FUNCTION__, $groupIdentifier, [$tag]);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesInGroupByTags($groupIdentifier, array $tags): void
    {
        parent::flushCachesInGroupByTags($groupIdentifier, $tags);
        $this->emitFlushEvent(__FUNCTION__, $groupIdentifier, $tags);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesByTag($tag): void
    {
        parent::flushCachesByTag($tag);
        $this->emitFlushEvent(__FUNCTION__, null, [$tag]);
    }
    
    /**
     * @inheritDoc
     */
    public function flushCachesByTags(array $tags): void
    {
        parent::flushCachesByTags($tags);
        $this->emitFlushEvent(__FUNCTION__, null, $tags);
    }
    
    /**
     * Internal helper to emit the clear cache event
     *
     * @param   string       $caller
     * @param   string|null  $group
     * @param   array|null   $tags
     */
    protected function emitFlushEvent(string $caller, ?string $group = null, ?array $tags = null): void
    {
        /** @noinspection PhpParamsInspection */
        TypoEventBus::getInstance()->dispatch(new CacheClearedEvent(
            $caller,
            empty($group) ? 'all' : $group,
            $tags ?? [],
            $this
        ));
    }
}
