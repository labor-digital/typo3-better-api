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
 * Last modified: 2020.05.12 at 13:50
 */

namespace LaborDigital\T3ba\TypoContext;

use LaborDigital\T3ba\Core\Exception\T3baException;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Repository of information about the environment
 */
class EnvFacet implements FacetInterface
{
    
    /**
     * Stores the current typo3 version as integer
     *
     * @var int
     */
    protected $versionInt;
    
    /**
     * Stores the version comparisons to save repetitive overhead
     *
     * @var array
     */
    protected $versionComparisons = [];
    
    /**
     * Returns typo3's application context object
     *
     * @return \TYPO3\CMS\Core\Core\ApplicationContext
     */
    public function getApplicationContext(): ApplicationContext
    {
        return Environment::getContext();
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'env';
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
     * @throws T3baException
     * @noinspection TypeUnsafeComparisonInspection
     */
    public function isVersion($version, string $operator = '='): bool
    {
        // Serve already created comparisons from the fast lane cache
        $key = $version . $operator;
        if (isset($this->versionComparisons[$key])) {
            return $this->versionComparisons[$key];
        }
        
        // Get the integer version of the typo3 version
        if (empty($this->versionInt)) {
            $this->versionInt = VersionNumberUtility::convertVersionNumberToInteger($this->getVersion());
        }
        $versionInt = $this->versionInt;
        
        // Check if we have to use fuzzy compare
        if (strlen($version . '') < 3) {
            $versionInt = floor($versionInt / 1000000) * 1000000;
        }
        
        // Compare the given version with the current version
        $givenInt = VersionNumberUtility::convertVersionNumberToInteger($version);
        switch ($operator) {
            case '=':
                $this->versionComparisons[$key] = $versionInt == $givenInt;
                break;
            case '!=':
                $this->versionComparisons[$key] = $versionInt != $givenInt;
                break;
            case '>':
                $this->versionComparisons[$key] = $versionInt > $givenInt;
                break;
            case '>=':
                $this->versionComparisons[$key] = $versionInt >= $givenInt;
                break;
            case '<':
                $this->versionComparisons[$key] = $versionInt < $givenInt;
                break;
            case '<=':
                $this->versionComparisons[$key] = $versionInt <= $givenInt;
                break;
            default:
                throw new T3baException("Invalid operator \"$operator\" given! Only =, !=, <, >, <= or >= are supported!");
        }
        
        // Done
        return $this->versionComparisons[$key];
    }
    
    /**
     * Returns the current typo3 version as a semver string.
     *
     * @param   bool  $exact  If this is set to true the version may contain suffixes like "-dev" "-rc..." or similar.
     *
     * @return string
     */
    public function getVersion(bool $exact = false): string
    {
        return $exact ? VersionNumberUtility::getCurrentTypo3Version() : VersionNumberUtility::getNumericTypo3Version();
    }
    
    /**
     * Returns true if the current call was performed in the typo3 backend
     *
     * @return bool
     */
    public function isBackend(): bool
    {
        return defined('TYPO3_MODE') && TYPO3_MODE === 'BE';
    }
    
    /**
     * Returns true if the current call was performed in the typo3 frontend
     *
     * @return bool
     */
    public function isFrontend(): bool
    {
        return defined('TYPO3_MODE') && TYPO3_MODE === 'FE';
    }
    
    /**
     * Returns true if the current call was performed in the typo3 cli handler
     *
     * @return bool
     */
    public function isCli(): bool
    {
        return Environment::isCli() || PHP_SAPI === 'cli';
    }
    
    /**
     * Returns true if the current call was performed in the typo3 install tool
     *
     * @return bool
     */
    public function isInstall(): bool
    {
        return TYPO3_REQUESTTYPE === TYPO3_REQUESTTYPE_INSTALL
               || TYPO3_REQUESTTYPE === (TYPO3_REQUESTTYPE_INSTALL + TYPO3_REQUESTTYPE_BE);
    }
    
    /**
     * Returns true if debug messages in the frontend should be shown
     *
     * @return bool
     */
    public function isFeDebug(): bool
    {
        return ! empty($GLOBALS['TYPO3_CONF_VARS']['FE']['debug']);
    }
    
    /**
     * Returns true if debug messages in the backend should be shown
     *
     * @return bool
     */
    public function isBeDebug(): bool
    {
        return ! empty($GLOBALS['TYPO3_CONF_VARS']['BE']['debug']);
    }
    
    /**
     * Returns true if the current instance is running in development context
     *
     * @return bool
     */
    public function isDev(): bool
    {
        return Environment::getContext()->isDevelopment();
    }
    
    /**
     * Returns true if the current instance is running in production OR staging context
     *
     * @param   bool  $includeStaging  If this is set to false the method will not check for a staging version,
     *                                 instead it only returns true IF the context is set to production
     *
     * @return bool
     */
    public function isProduction(bool $includeStaging = true): bool
    {
        return $includeStaging ? ! $this->isDev() : Environment::getContext()->isProduction();
    }
    
    /**
     * Returns true if the current instance is running in staging context
     *
     * @return bool
     */
    public function isStaging(): bool
    {
        return Environment::getContext()->isTesting();
    }
}
