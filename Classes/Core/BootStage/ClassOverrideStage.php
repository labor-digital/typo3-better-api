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
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Core\Override\ExtendedBootstrap;
use LaborDigital\T3BA\Core\Override\ExtendedCacheManager;
use LaborDigital\T3BA\Core\Override\ExtendedContainerBuilder;
use LaborDigital\T3BA\Core\Override\ExtendedLanguageService;
use LaborDigital\T3BA\Core\Override\ExtendedLocalizationUtility;
use LaborDigital\T3BA\Core\Override\ExtendedTypoScriptParser;
use LaborDigital\T3BA\Event\KernelBootEvent;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ClassOverrideStage implements BootStageInterface
{

    public const OVERRIDE_MAP
        = [
            Bootstrap::class           => ExtendedBootstrap::class,
            ContainerBuilder::class    => ExtendedContainerBuilder::class,
            CacheManager::class        => ExtendedCacheManager::class,
            TypoScriptParser::class    => ExtendedTypoScriptParser::class,
            LocalizationUtility::class => ExtendedLocalizationUtility::class,
            LanguageService::class     => ExtendedLanguageService::class,
            //            DataHandler::class         => ExtendedDataHandler::class,
        ];

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        ClassOverrideGenerator::init($kernel->getClassLoader(), $kernel->getFs()->getMount('ClassOverrides'));
        $eventBus->addListener(KernelBootEvent::class, [$this, 'onKernelBoot']);
    }

    /**
     * Register all class overrides
     */
    public function onKernelBoot(): void
    {
        foreach (static::OVERRIDE_MAP as $target => $override) {
            if (ClassOverrideGenerator::hasClassOverride($override)) {
                continue;
            }
            ClassOverrideGenerator::registerOverride($target, $override);
        }
    }

}
