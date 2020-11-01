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

namespace LaborDigital\Typo3BetterApi;

use Composer\Autoload\ClassLoader;
use Helhum\Typo3Console\Core\Booting\Scripts;
use Helhum\Typo3Console\Core\Kernel;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\DbBaseIdApplier;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\FalFileBaseDirApplier;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\FieldDefaultAndPlaceholderTranslationApplier;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\FixFlexFormSectionDefinitionApplier;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\FixSectionToggleStateApplier;
use LaborDigital\Typo3BetterApi\BackendForms\Addon\GroupElementsCanTriggerReloadApplier;
use LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContextFilter;
use LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementNode;
use LaborDigital\Typo3BetterApi\BackendForms\CustomWizard\CustomWizardNode;
use LaborDigital\Typo3BetterApi\BackendForms\Node\PathSegmentSlugElementNode;
use LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewService;
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters\ObjectContainerAdapter;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedBootstrap;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedCacheManager;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedDataHandler;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedDataMapper;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedLanguageService;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedLocalizationUtility;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedNodeFactory;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedPackageManager;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedReferenceIndex;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedReflectionService;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedSiteConfiguration;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\Typo3Console\ExtendedScripts;
use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService;
use LaborDigital\Typo3BetterApi\Error\DebugExceptionHandler;
use LaborDigital\Typo3BetterApi\Error\ProductionExceptionHandler;
use LaborDigital\Typo3BetterApi\Event\Events\AfterExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BootstrapFailsafeDefinitionEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\PackageManagerCreatedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\BootstrapContainerFilterEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use LaborDigital\Typo3BetterApi\ExtConfig\Builtin\BetterApiExtConfig;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigService;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use LaborDigital\Typo3BetterApi\Pid\PidTcaFilter;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService;
use Neunerlei\EventBus\EventBusInterface;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Database\ReferenceIndex;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class BetterApiInit
{

    /**
     * True if the init method was executed,
     * this will block further executions
     *
     * @var bool
     */
    protected static $initStarted = false;

    /**
     * True when the init is complete
     *
     * @var bool
     */
    protected static $initComplete = false;

    /**
     * Holds the typo3 event bus if we are ready for it
     *
     * @var EventBusInterface
     */
    protected $eventBus;

    /**
     * The container instance
     *
     * @var TypoContainer
     */
    protected $container;

    /**
     * The app context object
     *
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $context;

    /**
     * BetterApiInit constructor.
     *
     * @param   \Neunerlei\EventBus\EventBusInterface  $eventBus
     */
    protected function __construct(EventBusInterface $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * This stage is executed inside the composerAutoloadInclude.php
     * which is, as the name suggests, loaded as "autoload file" via composer.
     *
     * It contains mostly root level changes that are required before any other
     * class is loaded
     *
     * @param   \Composer\Autoload\ClassLoader  $composerClassLoader
     */
    public static function init(ClassLoader $composerClassLoader): void
    {
        include __DIR__ . '/functions.php';


        // Do remaining bootstrap
        $eventBus->addListener(
            AfterExtLocalConfLoadedEvent::class,
            static function () use ($self) {
                $self->setupContainer();
                $self->dispatchInitEvent();
                $self->setupTypoContext();
                $self->applyCacheConfiguration();
                $self->addFormEngineNodes();
                $self->bindInternalEvents();
                static::$initComplete = true;
            },
            ['priority' => 100]
        );

        // Make sure to destroy the context instance after the bootstrap finished
        // We need this for v9 because we require the Context class, before it is initialized
        // in the Application classes. This will lead to unexpected behaviour in the backend and edge cases.
        $eventBus->addListener(
            BootstrapContainerFilterEvent::class,
            static function () use ($self) {
                GeneralUtility::removeSingletonInstance(
                    Context::class,
                    $self->context->getRootContext()
                );
                ObjectContainerAdapter::removeSingleton(
                    $self->container->get(Container::class),
                    Context::class
                );
                $self->context->__unlinkContext();
            }
        );

    }

    /**
     * Returns true if the init is completed, false if it is still running
     *
     * @return bool
     */
    public static function isComplete(): bool
    {
        return static::$initComplete;
    }

    /**
     * Makes sure that this package is always active in the package states
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager  $packageManager
     */
    protected function forceSelfActivation(PackageManager $packageManager): void
    {
        $packageKey = 'typo3_better_api';
        if ($packageManager->isPackageActive($packageKey)) {
            return;
        }
        if (! $packageManager->isPackageAvailable($packageKey)) {
            $packageManager->scanAvailablePackages();
        }
        if (! $packageManager->isPackageActive($packageKey)) {
            $packageManager->activatePackage($packageKey);
        }
    }


    /**
     * Registers some low level class overrides.
     *
     * Note: I use these overrides ONLY to implement new hooks into TYPO3
     * If there will be a better alternative or even a hook that shows up in a
     * future version I will be more than happy to remove those overrides...
     */
    protected function applyCoreModding(): void
    {
        ClassOverrideGenerator::registerOverride(Bootstrap::class, ExtendedBootstrap::class);
        ClassOverrideGenerator::registerOverride(ReflectionService::class, ExtendedReflectionService::class);
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
    }


    /**
     * Registers our custom exception handler that wraps the registered
     * exception handler to emit events when an error/exception occurred
     */
    protected function registerErrorHandlerAdapter(): void
    {
        // Register production exception handler
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']
            !== ProductionExceptionHandler::class) {
            ProductionExceptionHandler::__setDefaultExceptionHandler((string)$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']
                = ProductionExceptionHandler::class;
        }

        // Register debug exception handler
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']
            !== DebugExceptionHandler::class) {
            DebugExceptionHandler::__setDefaultExceptionHandler((string)$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']
                = DebugExceptionHandler::class;
        }
    }

    /**
     * Creates the typo container instance and sets up the default configuration
     */
    protected function setupContainer(): void
    {
        // Register our overwrite implementations
        $this->container->setClassFor(
            ExtendedCacheManager::class,
            CacheManager::class
        );

    }

    /**
     * Is used to bin our internal event subscribers to the event bus
     */
    protected function bindInternalEvents(): void
    {
        // Always add those
        $this->eventBus
            ->addLazySubscriber(TempFs::class, static function () {
                return TempFs::makeInstance('');
            })
            ->addLazySubscriber(TypoScriptService::class)
            ->addLazySubscriber(PidTcaFilter::class)
            ->addLazySubscriber(ExtConfigService::class);

        // Backend only events
        if ($this->context->Env()->isBackend()) {
            $this->eventBus
                ->addLazySubscriber(BackendPreviewService::class)
                ->addLazySubscriber(DataHandlerActionService::class)
                ->addLazySubscriber(CustomElementContextFilter::class);

            // Register form engine addons/patches
            $this->eventBus
                ->addLazySubscriber(DbBaseIdApplier::class)
                ->addLazySubscriber(GroupElementsCanTriggerReloadApplier::class)
                ->addLazySubscriber(FieldDefaultAndPlaceholderTranslationApplier::class)
                ->addLazySubscriber(FixFlexFormSectionDefinitionApplier::class)
                ->addLazySubscriber(FixSectionToggleStateApplier::class)
                ->addLazySubscriber(FalFileBaseDirApplier::class);
        }

        // Make sure the exception handler is registered correctly
        $this->eventBus->addListener(
            ExtLocalConfLoadedEvent::class,
            function () {
                $this->registerErrorHandlerAdapter();
            },
            ['priority' => -500]
        );

        // Register our own ext config class
        betterExtConfig(
            'laborDigital.typo3_better_api',
            BetterApiExtConfig::class,
            ['before' => ['first', 'last']]
        );
    }

    /**
     * Register some of our cache configurations
     */
    protected function applyCacheConfiguration(): void
    {
        $cc                       = &$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'];
        $cc['ba_cache_pageBased'] = [
            'frontend' => VariableFrontend::class,
            'backend'  => Typo3DatabaseBackend::class,
            'options'  => [
                'compression' => true,
            ],
            'groups'   => ['pages'],
        ];
        $cc['ba_cache_frontend']  = [
            'frontend' => VariableFrontend::class,
            'backend'  => Typo3DatabaseBackend::class,
            'options'  => [
                'compression' => true,
            ],
            'groups'   => ['pages'],
        ];
        $cc['ba_cache_general']   = [
            'frontend' => VariableFrontend::class,
            'backend'  => Typo3DatabaseBackend::class,
            'options'  => [
                'compression'     => true,
                'defaultLifetime' => 0,
            ],
            'groups'   => ['system'],
        ];
    }

    /**
     * Adds our form engine nodes to the configuration
     */
    protected function addFormEngineNodes(): void
    {
        // Ignore if we are not in backend mode
        if (! $this->context->Env()->isBackend()) {
            return;
        }
        $nodeRegistry
            = &$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'];

        // Register the custom element node
        $nodeRegistry['betterApiCustomElement'] = [
            'nodeName' => 'betterApiCustomElement',
            'priority' => 40,
            'class'    => CustomElementNode::class,
        ];
        // Register the custom wizard node
        $nodeRegistry['betterApiCustomWizard'] = [
            'nodeName' => 'betterApiCustomWizard',
            'priority' => 40,
            'class'    => CustomWizardNode::class,
        ];
        // Register our custom slug node
        $nodeRegistry['betterApiPathSegmentSlug'] = [
            'nodeName' => 'betterApiPathSegmentSlug',
            'priority' => 40,
            'class'    => PathSegmentSlugElementNode::class,
        ];
    }

    /**
     * Serves as compatibility layer with helhum's console package
     */
    protected function applyHelhumConsoleCompatibility(): void
    {
        // Check if we require the compatibility layer
        if (php_sapi_name() !== 'cli') {
            return;
        }
        if (! class_exists(Kernel::class)) {
            return;
        }
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $found = false;
        foreach ($trace as $step) {
            if (! isset($step['file']) || ! is_string($step['file'])
                || stripos($step['file'], 'typo3-console') === false) {
                continue;
            }
            $found = true;
            break;
        }
        if (! $found) {
            return;
        }

        // Register the extended scripts class
        ClassOverrideGenerator::registerOverride(
            Scripts::class,
            ExtendedScripts::class
        );
    }
}