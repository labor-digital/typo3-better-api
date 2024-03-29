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


namespace LaborDigital\T3ba\TypoContext\Util;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CacheLessSiteConfigurationAdapter extends SiteConfiguration implements NoDiInterface
{
    /**
     * @inheritDoc
     */
    public function __construct(string $configPath = '') { parent::__construct($configPath); }
    
    /**
     * @inheritDoc
     */
    protected function getCache(): PhpFrontend
    {
        return new PhpFrontend('foo', new NullBackend('foo'));
    }
    
    /**
     * Creates a new instance of myself based on the given site config
     *
     * @param   \TYPO3\CMS\Core\Configuration\SiteConfiguration|null  $siteConfiguration
     *
     * @return static
     * @noinspection ExposingInternalClassesInspection
     */
    public static function makeInstance(?SiteConfiguration $siteConfiguration = null): self
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $siteConfiguration = $siteConfiguration ?? GeneralUtility::makeInstance(SiteConfiguration::class);
        $configPath = $siteConfiguration->configPath;
        
        return GeneralUtility::makeInstance(static::class, $configPath);
    }
}
