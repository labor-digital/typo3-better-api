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
 * Last modified: 2020.03.18 at 11:44
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin;


use DateTime;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\AbstractFormPreset;
use Neunerlei\Options\Options;
use Neunerlei\TinyTimy\DateTimy;

class InputFieldPreset extends AbstractFormPreset {
	
	/**
	 * Configures the current field as a simple input element
	 *
	 * @param array $options Additional options for this preset
	 *                       - default string: A default value for your input field
	 *                       - required, trim, lower, int, email, password, unique, null bool: Any of these values can
	 *                       be passed to define their matching "eval" rules
	 *                       - maxLength int (2048): The max length of a input (also affects the length of the db
	 *                       field)
	 *                       - minLength int (0): The min length of a input
	 *                       - readOnly bool (FALSE): True to make this field read only
	 *
	 */
	public function input(array $options = []) {
		// Prepare the options
		$options = Options::make($options,
			$this->addEvalOptions(
				$this->addMinMaxLengthOptions(
					$this->addReadOnlyOptions(
						$this->addPlaceholderOption(
							[
								"default" => [
									"type"    => "string",
									"default" => "",
								],
							]
						)
					)
				)
			));
		
		// Prepare the config
		$config = ["type" => "input"];
		
		// Apply defaults
		if (!empty($options["default"])) $config["default"] = $options["default"];
		$config = $this->addReadOnlyConfig($config, $options);
		$config = $this->addEvalConfig($config, $options);
		$config = $this->addMaxLengthConfig($config, $options, TRUE);
		$config = $this->addPlaceholderConfig($config, $options);
		
		// Done
		$this->field->addConfig($config);
	}
	
	/**
	 * Configures this field as either a date or a datetime field.
	 * Date fields have their own datepicker.
	 *
	 * @param array $options Additional options for this preset
	 *                       - default string|number|DateTime: A default value for your input field
	 *                       - withTime bool (FALSE): If set to true this field can also have the time set, not only
	 *                       the date
	 *                       - asInt bool (FALSE): By default the database value will be written as "datetime" type. If
	 *                       you however want the database to store the date as integer you can set this to true
	 *                       - required, trim bool: Any of these values can be passed
	 *                       to define their matching "eval" rules
	 */
	public function date(array $options = []) {
		
		// Prepare options
		$options = Options::make($options,
			$this->addEvalOptions([
				"withTime" => [
					"type"    => "bool",
					"default" => FALSE,
				],
				"asInt"    => [
					"type"    => "bool",
					"default" => FALSE,
				],
				"default"  => [
					"type"    => ["null", "string", "number", DateTime::class, DateTimy::class],
					"default" => NULL,
				],
			], ["required", "trim"])
		);
		
		// Set sql statement
		$this->setSqlDefinitionForTcaField(
			$options["asInt"] ?
				"int(11) DEFAULT '0'" :
				"datetime DEFAULT 'CURRENT_TIMESTAMP'"
		);
		
		// Prepare the config
		$config = ["type" => "input"];
		if ($options["default"] !== NULL) {
			$date = new DateTimy($options["default"]);
			$config["default"] = $options["asInt"] ? $date->getTimestamp() : $date->formatSql();
		}
		$options[$options["withTime"] ? "datetime" : "date"] = TRUE;
		$config = $this->addEvalConfig($config, $options);
		if (!$options["asInt"]) $config["dbType"] = "datetime";
		
		// Done
		$this->field->addConfig($config);
	}
	
	/**
	 * Configures the current field as a link selection.
	 *
	 * @param array $options Additional config options for this preset
	 *                       - allowFiles bool (FALSE): True to allow file links
	 *                       - allowExternal bool (TRUE): True to allow external URL links
	 *                       - allowPages bool (TRUE): True to allow links to pages
	 *                       - allowMail bool (FALSE): True to allow links to mails
	 *                       - allowFolder bool (FALSE): True to allow links to storage folders
	 *                       - default string: A default value for your input field
	 *                       - maxLength int (2048): The max length of a link (also affects the length of the db field)
	 *                       - minLength int (0): The min length of a input
	 *                       - hideClutter bool: By default we hide clutter fields like class or params in the link
	 *                       browser. If you want those fields to be set, set this to false.
	 *                       - required, trim bool: Any of these values can be passed
	 *                       to define their matching "eval" rules
	 */
	public function link(array $options = []) {
		// Prepare the options
		$options = Options::make($options,
			$this->addEvalOptions(
				$this->addMinMaxLengthOptions(
					[
						"allowFiles"    => [
							"type"    => "bool",
							"default" => FALSE,
						],
						"allowExternal" => [
							"type"    => "bool",
							"default" => TRUE,
						],
						"allowPages"    => [
							"type"    => "bool",
							"default" => TRUE,
						],
						"allowMail"     => [
							"type"    => "bool",
							"default" => FALSE,
						],
						"allowFolder"   => [
							"type"    => "bool",
							"default" => FALSE,
						],
						"default"       => [
							"type"    => "string",
							"default" => "",
						],
						"hideClutter"   => [
							"type"    => "bool",
							"default" => TRUE,
						],
					]
					, 2048),
				["required", "trim"], ["trim" => TRUE]));
		
		// Prepare blinded url types
		$blindFields = [];
		if (!$options["allowFiles"]) $blindFields[] = "file";
		if (!$options["allowExternal"]) $blindFields[] = "url";
		if (!$options["allowPages"]) $blindFields[] = "page";
		if (!$options["allowMail"]) $blindFields[] = "mail";
		if (!$options["allowFolder"]) $blindFields[] = "folder";
		$blindFields = implode(",", $blindFields);
		
		// Prepare the config
		$config = [
			"type"         => "input",
			"softref"      => "typolink,typolink_tag,images,url",
			"renderType"   => "inputLink",
			"fieldControl" => [
				"linkPopup" => [
					"options" => [
						"blindLinkOptions" => $blindFields,
						"blindLinkFields"  => $options["hideClutter"] ? "class,params" : "",
					],
				],
			],
		];
		
		// Apply defaults
		if (!empty($options["default"])) $config["default"] = $options["default"];
		$config = $this->addEvalConfig($config, $options);
		$config = $this->addMaxLengthConfig($config, $options, TRUE);
		
		// Set the field
		$this->field->addConfig($config);
	}
	
	
	/**
	 * Converts your field into a slug or path segment field. By default we will use a custom renderer
	 * to make sure your slug's don't look like "www.your-domain.deyour-slug". If you want the default
	 * behaviour set the "useNativeElement" flag to true.
	 *
	 * @param array $fields  The list of fields from which the slug should be generated.
	 *                       Multiple fields will be concatenated like described in the TCA configuration.
	 * @param array $options Additional configuration options
	 *                       - replacements array (["/" => "-"]): A list of characters that should be replaced
	 *                       with another character when the slug is generated. By default we remove all slashes
	 *                       and turn them into dashes.
	 *                       - default string: A default value for your input field
	 *                       - useNativeElement bool (FALSE): As stated above, we auto-apply a small
	 *                       visual fix to the slug element to make it more speaking for the editor.
	 *                       If you don't want that fix, set this flag to true.
	 *                       - required, uniqueInSite bool: Any of these values can be passed
	 *                       to define their matching "eval" rules
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Slug.html
	 */
	public function slug(array $fields, array $options = []) {
		$options = Options::make($options,
			$this->addEvalOptions([
				"replacements"     => [
					"type"    => "array",
					"default" => ["/" => "-"],
				],
				"default"          => [
					"type"    => ["string", "number"],
					"default" => "",
				],
				"useNativeElement" => [
					"type"    => "bool",
					"default" => FALSE,
				],
			], ["required", "uniqueInSite"]));
		
		
		// Build the configuration
		$config = [
			"type"              => "slug",
			"generatorOptions"  => [
				"fields"               => $fields,
				"fieldSeparator"       => "/",
				"prefixParentPageSlug" => FALSE,
				"replacements"         => $options["replacements"],
			],
			"prependSlash"      => FALSE,
			"fallbackCharacter" => "-",
			"default"           => $options["default"],
			"size"              => 50,
		];
		if ($options["useNativeElement"]) $config["renderType"] = "betterApiPathSegmentSlug";
		$config = $this->addEvalConfig($config, $options);
		$config = $this->addMaxLengthConfig($config, ["maxLength" => 2048]);
		
		// Inject the field configuration
		$this->field->addConfig($config);
	}
}