<?php
/*
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
 * Last modified: 2020.08.22 at 21:56
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use LaborDigital\T3BA\Core\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\T3BA\Core\Event\KernelBootEvent;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\Override\ExtendedBootstrap;
use LaborDigital\T3BA\Core\Override\ExtendedCacheManager;
use LaborDigital\T3BA\Core\Override\ExtendedContainerBuilder;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\DependencyInjection\ContainerBuilder;

class ClassOverrideStage implements BootStageInterface
{

    public const OVERRIDE_MAP
        = [
            Bootstrap::class        => ExtendedBootstrap::class,
            ContainerBuilder::class => ExtendedContainerBuilder::class,
            CacheManager::class     => ExtendedCacheManager::class,
        ];

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        // Register the override generator's auto-loader
        ClassOverrideGenerator::init($kernel->getClassLoader());

        // Register overrides
        $eventBus->addListener(KernelBootEvent::class, static function () {
            foreach (static::OVERRIDE_MAP as $target => $override) {
                if (ClassOverrideGenerator::hasClassOverride($override)) {
                    continue;
                }
                ClassOverrideGenerator::registerOverride($target, $override);
            }
        });

//        // Apply the required overrides
//        ClassOverrideGenerator::registerOverride(\TYPO3\CMS\Core\Core\Bootstrap::class, ExtendedBootstrap::class);
//        ClassOverrideGenerator::registerOverride(CacheManager::class, ExtendedCacheManager::class);
//        ClassOverrideGenerator::registerOverride(LocalizationUtility::class, ExtendedLocalizationUtility::class);
//        ClassOverrideGenerator::registerOverride(DataHandler::class, ExtendedDataHandler::class);
//        ClassOverrideGenerator::registerOverride(NodeFactory::class, ExtendedNodeFactory::class);
//        ClassOverrideGenerator::registerOverride(DataMapper::class, ExtendedDataMapper::class);
//        ClassOverrideGenerator::registerOverride(SiteConfiguration::class, ExtendedSiteConfiguration::class);
//        ClassOverrideGenerator::registerOverride(ReferenceIndex::class, ExtendedReferenceIndex::class);
//
//        // Make sure we don't crash legacy code when changing the language service
//        ClassOverrideGenerator::registerOverride(
//            LanguageService::class,
//            ExtendedLanguageService::class
//        );
//        if (! class_exists(LanguageService::class, false)
//            && ! class_exists(\TYPO3\CMS\Lang\LanguageService::class, false)) {
//            class_alias(LanguageService::class, \TYPO3\CMS\Lang\LanguageService::class);
//        }
    }

}
