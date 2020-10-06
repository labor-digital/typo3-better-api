<?php
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

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides;

use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;
use LaborDigital\Typo3BetterApi\Event\Events\SiteConfigFilterEvent;
use TYPO3\CMS\Core\Configuration\BetterApiClassOverrideCopy__SiteConfiguration;

class ExtendedSiteConfiguration extends BetterApiClassOverrideCopy__SiteConfiguration
{
    use CommonDependencyTrait;

    /**
     * @inheritDoc
     */
    public function getAllSiteConfigurationFromFiles(): array
    {
        $siteConfig = parent::getAllSiteConfigurationFromFiles();

        // Ignore this if the backend generates new yaml files
        if ($this->TypoContext()->Env()->isBackend()
            && $this->TypoContext()->Request()->getGet('route') === '/site/configuration/'
            && $this->TypoContext()->Request()->getGet('action') === 'save') {
            return $siteConfig;
        }

        // Allow filtering
        $this->EventBus()->dispatch(($e = new SiteConfigFilterEvent($siteConfig)));
        $siteConfig = $e->getConfig();

        return $siteConfig;
    }
}
