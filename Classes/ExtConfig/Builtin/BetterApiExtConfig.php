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
 * Last modified: 2020.03.21 at 16:28
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Builtin;


use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin\BasicFieldPreset;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin\CustomElementPreset;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin\InputFieldPreset;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin\RelationPreset;
use LaborDigital\Typo3BetterApi\Event\EventConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend\BackendConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Core\CoreConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\ExtBaseOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Fluid\FluidConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\LinkAndPidOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Log\LogConfigOption;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset\FieldPresetApplierTraitGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\TableOption;
use LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionList;
use LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionTraitGenerator;
use LaborDigital\Typo3BetterApi\Middleware\RequestCollectorMiddleware;
use LaborDigital\Typo3BetterApi\Translation\FileSync\TranslationSyncCommand;
use LaborDigital\Typo3BetterApi\Translation\TranslationConfigOption;
use LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption;

class BetterApiExtConfig implements ExtConfigInterface, ExtConfigExtensionInterface {
	
	/**
	 * @inheritDoc
	 */
	public function configure(ExtConfigOptionList $configurator, ExtConfigContext $context) {
		
		// Register translation
		$configurator->translation()->registerContext("betterApi");
		
		// Register commands
		$configurator->backend()->registerCommand(TranslationSyncCommand::class);
		
		// Register middlewares
		$configurator->http()
			->registerMiddleware(RequestCollectorMiddleware::class, "frontend", [
				"after"  => "typo3/cms-frontend/site",
				"before" => "typo3/cms-frontend/base-redirect-resolver",
			])
			->registerMiddleware(RequestCollectorMiddleware::class, "backend", [
				"after" => "typo3/cms-backend/site-resolver",
			]);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function extendExtConfig(ExtConfigExtensionRegistry $extender, ExtConfigContext $context) {
		// Register extension handlers
		$extender->registerExtensionHandler(ExtConfigExtensionInterface::TYPE_FORM_FIELD_PRESET, $context->getInstanceOf(FieldPresetApplierTraitGenerator::class));
		$extender->registerExtensionHandler(ExtConfigExtensionInterface::TYPE_OPTION_LIST_ENTRY, $context->getInstanceOf(ExtConfigOptionTraitGenerator::class));
		
		// Register default presets
		$extender->registerFieldPreset(BasicFieldPreset::class);
		$extender->registerFieldPreset(CustomElementPreset::class);
		$extender->registerFieldPreset(InputFieldPreset::class);
		$extender->registerFieldPreset(RelationPreset::class);
		
		// Register default options
		$extender->registerOptionListEntry(LinkAndPidOption::class);
		$extender->registerOptionListEntry(EventConfigOption::class);
		$extender->registerOptionListEntry(TranslationConfigOption::class);
		$extender->registerOptionListEntry(TypoScriptConfigOption::class);
		$extender->registerOptionListEntry(CoreConfigOption::class);
		$extender->registerOptionListEntry(FluidConfigOption::class);
		$extender->registerOptionListEntry(BackendConfigOption::class);
		$extender->registerOptionListEntry(TableOption::class);
		$extender->registerOptionListEntry(ExtBaseOption::class);
		$extender->registerOptionListEntry(LogConfigOption::class);
		$extender->registerOptionListEntry(HttpConfigOption::class);
	}
	
	
}