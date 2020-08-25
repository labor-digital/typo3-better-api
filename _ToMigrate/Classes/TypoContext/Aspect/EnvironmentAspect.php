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

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\TypoContext\Facet\EnvFacet;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;

/**
 * Class EnvironmentAspect
 *
 * @package    LaborDigital\Typo3BetterApi\TypoContext\Aspect
 * @deprecated will be removed in v10 -> Use EnvFacet instead
 */
class EnvironmentAspect implements AspectInterface
{
    use AutomaticAspectGetTrait;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\Facet\EnvFacet
     */
    protected $facet;
    
    /**
     * EnvironmentAspect constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\Facet\EnvFacet  $facet
     */
    public function __construct(EnvFacet $facet)
    {
        $this->facet = $facet;
    }
    
    /**
     * @inheritDoc
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function get(string $name)
    {
        if ($name === 'FACET') {
            return $this->facet;
        }
        
        return $this->handleGet($name);
    }
    
    /**
     * Returns typo3's application context object
     *
     * @return \TYPO3\CMS\Core\Core\ApplicationContext
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function getApplicationContext(): ApplicationContext
    {
        return $this->facet->getApplicationContext();
    }
    
    /**
     * Returns true if the current typo3 version is LTS 9.x
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function is9(): bool
    {
        return $this->facet->is9();
    }
    
    /**
     * Returns true if the current typo3 version is LTS 10.x
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function is10(): bool
    {
        return $this->facet->is10();
    }
    
    /**
     * Can be used to compare a given version with the current typo3 version
     *
     * @param   string|double|int  $version   The version to check for.
     *                                        If just a single number, like 7, 8 or 9 is given, this method will fuzzy
     *                                        compare the Typo3 major-version with the given number.
     * @param   string             $operator  The operator to use when checking the version.
     *                                        The operator can be one of: =, !=, <, >, <= or >=
     *                                        The final statement will be read as: $typo3Version $operator $yourVersion
     *
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isVersion($version, string $operator = '='): bool
    {
        return $this->facet->isVersion($version, $operator);
    }
    
    /**
     * Returns the current typo3 version as a semver string.
     *
     * @param   bool  $exact  If this is set to true the version may contain suffixes like "-dev" "-rc..." or similar.
     *
     * @return string
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function getVersion(bool $exact = false): string
    {
        return $this->facet->getVersion($exact);
    }
    
    /**
     * Returns true if the current call was performed in the typo3 backend
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isBackend(): bool
    {
        return $this->facet->isBackend();
    }
    
    /**
     * Returns true if the current call was performed in the typo3 frontend
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isFrontend(): bool
    {
        return $this->facet->isFrontend();
    }
    
    /**
     * Returns true if the current call was performed in the typo3 cli handler
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isCli(): bool
    {
        return $this->facet->isCli();
    }
    
    /**
     * Returns true if the current call was performed in the typo3 install tool
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isInstall(): bool
    {
        return $this->facet->isInstall();
    }
    
    /**
     * Returns true if the current instance is running in development context
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isDev(): bool
    {
        return $this->facet->isDev();
    }
    
    /**
     * Returns true if the current instance is running in production OR staging context
     *
     * @param   bool  $includeStaging  If this is set to false the method will not check for a staging version,
     *                                 instead it only returns true IF the context is set to production
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isProduction(bool $includeStaging = true): bool
    {
        return $this->facet->isProduction($includeStaging);
    }
    
    /**
     * Returns true if the current instance is running in staging context
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use EnvFacet instead
     */
    public function isStaging(): bool
    {
        return $this->facet->isStaging();
    }
}
