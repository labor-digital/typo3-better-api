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
 * Last modified: 2020.05.27 at 03:16
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core;


use Composer\Autoload\ClassLoader;
use LaborDigital\T3BA\Core\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\T3BA\Core\Override\ExtendedBootstrap;
use LaborDigital\T3BA\Core\Override\ExtendedCacheManager;
use LaborDigital\T3BA\Core\Override\ExtendedDataHandler;
use LaborDigital\T3BA\Core\Override\ExtendedDataMapper;
use LaborDigital\T3BA\Core\Override\ExtendedLanguageService;
use LaborDigital\T3BA\Core\Override\ExtendedLocalizationUtility;
use LaborDigital\T3BA\Core\Override\ExtendedNodeFactory;
use LaborDigital\T3BA\Core\Override\ExtendedReferenceIndex;
use LaborDigital\T3BA\Core\Override\ExtendedSiteConfiguration;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class Bootstrap
{
    /**
     * The instance after the
     * @var self
     */
    protected static $instance;

    public function __construct() { }

    public static function init(ClassLoader $composerClassLoader): void
    {
        // Register the override generator's auto-loader
        ClassOverrideGenerator::init($composerClassLoader);

        // Apply the required overrides
        ClassOverrideGenerator::registerOverride(Bootstrap::class, ExtendedBootstrap::class);
        ClassOverrideGenerator::registerOverride(CacheManager::class, ExtendedCacheManager::class);
        ClassOverrideGenerator::registerOverride(LocalizationUtility::class, ExtendedLocalizationUtility::class);
        ClassOverrideGenerator::registerOverride(DataHandler::class, ExtendedDataHandler::class);
        ClassOverrideGenerator::registerOverride(NodeFactory::class, ExtendedNodeFactory::class);
        ClassOverrideGenerator::registerOverride(DataMapper::class, ExtendedDataMapper::class);
        ClassOverrideGenerator::registerOverride(SiteConfiguration::class, ExtendedSiteConfiguration::class);
        ClassOverrideGenerator::registerOverride(ReferenceIndex::class, ExtendedReferenceIndex::class);

        // Make sure we don't crash legacy code when changing the language service
        ClassOverrideGenerator::registerOverride(
            LanguageService::class,
            ExtendedLanguageService::class
        );
        if (! class_exists(LanguageService::class, false)
            && ! class_exists(\TYPO3\CMS\Lang\LanguageService::class, false)) {
            class_alias(LanguageService::class, \TYPO3\CMS\Lang\LanguageService::class);
        }

        // Start our bootstrap
        (new self())->run();
    }

    protected function run()
    {
    }
}
