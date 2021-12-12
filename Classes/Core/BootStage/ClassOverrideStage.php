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


namespace LaborDigital\T3ba\Core\BootStage;


use LaborDigital\T3ba\Core\CodeGeneration\AutoLoader;
use LaborDigital\T3ba\Core\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\LegacyContext;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideList;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideStackResolver;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\Kernel;
use LaborDigital\T3ba\Core\Override\ExtendedBackendUtility;
use LaborDigital\T3ba\Core\Override\ExtendedBootstrap;
use LaborDigital\T3ba\Core\Override\ExtendedCacheManager;
use LaborDigital\T3ba\Core\Override\ExtendedContainerBuilder;
use LaborDigital\T3ba\Core\Override\ExtendedDataHandler;
use LaborDigital\T3ba\Core\Override\ExtendedDataMapper;
use LaborDigital\T3ba\Core\Override\ExtendedHiddenRestriction;
use LaborDigital\T3ba\Core\Override\ExtendedLanguageService;
use LaborDigital\T3ba\Core\Override\ExtendedLocalizationUtility;
use LaborDigital\T3ba\Core\Override\ExtendedNodeFactory;
use LaborDigital\T3ba\Core\Override\ExtendedPackageManager;
use LaborDigital\T3ba\Core\Override\ExtendedReferenceIndex;
use LaborDigital\T3ba\Core\Override\ExtendedRootLineUtility;
use LaborDigital\T3ba\Core\Override\ExtendedSiteConfiguration;
use LaborDigital\T3ba\Core\Override\ExtendedTypoScriptParser;
use LaborDigital\T3ba\Event\KernelBootEvent;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\DependencyInjection\ContainerBuilder;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ClassOverrideStage implements BootStageInterface
{
    
    public const OVERRIDE_MAP
        = [
            HiddenRestriction::class => ExtendedHiddenRestriction::class,
            BackendUtility::class => ExtendedBackendUtility::class,
            Bootstrap::class => ExtendedBootstrap::class,
            ContainerBuilder::class => ExtendedContainerBuilder::class,
            CacheManager::class => ExtendedCacheManager::class,
            TypoScriptParser::class => ExtendedTypoScriptParser::class,
            LocalizationUtility::class => ExtendedLocalizationUtility::class,
            LanguageService::class => ExtendedLanguageService::class,
            SiteConfiguration::class => ExtendedSiteConfiguration::class,
            NodeFactory::class => ExtendedNodeFactory::class,
            DataHandler::class => ExtendedDataHandler::class,
            ReferenceIndex::class => ExtendedReferenceIndex::class,
            DataMapper::class => ExtendedDataMapper::class,
            PackageManager::class => ExtendedPackageManager::class,
            RootlineUtility::class => ExtendedRootLineUtility::class,
        ];
    
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        ClassOverrideGenerator::init(
            $this->makeAutoLoaderInstance($kernel),
            // @todo the phpunit flag should be detected by and inherited from the kernel class
            str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'phpunit')
        );
        
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
    
    /**
     * Factory to register the internal services in our container and initialize the auto loader instance
     *
     * @param   \LaborDigital\T3ba\Core\Kernel  $kernel
     *
     * @return \LaborDigital\T3ba\Core\CodeGeneration\AutoLoader
     */
    protected function makeAutoLoaderInstance(Kernel $kernel): AutoLoader
    {
        $container = $kernel->getContainer();
        $composerClassLoader = $kernel->getClassLoader();
        
        $container->set(OverrideList::class, new OverrideList());
        $container->set(OverrideStackResolver::class, new OverrideStackResolver(
            $kernel->getEventBus(),
            $mount = $kernel->getFs()->getMount('ClassOverrides'),
            static function () use ($composerClassLoader) {
                return new CodeGenerator($composerClassLoader);
            }
        ));
        
        $loader = new AutoLoader(
            $container->get(OverrideList::class),
            $container->get(OverrideStackResolver::class),
            new LegacyContext(
                $mount,
                $composerClassLoader
            )
        );
        
        $container->set(AutoLoader::class, $loader);
        
        return $loader;
    }
    
}
