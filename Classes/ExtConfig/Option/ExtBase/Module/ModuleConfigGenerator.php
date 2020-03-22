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
 * Last modified: 2020.03.18 at 19:38
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module;


use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedStackGeneratorInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\AbstractConfigGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\ElementConfig;
use LaborDigital\Typo3BetterApi\Translation\TranslationService;
use Neunerlei\Arrays\Arrays;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\TinyTimy\DateTimy;

class ModuleConfigGenerator extends AbstractConfigGenerator implements CachedStackGeneratorInterface {
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
	 */
	protected $translationService;
	
	/**
	 * ExtBaseModuleConfigGenerator constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Translation\TranslationService $translationService
	 */
	public function __construct(TranslationService $translationService) {
		$this->translationService = $translationService;
	}
	
	/**
	 * @inheritDoc
	 */
	public function generate(array $stack, ExtConfigContext $context, array $additionalData, $option) {
		// Prepare temporary storage
		$tmp = new class {
			public $typoScript         = [];
			public $registerModuleArgs = [];
		};
		
		// Loop through the stack
		foreach ($stack as $pluginName => $data) {
			$context->runWithFirstCachedValueDataScope($data, function () use ($context, $pluginName, $data, $tmp) {
				// Create the configurator
				$configurator = $context->getInstanceOf(ModuleConfigurator::class, [$pluginName, $context]);
				
				// Loop through the stack
				$context->runWithCachedValueDataScope($data, function (string $configClass) use ($context, $configurator, $pluginName) {
					if (!in_array(ModuleConfigurationInterface::class, class_implements($configClass)))
						throw new ExtConfigException("Invalid configuration class: $configClass for module: $pluginName. It has to implement the correct interface: " . ModuleConfigurationInterface::class);
					call_user_func([$configClass, "configureModule"], $configurator, $context);
				});
				
				// Build the parts
				$this->makeTranslationFileIfRequired($configurator, $context);
				$tmp->registerModuleArgs[] = $this->makeRegisterModuleArgs($configurator, $context);
				$tmp->typoScript[] = $this->makeTemplateDefinition("module", $configurator);
			});
		}
		
		// Create a new config object
		$config = $context->getInstanceOf(ElementConfig::class);
		$config->registerModuleArgs = $tmp->registerModuleArgs;
		$config->typoScript = implode(PHP_EOL . PHP_EOL, $tmp->typoScript);
		unset($tmp);
		
		// Done
		return $config;
	}
	
	/**
	 * Makes sure the module translation file exists or creates a new one
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module\ModuleConfigurator $configurator
	 * @param ExtConfigContext                                                                $context
	 */
	protected function makeTranslationFileIfRequired(ModuleConfigurator $configurator, ExtConfigContext $context) {
		
		// Check if the file exists
		$translationFile = $context->TypoContext->getPathAspect()->typoPathToRealPath($configurator->getTranslationFile());
		if (file_exists($translationFile)) return;
		
		// Check if we got a context
		if ($this->translationService->hasContext($configurator->getTranslationFile())) {
			$translationFile = $this->translationService->getContextFile($configurator->getTranslationFile(), TRUE);
			$configurator->setTranslationFile($translationFile);
			return;
		}
		
		// Create new translation file
		$definition = [
			[
				"tag"      => "xliff",
				"@version" => "1.0",
				[
					"tag"              => "file",
					"@source-language" => "en",
					"@datatype"        => "plaintext",
					"@original"        => "messages",
					"@date"            => (new DateTimy())->format("Y-m-d\TH:i:s\Z"),
					"@product-name"    => $context->getExtKey(),
					[
						"tag"     => "header",
						"content" => "",
					], [
						"tag" => "body",
						[
							"tag" => "trans-unit",
							"@id" => "mlang_tabs_tab",
							[
								"tag"     => "source",
								"content" => Inflector::toHuman($context->getExtKey()) . ": " . Inflector::toHuman($configurator->getPluginName()),
							],
						],
						[
							"tag" => "trans-unit",
							"@id" => "mlang_labels_tablabel",
							[
								"tag"     => "source",
								"content" => "A new and shiny module",
							],
						],
						[
							"tag" => "trans-unit",
							"@id" => "mlang_labels_tabdescr",
							[
								"tag"     => "source",
								"content" => "A new and shiny module",
							],
						],
					],
				],
			],
		];
		Fs::writeFile($translationFile, Arrays::dumpToXml($definition, TRUE));
	}
	
	/**
	 * Builds and returns the arguments that have to be passed to the "registerModule" method to add our module to the
	 * backend.
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Module\ModuleConfigurator $configurator
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                         $context
	 *
	 * @return array
	 * @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule()
	 */
	protected function makeRegisterModuleArgs(ModuleConfigurator $configurator, ExtConfigContext $context): array {
		return array_values([
			"extensionName"       => $context->getExtKeyWithVendor(),
			"mainModuleName"      => $configurator->getSection(),
			"subModuleName"       => $configurator->getModuleKey(),
			"position"            => $configurator->getPosition(),
			"controllerActions"   => $configurator->getActions(),
			"moduleConfiguration" => Arrays::merge(
				$configurator->getAdditionalOptions(),
				[
					"access" => implode(",", $configurator->getAccess()),
					"icon"   => $configurator->getIcon(),
					"labels" => $configurator->getTranslationFile(),
				]
			),
		]);
	}
}