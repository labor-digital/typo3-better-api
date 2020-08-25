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

use LaborDigital\Typo3BetterApi\Event\Events\SiteConfigFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Core\Configuration\BetterApiClassOverrideCopy__SiteConfiguration;

class ExtendedSiteConfiguration extends BetterApiClassOverrideCopy__SiteConfiguration
{
    /**
     * @inheritDoc
     */
    public function getAllSiteConfigurationFromFiles(): array
    {
        // Create the configuration if it is not yet cached
        $isCached   = ! empty($this->getCache()->get($this->cacheIdentifier));
        $siteConfig = parent::getAllSiteConfigurationFromFiles();
        if ($isCached) {
            return $siteConfig;
        }

        // Allow filtering
        TypoEventBus::getInstance()->dispatch(($e = new SiteConfigFilterEvent($siteConfig)));
        $siteConfig = $e->getConfig();

        // Update the cached value
        $this->getCache()->set($this->cacheIdentifier, json_encode($siteConfig, JSON_THROW_ON_ERROR));

        // Done
        return $siteConfig;
    }
}
