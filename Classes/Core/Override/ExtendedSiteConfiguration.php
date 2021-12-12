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
 * Last modified: 2020.03.18 at 17:36
 */

namespace LaborDigital\T3ba\Core\Override;

use LaborDigital\T3ba\Event\Core\SiteConfigFilterEvent;
use LaborDigital\T3ba\Tool\OddsAndEnds\ReflectionUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContextAwareTrait;
use LaborDigital\T3ba\TypoContext\Util\CacheLessSiteConfigurationAdapter;
use TYPO3\CMS\Backend\Controller\SiteConfigurationController;
use TYPO3\CMS\Core\Configuration\T3BaCopySiteConfiguration;

class ExtendedSiteConfiguration extends T3BaCopySiteConfiguration
{
    use TypoContextAwareTrait;
    
    /**
     * @inheritDoc
     */
    public function getAllSiteConfigurationFromFiles(bool $useCache = true): array
    {
        // Create the configuration if it is not yet cached
        $isCached = $useCache && ! empty($this->cache->get($this->cacheIdentifier));
        $siteConfig = parent::getAllSiteConfigurationFromFiles($useCache);
        if ($isCached) {
            return $siteConfig;
        }
        
        // Special switch if we load the configuration early
        if ($this instanceof CacheLessSiteConfigurationAdapter) {
            return $siteConfig;
        }
        
        // Ignore this if the backend generates new yaml files
        $context = $this->getTypoContext();
        if (! $useCache && $context->env()->isBackend() &&
            ReflectionUtil::getClosestFromStack(SiteConfigurationController::class, 10) !== null) {
            $this->cache->remove($this->cacheIdentifier);
            
            return $siteConfig;
        }
        
        // Allow filtering
        $siteConfig = $this->getTypoContext()->di()->cs()->eventBus
            ->dispatch(new SiteConfigFilterEvent($siteConfig))
            ->getConfig();
        
        // Update the cached value
        if ($useCache) {
            $this->cache->set($this->cacheIdentifier, 'return ' . var_export($siteConfig, true) . ';');
        }
        
        // Done
        return $siteConfig;
    }
}
