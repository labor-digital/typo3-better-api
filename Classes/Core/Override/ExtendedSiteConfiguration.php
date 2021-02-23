<?php
/*
 * Copyright 2020 Martin Neundorfer (Neunerlei)
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
 * Last modified: 2020.08.09 at 14:49
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
 * Last modified: 2020.03.18 at 17:36
 */

namespace LaborDigital\T3BA\Core\Override;

use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\Core\SiteConfigFilterEvent;
use LaborDigital\T3BA\ExtConfig\Adapter\CachelessSiteConfigurationAdapter;
use TYPO3\CMS\Core\Configuration\T3BA__Copy__SiteConfiguration;

class ExtendedSiteConfiguration extends T3BA__Copy__SiteConfiguration
{
    /**
     * @inheritDoc
     */
    public function getAllSiteConfigurationFromFiles(bool $useCache = true): array
    {
        // Create the configuration if it is not yet cached
        $isCached   = $useCache && ! empty($this->getCache()->get($this->cacheIdentifier));
        $siteConfig = parent::getAllSiteConfigurationFromFiles($useCache);
        if ($isCached) {
            return $siteConfig;
        }

        // Special switch if we load the configuration early
        if ($this instanceof CachelessSiteConfigurationAdapter) {
            return $siteConfig;
        }

        // Allow filtering
        TypoEventBus::getInstance()->dispatch(($e = new SiteConfigFilterEvent($siteConfig)));
        $siteConfig = $e->getConfig();

        // Update the cached value
        if ($useCache) {
            $this->getCache()->set($this->cacheIdentifier, 'return ' . var_export($siteConfig, true) . ';');
        }

        // Done
        return $siteConfig;
    }
}
