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
 * Last modified: 2020.03.20 at 14:29
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;


use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigTableAfterBuildEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigTableBeforeBuildEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigTableDefaultTcaFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigTableRawTcaTypeFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigTableTcaTypeFilterEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class TcaTable extends AbstractTcaTable {
	protected const DEFAULT_TCA = [
		"@sql" => [
			"additionalColumns" => [
				"uid"           => "int(11) NOT NULL auto_increment",
				"t3ver_oid"     => "int(11) DEFAULT '0' NOT NULL",
				"t3ver_id"      => "int(11) DEFAULT '0' NOT NULL",
				"t3ver_wsid"    => "int(11) DEFAULT '0' NOT NULL",
				"t3ver_label"   => "varchar(30) DEFAULT '' NOT NULL",
				"t3ver_state"   => "tinyint(4) DEFAULT '0' NOT NULL",
				"t3ver_stage"   => "tinyint(4) DEFAULT '0' NOT NULL",
				"t3ver_count"   => "int(11) DEFAULT '0' NOT NULL",
				"t3ver_tstamp"  => "int(11) DEFAULT '0' NOT NULL",
				"t3ver_move_id" => "int(11) DEFAULT '0' NOT NULL",
				"t3_origuid"    => "int(11) DEFAULT '0' NOT NULL",
				"editlock"      => "tinyint(4) DEFAULT '0' NOT NULL",
				"deleted"       => "tinyint(4) DEFAULT '0' NOT NULL",
			],
			"meta"              => [
				"PRIMARY KEY (`uid`)",
			],
		],
		
		"ctrl" => [
			"label"                    => "uid",
			"hideAtCopy"               => TRUE,
			"tstamp"                   => "tstamp",
			"crdate"                   => "crdate",
			"cruser_id"                => "cruser_id",
			"versioningWS"             => TRUE,
			"origUid"                  => "t3_origuid",
			"editlock"                 => "editlock",
			"prepentAtCopy"            => "",
			"transOrigPointerField"    => "l10n_parent",
			"transOrigDiffSourceField" => "l10n_diffsource",
			"languageField"            => "sys_language_uid",
			"enablecolumns"            => [
				"disabled"  => "hidden",
				"starttime" => "starttime",
				"endtime"   => "endtime",
				"fe_group"  => "fe_group",
			],
			"delete"                   => "deleted",
		],
		
		"columns"  => [
			"sys_language_uid" => [
				"@sql"    => "int(11) DEFAULT '0' NOT NULL",
				"exclude" => TRUE,
				"label"   => "LLL:EXT:lang/locallang_general.xlf:LGL.language",
				"config"  => [
					"type"       => "select",
					"renderType" => "selectSingle",
					"special"    => "languages",
					"items"      => [
						["LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages", -1],
					],
				],
			],
			"l10n_parent"      => [
				"@sql"        => "int(11) DEFAULT '0' NOT NULL",
				"displayCond" => "FIELD:sys_language_uid:>:0",
				"exclude"     => TRUE,
				"label"       => "LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent",
				"config"      => [
					"type"                => "select",
					"renderType"          => "selectSingle",
					"items"               => [["", 0]],
					"foreign_table"       => "{{table}}",
					"foreign_table_where" => "AND {{table}}.uid=###REC_FIELD_l10n_parent### AND {{table}}.sys_language_uid IN (-1,0)",
					"default"             => 0,
				],
			],
			"l10n_diffsource"  => [
				"@sql"   => "mediumblob NOT NULL",
				"config" => [
					"type"    => "passthrough",
					"default" => "",
				],
			],
			"l10n_source"      => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"config" => [
					"type" => "passthrough",
				],
			],
			"hidden"           => [
				"@sql"    => "tinyint(4) DEFAULT '0' NOT NULL",
				"exclude" => TRUE,
				"label"   => "LLL:EXT:lang/locallang_general.xlf:LGL.hidden",
				"config"  => [
					"type"    => "check",
					"default" => 0,
				],
			],
			"cruser_id"        => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"label"  => "cruser_id",
				"config" => ["type" => "passthrough"],
			],
			"pid"              => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"label"  => "pid",
				"config" => ["type" => "passthrough"],
			],
			"crdate"           => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"label"  => "crdate",
				"config" => ["type" => "passthrough"],
			],
			"tstamp"           => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"label"  => "tstamp",
				"config" => ["type" => "passthrough"],
			],
			"sorting"          => [
				"@sql"   => "int(11) DEFAULT '0' NOT NULL",
				"label"  => "sorting",
				"config" => ["type" => "passthrough"],
			],
			"starttime"        => [
				"@sql"    => "int(11) DEFAULT '0' NOT NULL",
				"exclude" => TRUE,
				"label"   => "LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel",
				"config"  => [
					"type"       => "input",
					"renderType" => "inputDateTime",
					"size"       => 16,
					"eval"       => "datetime",
					"default"    => 0,
					"behaviour"  => [
						"allowLanguageSynchronization" => TRUE,
					],
				],
			],
			"endtime"          => [
				"@sql"    => "int(11) DEFAULT '0' NOT NULL",
				"exclude" => TRUE,
				"label"   => "LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel",
				"config"  => [
					"type"       => "input",
					"renderType" => "inputDateTime",
					"size"       => 16,
					"eval"       => "datetime",
					"default"    => 0,
					"behaviour"  => [
						"allowLanguageSynchronization" => TRUE,
					],
				],
			],
			"fe_group"         => [
				"@sql"    => "varchar(100) DEFAULT '0' NOT NULL",
				"exclude" => TRUE,
				"label"   => "LLL:EXT:lang/locallang_general.xlf:LGL.fe_group",
				"config"  => [
					"type"                => "select",
					"renderType"          => "selectMultipleSideBySide",
					"size"                => 5,
					"maxitems"            => 20,
					"items"               =>
						[
							["LLL:EXT:lang/locallang_general.xlf:LGL.hide_at_login", -1],
							["LLL:EXT:lang/locallang_general.xlf:LGL.any_login", -2],
							["LLL:EXT:lang/locallang_general.xlf:LGL.usergroups", "--div--"],
						],
					"exclusiveKeys"       => "-1,-2",
					"foreign_table"       => "fe_groups",
					"foreign_table_where" => "ORDER BY fe_groups.title",
				],
			],
			"t3_origuid"       => [
				"config" => [
					"default" => 0,
					"type"    => "passthrough",
				],
			],
			"t3ver_label"      => [
				"label"  => " LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel",
				"config" => [
					"max"  => 255,
					"size" => 30,
					"type" => "input",
				],
			],
		],
		"types"    => [
			"0" => [
				"showitem" => "
					--div--;betterApi.tab.general,
					--div--;betterApi.tab.access,
					--palette--;;hidden,
					--palette--;;access,
					--div--;betterApi.tab.language,
					--palette--;;language",
			],
		],
		"palettes" => [
			"hidden"   => [
				"showitem" => "hidden;betterApi.field.hidden",
			],
			"language" => [
				"showitem" => "sys_language_uid;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel,l10n_parent",
			],
			"access"   => [
				"showitem" => "starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel, endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel, --linebreak--, fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel",
			],
		],
	];
	
	/**
	 * Defines a list of additional properties that are not included
	 * in a normal TCA array. We will automatically dump and reimport them into the "additionalConfig" node
	 */
	protected const ADDITIONAL_TCA_CONFIG_LIST = [
		"allowOnStandardPages", "modelList", "listPosition",
	];
	
	/**
	 * Contains the list of all instantiated tca types of this table
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
	 *
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableType[]
	 */
	protected $types = [];
	
	/**
	 * The control configuration for this table
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
	 */
	protected $ctrl;
	
	/**
	 * If true the table will be allowed on pages, and not only in folder items
	 *
	 * @var bool
	 */
	protected $allowOnStandardPages = FALSE;
	
	/**
	 * An array of model classes that should be mapped to this table
	 * @var array
	 */
	protected $modelList = [];
	
	/**
	 * Stores the position of this table when shown on the list mode
	 * @var array
	 */
	protected $listPosition = [];
	
	/**
	 * This allows you to set a class of the model which then will be mapped to this table
	 *
	 * @param string $className
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public function addModelClass(string $className): TcaTable {
		$this->modelList[] = $className;
		return $this;
	}
	
	/**
	 * Can be used to configure the order of tables when they are rendered in the "list" mode in the backend.
	 * This table will be sorted either before or after the table with $otherTableName
	 *
	 * @param string $otherTableName The table to relatively position this one to
	 * @param bool   $before         True by default, if set to false the table will be shown after the $otherTableName
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public function setListPosition(string $otherTableName, bool $before = TRUE): TcaTable {
		$this->listPosition[$before ? "before" : "after"][] =
			$this->context->OptionList->table()->getRealTableName($otherTableName);
		return $this;
	}
	
	/**
	 * Returns the list of currently configured model classes for this table
	 * @return array
	 */
	public function getModelClasses(): array {
		return array_unique($this->modelList);
	}
	
	/**
	 * Returns the readable title of this table
	 *
	 * @return string
	 */
	public function getTitle(): string {
		return $this->ctrl->getTitle();
	}
	
	/**
	 * Sets the system title / label for this table in the backend
	 *
	 * @param string $title
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public function setTitle(string $title): TcaTable {
		$this->ctrl->setTitle($title);
		return $this;
	}
	
	/**
	 * Returns an additional configuration object for the column "control" configuration
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Ctrl/Index.html
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableCtrl
	 */
	public function getCtrl(): TcaTableCtrl {
		return $this->ctrl;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLabel(): string {
		return $this->getTitle();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLabel(?string $label) {
		return $this->setTitle($label . "");
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasLabel(): bool {
		return !empty($this->getTitle());
	}
	
	/**
	 * Returns the instance of a certain tca type.
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableType
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	public function getType(string $id): TcaTableType {
		return $this->getTypeInternal($id);
	}
	
	/**
	 * Returns the list of all known type keys of this table
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
	 *
	 * @return array
	 */
	public function getTypes(): array {
		$types = array_keys(Arrays::getPath($this->config, ["types"], []));
		$types = Arrays::attach($types, array_keys($this->types));
		return array_unique($types);
	}
	
	/**
	 * Returns true if a given type exists for this table
	 *
	 * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasType(string $id): bool {
		return in_array($id, $this->getTypes(), TRUE);
	}
	
	/**
	 * Returns true if the table is allowed on standard pages, and not only in folder items
	 *
	 * @return bool
	 */
	public function isAllowedOnStandardPages(): bool {
		return $this->allowOnStandardPages;
	}
	
	/**
	 * Use this if you want to allow this table to have records on standard pages and not only in folder items
	 *
	 * @param bool $state
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public function setAllowOnStandardPages(bool $state = TRUE): TcaTable {
		$this->allowOnStandardPages = $state;
		return $this;
	}
	
	/**
	 * This method is useful if you want to create a new database table,
	 * but don't want to configure every column for typo3's versioning, sorting or translations
	 * manually.
	 *
	 * When you run this method the current table will be configured as a valid extbase table
	 * with all the meta columns already configured. You just have to add your payload and be done.
	 *
	 * Note: It is not recommended to use this method on existing tables. Only use it in freshly initialized tables
	 *
	 * @param array $options Additional options for advanced configuration
	 *                       - sortable bool (FALSE): If set to true the "sortby" field will automatically set to the
	 *                       "sorting" column.
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public function applyDefaults(array $options = []): TcaTable {
		// Prepare options
		$options = Options::make($options, [
			"sortable" => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		// Prepare the default tca
		$default = static::DEFAULT_TCA;
		$default["ctrl"]["title"] = Inflector::toHuman(preg_replace("/^(.*?_domain_model_)/", "", $this->getTableName()));
		$default["ctrl"]["iconfile"] = "EXT:" . $this->context->getExtKey() . "/ext_icon.gif";
		$default["columns"]["l10n_parent"]["config"]["foreign_table"] = $this->getTableName();
		$default["columns"]["l10n_parent"]["config"]["foreign_table_where"] =
			str_replace("{{table}}", $this->getTableName(), $default["columns"]["l10n_parent"]["config"]["foreign_table_where"]);
		if ($options["sortable"]) $default["ctrl"]["sortby"] = "sorting";
		
		// Allow filtering
		$this->context->EventBus->dispatch(($e = new ExtConfigTableDefaultTcaFilterEvent($default, $this)));
		$default = $e->getDefaultTca();
		
		// Inject the defaults into the current config
		$this->config = Arrays::merge($default, $this->config);
		
		// Reinitialize the object
		$this->elements = [];
		$this->ensureInitialTab();
		$this->types = [];
		$this->initializeInstance();
		
		// Done
		return $this;
	}
	
	/**
	 * @inheritDoc
	 */
	public function removeElement(string $id): bool {
		$this->context->SqlGenerator->removeDefinitionFor($this->getTableName(), $id);
		return parent::removeElement($id);
	}
	
	/**
	 * Internal helper which generates the tca and additional, required configuration
	 * based on the current table configuration
	 *
	 * @return array
	 */
	public function __build(): array {
		// Emit event
		$this->context->EventBus->dispatch(($e = new ExtConfigTableBeforeBuildEvent($this->tableName, $this)));
		
		// Build the base tca
		$tca = parent::__build();
		unset($tca["@sql"]);
		
		// Inherit all missing columns from the existing tca
		foreach (Arrays::getPath($this->config, ["columns"], []) as $k => $v)
			if (!isset($tca["columns"][$k])) $tca["columns"][$k] = $v;
		
		// Build the loaded type TCA's
		foreach ($this->types as $key => $type) {
			$typeTca = $type->__build();
			unset($typeTca["ctrl"]);
			
			// Allow filtering
			$this->context->EventBus->dispatch(($e = new ExtConfigTableRawTcaTypeFilterEvent(
				$typeTca, $key, $this->tableName, $this
			)));
			$typeTca = $e->getTypeTca();
			
			// Calculate columns overrides
			$overrides = $this->buildColumnOverrides($tca["columns"], $typeTca["columns"]);
			if (!empty($overrides)) $tca["types"][$key]["columnsOverrides"] = $overrides;
			
			// Calculate changed palettes
			$typeShowItem = &$typeTca["types"][$key]["showitem"];
			$this->buildMergedPalettes($key, $typeShowItem, $tca["palettes"], $typeTca["palettes"]);
			$tca["types"][$key]["showitem"] = $typeShowItem;
			
			// Allow filtering
			$this->context->EventBus->dispatch(($e = new ExtConfigTableTcaTypeFilterEvent(
				$typeTca, $key, $this->tableName, $this
			)));
			$tca["types"][$key] = $e->getTypeTca();
		}
		
		// Update control object
		$tca["ctrl"] = $this->ctrl->getRaw();
		
		// Add additional information to the tca
		$additionalConfig = [];
		foreach (static::ADDITIONAL_TCA_CONFIG_LIST as $property)
			$additionalConfig[$property] = $this->$property;
		$tca["additionalConfig"] = $additionalConfig;
		
		// Allow filtering
		$this->context->EventBus->dispatch(($e = new ExtConfigTableAfterBuildEvent(
			$tca, $this->tableName, $this
		)));
		return $e->getTca();
	}
	
	/**
	 * Internal helper which calculates the diff between the main table's columns and
	 * the columns in a type. It will then result in a columnsOverrides array for this type
	 *
	 * @param array $columns
	 * @param array $typeColumns
	 *
	 * @return array
	 */
	protected function buildColumnOverrides(array &$columns, array $typeColumns): array {
		
		// No changes -> skip this
		if ($columns == $typeColumns) return [];
		
		$overrides = [];
		
		// Helper to find differences in multidimensional arrays
		$walker = function (array $a, array $b, $walker): array {
			$diff = [];
			foreach ($b as $k => $v) {
				if (!isset($a[$k])) $diff[$k] = $v;
				else if ($a[$k] === $v || is_numeric($a[$k]) && is_numeric($v) && $a[$k] == $v) continue;
				else if (is_array($v)) {
					if (!is_array($a[$k])) $diff[$k] = $v;
					else {
						$_diff = $walker($a[$k], $v, $walker);
						if (!empty($_diff)) $diff[$k] = $_diff;
					}
				} else $diff[$k] = $v;
			}
			return $diff;
		};
		
		// Loop over all type columns and generate the diff to the main definition
		foreach ($typeColumns as $id => $col) {
			// Column does not exist in parent
			if (!isset($columns[$id])) {
				// Make sure dummy columns (like editlock and co don't get added to the main tca)
				// They are not in the columns array because we don't configure them by default
				// but they are "theoretically" there. So check if we have a field for the id first
				if (!$this->hasField($id)) {
					$columns[$id] = $col;
				} else {
					// Add col completely as override
					$overrides[$id] = $col;
				}
				continue;
			}
			
			// Check if the column equals the other column
			if ($columns[$id] == $col) continue;
			
			// Calculate difference
			$diff = $walker($columns[$id], $col, $walker);
			if (empty($diff)) continue;
			$overrides[$id] = $diff;
		}
		
		return $overrides;
	}
	
	/**
	 * This internal helper is used to merge the type's palettes into the parent's palettes.
	 * If it detects a mismatch it will create a new, separate palette for the type to avoid global pollution
	 *
	 * @param string $type
	 * @param string $typeShowitem
	 * @param array  $palettes
	 * @param array  $typePalettes
	 */
	protected function buildMergedPalettes(string $type, string &$typeShowitem, array &$palettes, array $typePalettes): void {
		// Ignore if the palettes are identical
		if ($palettes == $typePalettes) return;
		
		// Loop over all type palettes
		foreach ($typePalettes as $k => $p) {
			$showitem = $p["showitem"];
			
			// Add new palette
			if (!isset($palettes[$k])) {
				$palettes[$k]["showitem"] = $showitem;
				continue;
			}
			
			// Check if there is already a showitem for this palette
			if (isset($palettes[$k]["showitem"])) {
				// Ignore identical palette
				if ($palettes[$k]["showitem"] === $showitem) continue;
				
				// Compare a unified version of both
				if (Inflector::toComparable($palettes[$k]["showitem"]) === Inflector::toComparable($showitem)) continue;
			}
			
			// Create a new version of this palette for the type
			$newK = $type . "-" . $k;
			$palettes[$newK]["showitem"] = $showitem;
			
			// Update type's show item...
			// Yay for string manipulation \o/...
			$typeShowitem = preg_replace("/(--palette--;[^;,]*;)" . preg_quote($k) . "(,|$)/si", "\${1}$newK,", $typeShowitem);
		}
	}
	
	/**
	 * Internal helper to create retrieve or create a instance of a tca type representation
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableType
	 */
	protected function getTypeInternal(string $id): TcaTableType {
		// Return already existing type object
		if (isset($this->types[$id])) return $this->types[$id];
		
		// Merge columns overrides into the main tca
		$typeConfig = Arrays::getPath($this->config, ["types", $id], []);
		if (!isset($typeConfig["showitem"])) $typeConfig["showitem"] = "";
		
		// Create real tca configuration for this type
		$typeTca = [
			"palettes" => Arrays::getPath($this->config, ["palettes"], []),
			"columns"  => [],
		];
		if (!is_array($typeTca["palettes"])) $typeTca["palettes"] = [];
		$columnsOverrides = Arrays::getPath($typeConfig, ["columnsOverrides"], []);
		$typeTca["types"] = [$id => ["showitem" => $typeConfig["showitem"]]];
		
		// Create the new type
		$type = $this->context->getInstanceOf(TcaTableType::class, [$this->tableName . "-" . $id, $this->context]);
		$type->setRaw($typeTca);
		$type->__setParent($this);
		$type->setTableName($this->tableName);
		
		// Inject the field resolver
		$type->__setFieldTcaResolver(function ($field) use ($columnsOverrides) {
			// Try to get a fresh config from one of our own children or use our config object...
			if ($this->hasElementInternal($field, static::TYPE_ELEMENT))
				$tca = $this->getElementInternal($field, static::TYPE_ELEMENT)->getRaw();
			else $tca = Arrays::getPath($this->config, ["columns", $field], []);
			
			// Check if this field is not known in the sql table yet
			if (empty($tca))
				if (empty($this->context->SqlGenerator->getDefinitionFor($this->tableName, $field)))
					$this->context->SqlGenerator->setDefinitionFor($this->tableName, $field, "text");
			
			// Check if there is a registered override
			$overrides = Arrays::getPath($columnsOverrides, $field, []);
			if (!empty($overrides))
				ArrayUtility::mergeRecursiveWithOverrule($tca, $overrides);
			
			// Done
			return $tca;
		});
		
		// Initialize the type
		$type->initializeInstance();
		
		// Done
		return $this->types[$id] = $type;
	}
	
	/**
	 * Internal helper to create a new instance of a tca table, based on the global tca array
	 *
	 * @param string           $tableName
	 * @param ExtConfigContext $context
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
	 */
	public static function makeInstance(string $tableName, ExtConfigContext $context): TcaTable {
		// Load the tca
		$tableTca = Arrays::getPath($GLOBALS, ["TCA", $tableName], []);
		
		// Create a new instance
		$i = $context->getInstanceOf(static::class, [$tableName, $context]);
		$i->setRaw($tableTca);
		$i->setTableName($tableName);
		$i->initializeInstance();
		
		// Done
		return $i;
	}
	
	/**
	 * Internal helper to initialize all references of this table, based on the given tca
	 */
	protected function initializeInstance(): void {
		parent::initializeInstance();
		
		// Import sql statements
		$this->context->SqlGenerator->__setByTcaDefinition($this->getTableName(), $this->config);
		
		// Create control object
		$ctrl = Arrays::getPath($this->config, "ctrl", []);
		$this->ctrl = TypoContainer::getInstance()->get(TcaTableCtrl::class, [
			"args" => [$ctrl, $this],
		]);
		
		// Import additional config
		foreach (static::ADDITIONAL_TCA_CONFIG_LIST as $property)
			if (!empty($this->config[$property])) $this->$property = $this->config[$property];
		
	}
}