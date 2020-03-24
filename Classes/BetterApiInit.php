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
 * Last modified: 2020.03.21 at 20:48
 */

namespace LaborDigital\Typo3BetterApi;

use Composer\Autoload\ClassLoader;
use Doctrine\DBAL\Driver\Mysqli\MysqliConnection;
use Doctrine\DBAL\Driver\Mysqli\MysqliStatement;
use Exception;
use Helhum\Typo3Console\Core\Booting\Scripts;
use Helhum\Typo3Console\Core\Kernel;
use Kint\Kint;
use Kint\Parser\BlacklistPlugin;
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
use LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection\LazyConstructorInjectionHook;
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
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedReflectionService;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\ExtendedSiteConfiguration;
use LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\Typo3Console\ExtendedScripts;
use LaborDigital\Typo3BetterApi\CoreModding\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\Typo3BetterApi\CoreModding\FailsafeWrapper;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbService;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceInterface;
use LaborDigital\Typo3BetterApi\Error\DebugExceptionHandler;
use LaborDigital\Typo3BetterApi\Error\ProductionExceptionHandler;
use LaborDigital\Typo3BetterApi\Event\Dispatcher\TypoDispatcher;
use LaborDigital\Typo3BetterApi\Event\Events\AfterExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\BootstrapFailsafeDefinitionEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ClassSchemaFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\InitEvent;
use LaborDigital\Typo3BetterApi\Event\Events\InitInstanceFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\LoadExtLocalConfIfTcaIsRequiredWithoutItEvent;
use LaborDigital\Typo3BetterApi\Event\Events\RegisterRuntimePackagesEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\BootstrapContainerFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\CacheManagerCreatedEvent;
use LaborDigital\Typo3BetterApi\Event\ListenerProvider\TypoListenerProvider;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use LaborDigital\Typo3BetterApi\ExtConfig\Builtin\BetterApiExtConfig;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigService;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
use LaborDigital\Typo3BetterApi\Kint\LazyLoadingPlugin;
use LaborDigital\Typo3BetterApi\Kint\TypoInstanceTypePlugin;
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
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\QueryObjectModelFactory;
use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use TYPO3\CMS\Extbase\Reflection\ClassSchema;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class BetterApiInit {
	
	/**
	 * True if the init method was executed,
	 * this will block further executions
	 * @var bool
	 */
	protected static $initStarted = FALSE;
	
	/**
	 * True when the init is complete
	 * @var bool
	 */
	protected static $initComplete = FALSE;
	
	/**
	 * Holds the typo3 event bus if we are ready for it
	 * @var EventBusInterface
	 */
	protected $eventBus;
	
	/**
	 * The container instance
	 * @var TypoContainer
	 */
	protected $container;
	
	/**
	 * The app context object
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
	 */
	protected $context;
	
	/**
	 * BetterApiInit constructor.
	 *
	 * @param \Neunerlei\EventBus\EventBusInterface $eventBus
	 */
	protected function __construct(EventBusInterface $eventBus) {
		$this->eventBus = $eventBus;
	}
	
	/**
	 * This stage is executed inside the composerAutoloadInclude.php
	 * which is, as the name suggests, loaded as "autoload file" via composer.
	 *
	 * It contains mostly root level changes that are required before any other class is loaded
	 *
	 * @param \Composer\Autoload\ClassLoader $composerClassLoader
	 */
	public static function init(ClassLoader $composerClassLoader) {
		if (static::$initStarted) return;
		static::$initStarted = TRUE;
		
		// Create the event bus
		$eventBus = new TypoEventBus();
		$listenerProvider = new TypoListenerProvider();
		$eventBus->setConcreteListenerProvider($listenerProvider);
		$dispatcher = new TypoDispatcher($listenerProvider);
		$eventBus->setConcreteDispatcher($dispatcher);
		TypoEventBus::__setInstance($eventBus);
		
		// Load the global events
		if (isset($GLOBALS["BETTER_API_LOW_LEVEL_EVENTS"]) && is_array($GLOBALS["BETTER_API_LOW_LEVEL_EVENTS"]))
			foreach ($GLOBALS["BETTER_API_LOW_LEVEL_EVENTS"] as $event => $handler)
				$eventBus->addListener($event, $handler);
		unset($GLOBALS["BETTER_API_LOW_LEVEL_EVENTS"]);
		unset($event);
		unset($handler);
		
		// Register the override generator's auto-loader
		ClassOverrideGenerator::init($composerClassLoader);
		
		// Create myself
		$self = new static($eventBus);
		
		// Allow replacement of this object
		$e = new InitInstanceFilterEvent($self, $eventBus);
		$eventBus->dispatch($e);
		$self = $e->getInitInstance();
		
		// Apply the first step of our bootstrap
		$self->applyCoreModding();
		$self->applyDebuggerConfig();
		include __DIR__ . "/functions.php";
		
		// Check if we need to apply the compatibility script for helhum's console
		if (php_sapi_name() === "cli")
			$self->applyHelhumConsoleCompatibility();
		
		// TYPO3 9
		// When the Cache Manager is instantiated we create the extBase cache
		// to prevent deprecation warnings...
		$eventBus->addListener(CacheManagerCreatedEvent::class, function (CacheManagerCreatedEvent $e) use ($self) {
			$e->getCacheManager()->setCacheConfigurations($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']);
			$e->getCacheManager()->getCache("extbase_reflection");
			$self->registerErrorHandlerAdapter();
		});
		
		// Handle the failsafe state
		$eventBus->addListener(BootstrapFailsafeDefinitionEvent::class, function (BootstrapFailsafeDefinitionEvent $e) {
			FailsafeWrapper::$isFailsafe = $e->isFailsafe();
		});
		
		// Activate myself
		$eventBus->addListener(RegisterRuntimePackagesEvent::class, function (RegisterRuntimePackagesEvent $event) use ($self) {
			$self->forceSelfActivation($event->getPackageManager());
		});
		$eventBus->addListener(RegisterRuntimePackagesEvent::class, function (RegisterRuntimePackagesEvent $event) use ($self) {
			$self->activateHookExtension($event->getPackageManager());
		}, ["priority" => -500]);
		
		// Do remaining bootstrapping
		$eventBus->addListener(AfterExtLocalConfLoadedEvent::class, function () use ($self) {
			$self->setupContainer();
			$self->dispatchInitEvent();
			$self->setupTypoContext();
			$self->applyCacheConfiguration();
			$self->addFormEngineNodes();
			$self->bindInternalEvents();
			static::$initComplete = TRUE;
		}, ["priority" => 100]);
		
		// Make sure to destroy the context instance after the bootstrap finished
		// We need this for v9 because we require the Context class, before it is initialized
		// in the Application classes. This will lead to unexpected behaviour in the backend and edge cases.
		$eventBus->addListener(BootstrapContainerFilterEvent::class, function () use ($self) {
			GeneralUtility::removeSingletonInstance(Context::class, $self->context->getRootContext());
			ObjectContainerAdapter::removeSingleton($self->container->get(Container::class), Context::class);
			$self->context->__unlinkContext();
		});
		
		// Register a fallback that loads the ext localconf files if the install tool applies some TCA related checks
		$eventBus->addListener(LoadExtLocalConfIfTcaIsRequiredWithoutItEvent::class, function () {
			// Ignore if the init is completed
			if (BetterApiInit::isComplete()) return;
			
			// Load the ext local conf files
			ExtensionManagementUtility::loadExtLocalconf(FALSE);
		});
		
	}
	
	/**
	 * Returns true if the init is completed, false if it is still running
	 * @return bool
	 */
	public static function isComplete(): bool {
		return static::$initComplete;
	}
	
	/**
	 * Makes sure that this package is always active in the package states
	 *
	 * @param \TYPO3\CMS\Core\Package\PackageManager $packageManager
	 */
	protected function forceSelfActivation(PackageManager $packageManager): void {
		$packageKey = "typo3_better_api";
		if ($packageManager->isPackageActive($packageKey)) return;
		if (!$packageManager->isPackageAvailable($packageKey))
			$packageManager->scanAvailablePackages();
		if (!$packageManager->isPackageActive($packageKey))
			$packageManager->activatePackage($packageKey);
	}
	
	/**
	 * Activates the hook extension that should be always the last in the list of extension.
	 * This is important because we want all other extensions to be able to do their stuff, before we do our stuff :)
	 *
	 * @param \TYPO3\CMS\Core\Package\PackageManager $packageManager
	 */
	protected function activateHookExtension(PackageManager $packageManager): void {
		// Make sure the package was never enabled
		$packageKey = "typo3_better_api_hook";
		if ($packageManager->isPackageActive($packageKey))
			$packageManager->deactivatePackage($packageKey);
		
		// Create the package and register the base path
		$package = new Package($packageManager, $packageKey, __DIR__ . "/../HookExtension/" . $packageKey . "/");
		$adapter = new class extends PackageManager {
			public function registerHookPackage(PackageManager $packageManager, PackageInterface $package) {
				// Register a new base path
				$packageManager->packagesBasePaths[$package->getPackageKey()] = $package->getPackagePath();
				
				// Activate the package
				$packageManager->activatePackageDuringRuntime($package->getPackageKey());
			}
		};
		$adapter->registerHookPackage($packageManager, $package);
	}
	
	/**
	 * Registers some low level class overrides.
	 *
	 * Note: I use these overrides ONLY to implement new hooks into TYPO3
	 * If there will be a better alternative or even a hook that shows up in a future version
	 * I will be more than happy to remove those overrides...
	 */
	protected function applyCoreModding() {
		ClassOverrideGenerator::registerOverride(Bootstrap::class, ExtendedBootstrap::class);
		ClassOverrideGenerator::registerOverride(ReflectionService::class, ExtendedReflectionService::class);
		ClassOverrideGenerator::registerOverride(CacheManager::class, ExtendedCacheManager::class);
		ClassOverrideGenerator::registerOverride(LocalizationUtility::class, ExtendedLocalizationUtility::class);
		ClassOverrideGenerator::registerOverride(DataHandler::class, ExtendedDataHandler::class);
		ClassOverrideGenerator::registerOverride(NodeFactory::class, ExtendedNodeFactory::class);
		ClassOverrideGenerator::registerOverride(DataMapper::class, ExtendedDataMapper::class);
		ClassOverrideGenerator::registerOverride(SiteConfiguration::class, ExtendedSiteConfiguration::class);
		
		// Make sure we don't crash legacy code when changing the language service
		ClassOverrideGenerator::registerOverride(LanguageService::class, ExtendedLanguageService::class);
		if (!class_exists(LanguageService::class, FALSE) && !class_exists(\TYPO3\CMS\Lang\LanguageService::class, FALSE))
			class_alias(LanguageService::class, \TYPO3\CMS\Lang\LanguageService::class);
	}
	
	/**
	 * Apply the configuration for the labor/dbg package
	 */
	protected function applyDebuggerConfig() {
		if (function_exists("dbgConfig") && defined("_DBG_CONFIG_LOADED")) {
			// Register our Plugins
			Kint::$plugins[] = LazyLoadingPlugin::class;
			Kint::$plugins[] = TypoInstanceTypePlugin::class;
			
			// Register pre hook to fix broken typo3 iframe
			$recursion = FALSE;
			dbgConfig("postHooks", function () use (&$recursion) {
				if ($recursion) return;
				$recursion = TRUE;
				try {
					if ((defined('TYPO3_MODE') && TYPO3_MODE === 'BE') && php_sapi_name() !== "cli") {
						if (Kint::$mode_default !== Kint::MODE_RICH) return;
						flush();
						echo <<<HTML
							<script type="text/javascript">
							setTimeout(function () {
								document.getElementsByTagName("html")[0].setAttribute("style", "height:100vh; overflow:auto");
								document.getElementsByTagName("body")[0].setAttribute("style", "height:100vh; overflow:auto");
								}, 50);
							</script>
HTML;
						flush();
					}
				} catch (Exception $e) {
					// Ignore this...
				}
				$recursion = FALSE;
			});
			
			// Register blacklisted objects to prevent kint from breaking apart ...
			if (class_exists(BlacklistPlugin::class)) {
				BlacklistPlugin::$shallow_blacklist[] = ReflectionService::class;
				BlacklistPlugin::$shallow_blacklist[] = ObjectManager::class;
				BlacklistPlugin::$shallow_blacklist[] = DataMapper::class;
				BlacklistPlugin::$shallow_blacklist[] = PersistenceManager::class;
				BlacklistPlugin::$shallow_blacklist[] = QueryObjectModelFactory::class;
				BlacklistPlugin::$shallow_blacklist[] = ContentObjectRenderer::class;
				BlacklistPlugin::$shallow_blacklist[] = TypoEventBus::class;
				BlacklistPlugin::$shallow_blacklist[] = QueryResult::class;
				BlacklistPlugin::$shallow_blacklist[] = MysqliConnection::class;
				BlacklistPlugin::$shallow_blacklist[] = MysqliStatement::class;
			}
		}
	}
	
	/**
	 * Registers our custom exception handler that wraps the registered exception handler
	 * to emit events when an error/exception occurred
	 */
	protected function registerErrorHandlerAdapter() {
		
		// Register production exception handler
		if ($GLOBALS["TYPO3_CONF_VARS"]["SYS"]["productionExceptionHandler"] !== ProductionExceptionHandler::class) {
			ProductionExceptionHandler::__setDefaultExceptionHandler((string)$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["productionExceptionHandler"]);
			$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["productionExceptionHandler"] = ProductionExceptionHandler::class;
		}
		
		// Register debug exception handler
		if ($GLOBALS["TYPO3_CONF_VARS"]["SYS"]["debugExceptionHandler"] !== DebugExceptionHandler::class) {
			DebugExceptionHandler::__setDefaultExceptionHandler((string)$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["debugExceptionHandler"]);
			$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["debugExceptionHandler"] = DebugExceptionHandler::class;
		}
		
	}
	
	/**
	 * Creates the typo container instance and sets up the default configuration
	 */
	protected function setupContainer() {
		
		// Create the container instance
		$this->container = TypoContainer::getInstance();
		
		// Register our overwrite implementations
		$this->container->setClassFor(ExtendedCacheManager::class, CacheManager::class);
		
		// Register implementations
		$this->container->setClassFor(DbServiceInterface::class, DbService::class);
		$this->container->setClassFor(EventBusInterface::class, TypoEventBus::class);
		$this->container->setClassFor(TypoContainerInterface::class, TypoContainer::class);
		
		// Register existing instances
		$this->container->set(TypoEventBus::class, $this->eventBus);
		
		// Inject the container and the signal slot dispatcher into the event dispatcher
		$this->addSignalSlotDispatcherToEventDispatcher();
		
		// Register the lazy constructor injection hook
		$this->eventBus->addLazySubscriber(LazyConstructorInjectionHook::class);
		$this->eventBus->dispatch(new ClassSchemaFilterEvent(new ClassSchema(LazyConstructorInjectionHook::class), LazyConstructorInjectionHook::class));
	}
	
	/**
	 * Adds additional dependencies to the event dispatcher so it is linked
	 * with the signal slot dispatcher
	 */
	protected function addSignalSlotDispatcherToEventDispatcher() {
		$signalSlotDispatcher = $this->container->get(Dispatcher::class);
		
		// Update the event bus itself
		$this->eventBus->setContainer($this->container);
		
		// Update the listener provider
		$listenerProvider = $this->eventBus->getConcreteListenerProvider();
		if ($listenerProvider instanceof TypoListenerProvider)
			$listenerProvider->setHighLevelDependencies($signalSlotDispatcher, $this->container);
		
		// Update the dispatcher instance
		$dispatcher = $this->eventBus->getConcreteDispatcher();
		if ($dispatcher instanceof TypoDispatcher)
			$dispatcher->setSignalSlotDispatcher($signalSlotDispatcher);
	}
	
	/**
	 * Dispatch the init event
	 */
	protected function dispatchInitEvent() {
		$this->eventBus->dispatch(new InitEvent());
	}
	
	/**
	 * Creates the app context object
	 */
	protected function setupTypoContext() {
		$this->context = $this->container->get(TypoContext::class);
	}
	
	/**
	 * Is used to bin our internal event subscribers to the event bus
	 */
	protected function bindInternalEvents() {
		// Always add those
		$this->eventBus
			->addLazySubscriber(TempFs::class, function () {
				return TempFs::makeInstance("");
			})
			->addLazySubscriber(TypoScriptService::class)
			->addLazySubscriber(PidTcaFilter::class)
			->addLazySubscriber(ExtConfigService::class);
		
		// Backend only events
		if ($this->context->getEnvAspect()->isBackend()) {
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
		$this->eventBus->addListener(ExtLocalConfLoadedEvent::class, function () {
			$this->registerErrorHandlerAdapter();
		}, ["priority" => -500]);
		
		// Register our own ext config class
		betterExtConfig("laborDigital.typo3_better_api", BetterApiExtConfig::class, ["before" => ["first", "last"]]);
	}
	
	/**
	 * Register some of our cache configurations
	 */
	protected function applyCacheConfiguration() {
		$cc = &$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["caching"]["cacheConfigurations"];
		$cc["ba_cache_pageBased"] = [
			"frontend" => VariableFrontend::class,
			"backend"  => Typo3DatabaseBackend::class,
			"options"  => [
				"compression" => TRUE,
			],
			"groups"   => ["pages"],
		];
		$cc["ba_cache_frontend"] = [
			"frontend" => VariableFrontend::class,
			"backend"  => Typo3DatabaseBackend::class,
			"options"  => [
				"compression" => TRUE,
			],
			"groups"   => ["pages"],
		];
		$cc["ba_cache_general"] = [
			"frontend" => VariableFrontend::class,
			"backend"  => Typo3DatabaseBackend::class,
			"options"  => [
				"compression"     => TRUE,
				"defaultLifetime" => 0,
			],
			"groups"   => ["system"],
		];
	}
	
	/**
	 * Adds our form engine nodes to the configuration
	 */
	protected function addFormEngineNodes() {
		// Ignore if we are not in backend mode
		if (!$this->context->getEnvAspect()->isBackend()) return;
		$nodeRegistry = &$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["formEngine"]["nodeRegistry"];
		
		// Register the custom element node
		$nodeRegistry["betterApiCustomElement"] = [
			"nodeName" => "betterApiCustomElement",
			"priority" => 40,
			"class"    => CustomElementNode::class,
		];
		// Register the custom wizard node
		$nodeRegistry["betterApiCustomWizard"] = [
			"nodeName" => "betterApiCustomWizard",
			"priority" => 40,
			"class"    => CustomWizardNode::class,
		];
		// Register our custom slug node
		$nodeRegistry["betterApiPathSegmentSlug"] = [
			"nodeName" => "betterApiPathSegmentSlug",
			"priority" => 40,
			"class"    => PathSegmentSlugElementNode::class,
		];
	}
	
	/**
	 * Serves as compatibility layer with helhum's console package
	 */
	protected function applyHelhumConsoleCompatibility() {
		// Check if we require the compatibility layer
		if (php_sapi_name() !== "cli") return;
		if (!class_exists(Kernel::class)) return;
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$found = FALSE;
		foreach ($trace as $step) {
			if (!isset($step["file"]) || !is_string($step["file"]) || stripos($step["file"], "typo3-console") === FALSE) continue;
			$found = TRUE;
			break;
		}
		if (!$found) return;
		
		// Register the extended scripts class
		ClassOverrideGenerator::registerOverride(Scripts::class, ExtendedScripts::class);
	}
}