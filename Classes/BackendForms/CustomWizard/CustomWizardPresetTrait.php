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
 * Last modified: 2020.03.19 at 03:01
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomWizard;


use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;

trait CustomWizardPresetTrait {
	
	/**
	 * This helper is quite similar to the CustomElementPresetTrait class but is used
	 * to create custom wizard definitions for your own wizards.
	 * It is mend to be used inside your own field preset, that validates and documents the possible options
	 * and passes them into this helper afterwards. It will take care of all the heavy lifting and class
	 * validation for you.
	 *
	 * @param AbstractFormField $field       The reference of the field you currently configure.
	 *                                       Typically $this->field
	 * @param ExtConfigContext  $context     The ext config context. Typically $this->context
	 * @param string            $wizardClass The class name of the custom wizard you want to register.
	 *                                       The class has to implement the CustomWizardInterface interface
	 * @param array             $options     Any options you want to specify for your custom wizard
	 *                                       Generic options on all wizards are:
	 *                                       - before array|string: A list of other wizards that should be displayed
	 *                                       after this wizard
	 *                                       - after array|string: A list of other wizards that should be displayed
	 *                                       before this wizard
	 *                                       - wizardId string: Can be used to manually set the wizard id.
	 *                                       If left empty the id will be automatically created.
	 *
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	protected function applyCustomWizardPreset(AbstractFormField $field, ExtConfigContext $context,
											   string $wizardClass, array $options = []): void {
		
		// Validate if the class exists
		if (!class_exists($wizardClass))
			throw new BackendFormException("Could not configure your field: " . $field->getId() . " to use the custom wizard with class: $wizardClass. Because the class does not exist!");
		if (!in_array(CustomWizardInterface::class, class_implements($wizardClass)))
			throw new BackendFormException("Could not configure your field: " . $field->getId() . " to use the custom wizard with class: $wizardClass. Because the class does not implement the required " . CustomWizardInterface::class . " interface!");
		
		// Prepare options
		$options = Options::make($options, [
			"wizardId" => [
				"type"    => "string",
				"default" => Inflector::toDashed(str_replace("\\", "-", $wizardClass)),
			],
			"before"   => [
				"type"    => ["string", "array"],
				"default" => [],
			],
			"after"    => [
				"type"    => ["string", "array"],
				"default" => [],
			],
		]);
		
		// Prepare ordering
		$before = $after = [];
		if (!empty($options["before"])) $before = $options["before"];
		if (is_string($before)) $before = [$before];
		if (!empty($options["after"])) $after = $options["after"];
		if (is_string($after)) $after = [$after];
		
		// Build the wizard configuration
		$config = [
			"fieldWizard" => [
				$options["wizardId"] => [
					"type"       => "betterApiCustomWizard",
					"renderType" => "betterApiCustomWizard",
					"options"    => [
						"customWizardClass" => $wizardClass,
						"fieldName"         => $field->getId(),
					],
					"before"     => $before,
					"after"      => $after,
				],
			],
		];
		$field->addConfig($config);
		
		// Run the field configuration
		call_user_func([$wizardClass, "configureField"], $field, $options, $context);
	}
}