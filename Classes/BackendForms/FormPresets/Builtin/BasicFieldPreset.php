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
 * Last modified: 2020.03.18 at 11:43
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin;


use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\AbstractFormPreset;
use Neunerlei\Options\Options;

class BasicFieldPreset extends AbstractFormPreset {
	/**
	 * Configures the field as a passThrough type. Its value, which is sent to the DataHandler is just kept,
	 * as is and put into the database field. Default FormEngine however never sends values.
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Passthrough.html#type-passthrough
	 */
	public function passThrough() {
		$this->field->addConfig([
			"type" => "passthrough",
		]);
	}
	
	/**
	 * Converts the field into a checkbox
	 *
	 * @param array $options Additional options for this preset
	 *                       - default bool (FALSE): A default value for your input field
	 *                       - toggle bool (FALSE): If set to true, this field is rendered as toggle and not as checkbox
	 *                       - inverted bool (FALSE): If set to true checked / unchecked state are swapped in view:
	 *                       A checkbox is marked checked if the database bit is not set and vice versa.
	 */
	public function checkbox(array $options = []) {
		// Prepare the options
		$options = Options::make($options, [
			"default"  => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"toggle"   => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"inverted" => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		// Prepare the config
		$config = ["type" => "check"];
		$config["default"] = (int)$options["default"];
		if ($options["toggle"]) $config["renderType"] = "checkboxToggle";
		if ($options["inverted"]) $config["items"] = [0 => "", 1 => "", "invertStateDisplay" => TRUE,];
		
		// Set sql config
		$this->setSqlDefinitionForTcaField("tinyint(4) DEFAULT '0'");
		
		// Done
		$this->field->addConfig($config);
	}
	
	/**
	 * Configures the current input element as a text area optionally with a rte configuration
	 *
	 * @param array $options  Additional options
	 *                        - default string: An optional default value to set for this field
	 *                        - required, trim bool: Any of these values can be passed
	 *                        to define their matching "eval" rules
	 *                        - maxLength int (65000): The max length of a text (also affects the length of the db
	 *                        field)
	 *                        - minLength int (0): The min length of a input
	 *                        - cols int (50): The width of the rendered field in html cols
	 *                        - rows int (40): The height of the rendered field in html rows
	 *                        - rte bool (FALSE): If set to true this field will be rendered as RTE editor
	 *                        - rteConfig string: For TYPO3 > v7 Can be used to select which rte config is to apply to
	 *                        this field
	 */
	public function textArea(array $options = []) {
		// Prepare the options
		$options = Options::make($options,
			$this->addEvalOptions(
				$this->addMinMaxLengthOptions(
					[
						"default"   => [
							"type"    => "string",
							"default" => "",
						],
						"cols"      => [
							"type"    => "int",
							"default" => 42,
						],
						"rows"      => [
							"type"    => "int",
							"default" => 5,
						],
						"rte"       => [
							"type"    => "bool",
							"default" => FALSE,
						],
						"rteConfig" => [
							"type"    => "string",
							"default" => "",
						],
					], 60000
				)
			));
		
		// Prepare the config
		$config = ["type" => "text"];
		
		// Apply defaults
		if (!empty($options["default"])) $config["default"] = $options["default"];
		$config["rows"] = $options["rows"];
		$config["cols"] = $options["cols"];
		$config = $this->addEvalConfig($config, $options);
		$config = $this->addMaxLengthConfig($config, $options, TRUE);
		
		// Add rte config
		if ($options["rte"]) {
			$config["enableRichtext"] = TRUE;
			if (!empty($options["rteConfig"])) $config["richtextConfiguration"] = $options["rteConfig"];
		}
		
		// Done
		$this->field->addConfig($config);
	}
	
	/**
	 * Sets the current field as a simple select field.
	 *
	 * @param array $items   The items you want to set for this select field, as an array
	 *                       with the "value" as key and the "label" as value
	 * @param array $options Additional options for this preset
	 *                       - minItems int (0): The minimum number of items required to be valid
	 *                       - maxItems int (1): The maximum number of items allowed in this field
	 *                       - required bool: If set this field will be required to be filled
	 *                       - default string|number: If given this is used as default value when a new record is
	 *                       created
	 *                       - userFunc string: Can be given like any select itemProcFunc in typo3 as:
	 *                       vendor\className->methodName and is used as a filter for the items in the select field
	 *
	 */
	public function select(array $items, array $options = []) {
		// Prepare the options
		$options = Options::make($options,
			$this->addEvalOptions(
				$this->addMinMaxItemOptions([
					"userFunc" => [
						"type"    => "string",
						"default" => "",
					],
					"default"  => [
						"type"    => ["string", "number", "null"],
						"default" => NULL,
					],
				], ["maxItems" => 1]
				), ["required"])
		);
		
		// Convert the items array
		$itemsFiltered = [];
		foreach ($items as $k => $v)
			$itemsFiltered[] = [$v, $k];
		
		// Build the config
		$config = [
			"type"          => "select",
			"renderType"    => $options["maxItems"] <= 1 ? "selectSingle" : "selectCheckBox",
			"size"          => 1,
			"items"         => $itemsFiltered,
			"itemsProcFunc" => $options["userFunc"],
		];
		
		// Add additional config
		if (!is_null($options["default"])) $config["default"] = $options["default"];
		$config = $this->addMinMaxItemConfig($config, $options);
		$config = $this->addEvalConfig($config, $options);
		$this->setSqlDefinitionForTcaField("varchar(1024) DEFAULT ''");
		
		// Set the field
		$this->field->addConfig($config);
	}
	
	/**
	 * Creates a select field that has 9 possible positions from top-left over middle-middle to bottom-right.
	 * It can be used to create an image alignment configuration.
	 *
	 * If you add this field preset to the sys_file_reference table with the field name of "image_alignment",
	 * the fal file service will automatically find and return the alignment property when you request
	 * file information.
	 */
	public function imageAlignment() {
		if (!$this->field->hasLabel())
			$this->field->setLabel("tbbe.d.sys_file_reference.imageAlignment");
		$this->select([
			"tl" => "tbbe.d.sys_file_reference.imageAlignment.topLeft",
			"tc" => "tbbe.d.sys_file_reference.imageAlignment.topCenter",
			"tr" => "tbbe.d.sys_file_reference.imageAlignment.topRight",
			"cl" => "tbbe.d.sys_file_reference.imageAlignment.centerLeft",
			"cc" => "tbbe.d.sys_file_reference.imageAlignment.centerCenter",
			"cr" => "tbbe.d.sys_file_reference.imageAlignment.centerRight",
			"bl" => "tbbe.d.sys_file_reference.imageAlignment.bottomLeft",
			"bc" => "tbbe.d.sys_file_reference.imageAlignment.bottomCenter",
			"br" => "tbbe.d.sys_file_reference.imageAlignment.bottomRight",
		], ["default" => "cc"]);
	}
	
	/**
	 * Can be used to apply a callback function on a field.
	 * This can become quite handy if you want to configure multiple fields with the same configuration.
	 * Use a closure to wrap your field configuration and apply it to each field in your TCA
	 *
	 * @param callable $callable
	 */
	public function applyCallback(callable $callable) {
		call_user_func($callable, $this->field, $this->context);
	}
}