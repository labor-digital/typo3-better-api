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
 * Last modified: 2020.03.21 at 21:19
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase;


use LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewServiceInterface;
use LaborDigital\Typo3BetterApi\Event\Events\ExtLocalConfLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtTablesLoadedEvent;
use LaborDigital\Typo3BetterApi\Event\Events\TcaCompletelyLoadedEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\Helper\CTypeRegistrationTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\ElementConfig;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module\ModuleConfigGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurationInterface;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

/**
 * Class ExtBaseOption
 *
 * Can be used to add extBase plugins and backend modules
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase
 */
class ExtBaseOption extends AbstractExtConfigOption {
	use CTypeRegistrationTrait;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewServiceInterface
	 */
	protected $lazyBackendPreviewService;
	
	/**
	 * ExtBaseOptions constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewServiceInterface $lazyBackendPreviewService
	 */
	public function __construct(BackendPreviewServiceInterface $lazyBackendPreviewService) {
		$this->lazyBackendPreviewService = $lazyBackendPreviewService;
	}
	
	/**
	 * @inheritDoc
	 */
	public function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(ExtLocalConfLoadedEvent::class, "__applyExtLocalConf");
		$subscription->subscribe(ExtTablesLoadedEvent::class, "__applyExtTables");
		$subscription->subscribe(TcaCompletelyLoadedEvent::class, "__applyTcaOverrides", ["priority" => 400]);
	}
	
	/**
	 * Registers a new module to the typo3 backend
	 *
	 * @param string      $configuratorClass The name of the configuration class.
	 *                                       The class has to implement the ModuleConfigurationInterface
	 * @param string|null $pluginName        The unique plugin name to use for this backend module. The unique name of
	 *                                       the plugin that should be registered. If left empty the name is
	 *                                       automatically build based on the class name
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module\ModuleConfigurationInterface
	 */
	public function registerBackendModule(string $configuratorClass, ?string $pluginName = NULL): ExtBaseOption {
		if (empty($pluginName)) $pluginName = $this->makePluginNameFromConfigClass($configuratorClass);
		return $this->addRegistrationToCachedStack("modules", $pluginName, $configuratorClass);
	}
	
	/**
	 * Can be used to modify a TYPO3 backend module.
	 *
	 * Please note: You can only modify modules that have been registered using registerBackendModule()!
	 *
	 * @param string      $configuratorClass The name of the override class.
	 *                                       The class has to implement the ModuleConfigurationInterface
	 * @param string|null $pluginName        The unique plugin name of backend module to modify. If left empty the
	 *                                       name is automatically build based on the class name
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module\ModuleConfigurationInterface
	 */
	public function registerBackendModuleOverride(string $configuratorClass, ?string $pluginName = NULL): ExtBaseOption {
		if (empty($pluginName)) $pluginName = $this->makePluginNameFromConfigClass($configuratorClass);
		return $this->addOverrideToCachedStack("modules", $pluginName, $configuratorClass);
	}
	
	/**
	 * Registers a new ext base plugin / content element configuration
	 *
	 * @param string      $configuratorClass The name of the configuration class. Has to implement the
	 *                                       PluginConfigurationInterface.
	 * @param string|null $pluginName        The unique name of the plugin that should be registered. If left empty the
	 *                                       name is automatically build based on the class name
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurationInterface
	 */
	public function registerPlugin(string $configuratorClass, ?string $pluginName = NULL): ExtBaseOption {
		if (empty($pluginName)) $pluginName = $this->makePluginNameFromConfigClass($configuratorClass);
		return $this->addRegistrationToCachedStack("plugins", $pluginName, $configuratorClass);
	}
	
	/**
	 * Similar to registerPlugin() but registers all plugin definitions in a directory at once.
	 *
	 * @param string $directory The path to the directory to add. Either as absolute path or as EXT:... path
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption
	 */
	public function registerPluginDirectory(string $directory = "EXT:{{extkey}}/Classes/Controller"): ExtBaseOption {
		return $this->addDirectoryToCachedStack("plugins", $directory, function (string $className) {
			// Check if the class implements the correct interface
			return in_array(PluginConfigurationInterface::class, class_implements($className));
		}, function (string $className) {
			return $this->makePluginNameFromConfigClass($className);
		});
	}
	
	/**
	 * Registers an override for an ext base plugin / content element configuration
	 *
	 * @param string      $configuratorClass    The name of the configuration class. Has to implement the
	 *                                          PluginConfigurationInterface.
	 * @param string|null $pluginName           The unique name of the plugin that should be registered. If left empty
	 *                                          the name is automatically build based on the class name
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption
	 */
	public function registerPluginOverride(string $configuratorClass, ?string $pluginName = NULL): ExtBaseOption {
		if (empty($pluginName)) $pluginName = $this->makePluginNameFromConfigClass($configuratorClass);
		return $this->addOverrideToCachedStack("plugins", $pluginName, $configuratorClass);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __applyExtLocalConf() {
		// Load the plugin config
		$pluginConfig = $this->getPluginConfig();
		
		// Register plugins
		foreach ($pluginConfig->configurePluginArgs as $args)
			ExtensionUtility::configurePlugin(...$args);
		
		
		// Register plugin typoScript
		$this->context->TypoScript->addSetup($pluginConfig->typoScript, [
			"title" => "BetterApi - ExtBase Plugin Templates",
		]);
		
		// Register module typoScript
		$this->context->TypoScript->addSetup($this->getModuleConfig()->typoScript, [
			"title" => "BetterApi - ExtBase Module Templates",
		]);
		
		// This is only required when we are in the backend
		if ($this->context->TypoContext->getEnvAspect()->isBackend()) {
			// Register plugin wizard icons
			$this->context->TypoScript->addPageTsConfig($pluginConfig->tsConfig);
			$iconRegistry = $this->context->getInstanceOf(IconRegistry::class);
			foreach ($pluginConfig->iconDefinitionArgs as $args)
				$iconRegistry->registerIcon(...$args);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function __applyExtTables() {
		// This is only required when we are in the backend
		if ($this->context->TypoContext->getEnvAspect()->isBackend()) {
			// Load the plugin config
			$pluginConfig = $this->getPluginConfig();
			
			// Register data handler action handlers
			foreach ($pluginConfig->dataHandlerActionHandlers as $table => $actions)
				foreach ($actions as $action => $handlers)
					foreach ($handlers as $handler)
						$this->context->DataHandlerActions->registerActionHandler($table, $action, ...$handler);
			
			// Register backend preview and label renderers
			foreach ($pluginConfig->backendPreviewRenderers as $args)
				$this->lazyBackendPreviewService->registerBackendPreviewRenderer(...$args);
			foreach ($pluginConfig->backendListLabelRenderers as $args)
				$this->lazyBackendPreviewService->registerBackendListLabelRenderer(...$args);
			
			// Register plugin flex forms
			foreach ($pluginConfig->addPiFlexFormArgs as $args)
				ExtensionManagementUtility::addPiFlexFormValue(...$args);
			
			// Register plugins
			foreach ($pluginConfig->registerPluginArgs as $args)
				ExtensionUtility::registerPlugin(...$args);
			
			// Register modules
			foreach ($this->getModuleConfig()->registerModuleArgs as $args)
				ExtensionUtility::registerModule(...$args);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function __applyTcaOverrides() {
		// Load the plugin config
		$pluginConfig = $this->getPluginConfig();
		
		// Inject our cType entries into the tt content tca
		$this->registerCTypesForElements($GLOBALS["TCA"], $pluginConfig->cTypeEntries);
		
		// Register flex forms
		foreach ($pluginConfig->flexFormPlugins as $plugin) {
			$flexFormPath = ["TCA", "tt_content", "types", "list", "subtypes_addlist", $plugin];
			$val = Arrays::getPath($GLOBALS, $flexFormPath, "");
			if (!empty($val)) {
				if (is_string($val) && stripos($val, "pi_flexform") === FALSE)
					$val = rtrim($val, ", ") . ",pi_flexform";
			} else $val = "pi_flexform";
			$GLOBALS["TCA"] = Arrays::setPath($GLOBALS, $flexFormPath, $val)["TCA"];
		}
	}
	
	/**
	 * Returns the module configuration object
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\ElementConfig
	 */
	protected function getModuleConfig(): ElementConfig {
		return $this->getCachedStackValueOrRun("modules", ModuleConfigGenerator::class);
	}
	
	/**
	 * Returns the module configuration object
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\ElementConfig
	 */
	protected function getPluginConfig(): ElementConfig {
		return $this->getCachedStackValueOrRun("plugins", PluginConfigGenerator::class);
	}
	
	/**
	 * Internal helper that is used if there was no plugin name given.
	 * In that case we will use the config class as naming base and try to extract the plugin name out of it.
	 *
	 * We will automatically strip suffixes like module, plugin, ext, config, configuration, controller and override(s)
	 * from the base name before we convert it into a plugin name
	 *
	 * @param string $configClass
	 *
	 * @return string
	 */
	protected function makePluginNameFromConfigClass(string $configClass): string {
		$baseName = Path::classBasename($configClass);
		$baseName = preg_replace("~(controller)?(overrides?)?$~si", "", $baseName);
		return Inflector::toCamelCase($baseName);
	}
}