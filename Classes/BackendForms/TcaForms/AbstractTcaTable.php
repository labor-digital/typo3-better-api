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
 * Last modified: 2020.03.21 at 21:41
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;


use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractForm;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionCollectorTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;

abstract class AbstractTcaTable extends AbstractForm {
	use DataHandlerActionCollectorTrait;
	
	/**
	 * Defines the default tca array for a new field,
	 * that is not yet known in the configuration array
	 */
	public const DEFAULT_FIELD_TCA_CONFIG = [
		"@sql" => "text",
	];
	
	/**
	 * @var string
	 */
	protected $tableName;
	
	/**
	 * Holds the type key this instance represents
	 * @var string|int
	 */
	protected $typeKey;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(string $id, ExtConfigContext $context) {
		parent::__construct($id, $context);
		$this->form = $this;
	}
	
	/**
	 * Returns the name of the linked database table
	 * @return string
	 */
	public function getTableName(): string {
		return $this->tableName;
	}
	
	/**
	 * Returns the instance of a certain tab.
	 * Note: If the tab not exists, a new one will be created at the end of the form
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTab
	 */
	public function getTab(string $id): TcaTab {
		return $this->getOrCreateElement($id, static::TYPE_TAB, function () use ($id) {
			return $this->context->getInstanceOf(TcaTab::class, [$id, $this->context]);
		});
	}
	
	/**
	 * Returns a single palette instance
	 * Note: If the palette not exists, a new one will be created at the end of the form
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaPalette
	 */
	public function getPalette(string $id): TcaPalette {
		return $this->getOrCreateElement($id, static::TYPE_CONTAINER, function () use ($id) {
			return $this->context->getInstanceOf(TcaPalette::class, [$id, $this->context]);
		});
	}
	
	/**
	 * Returns true if the layout has a palette with that id already registered
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasPalette(string $id): bool {
		return $this->hasElementInternal($id, static::TYPE_CONTAINER);
	}
	
	/**
	 * Returns the list of all palettes that are used inside of this form
	 *
	 * @return array
	 */
	public function getPalettes(): array {
		return $this->getAllOfType(static::TYPE_CONTAINER);
	}
	
	/**
	 * Adds a new line break to palettes
	 *
	 * @param string $position The position where to add the tab. See moveElement() for details
	 *
	 * @return string
	 */
	public function addLineBreak(string $position = ""): string {
		$id = "lb-" . md5(microtime(TRUE));
		$lineBreak = $this->context->getInstanceOf(TcaPaletteLineBreak::class, [$id, $this->context]);
		$lineBreak->__setParent($this);
		$lineBreak->__setForm($this->getForm());
		$this->addElement($lineBreak, $position);
		return $id;
	}
	
	/**
	 * Returns the instance of a certain field inside your current layout
	 * Note: If the field not exists, a new one will be created at the end of the form
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
	 */
	public function getField(string $id): TcaField {
		return $this->getOrCreateElement($id, static::TYPE_ELEMENT, function () use ($id) {
			if (Arrays::hasPath($this->config, ["columns", $id])) $tca = $this->config["columns"][$id];
			else $tca = array_merge(static::DEFAULT_FIELD_CONFIG, static::DEFAULT_FIELD_TCA_CONFIG);
			return TcaField::makeFromTcaConfig($id, $tca, $this->context, $this);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function getFields(): array {
		return array_filter(parent::getFields(), function ($f) {
			return !$f instanceof TcaPaletteLineBreak;
		});
	}
	
	/**
	 * Returns the type key for this table / type
	 *
	 * @return string
	 */
	public function getTypeKey(): string {
		// Try to reload the type key if we have a new table
		if (empty($this->typeKey) && $this instanceof TcaTable) $this->typeKey = reset($this->getTypes());
		return $this->typeKey;
	}
	
	/**
	 * Internal helper which generates the base tca for the elements inside this table
	 *
	 * @return array
	 */
	public function __build(): array {
		// Begin a blank slate tca config
		$tca = json_decode(json_encode($this->config), TRUE);
		$tca["columns"] = [];
		$tca["ctrl"] = [];
		
		// Build field configuration
		$fields = $this->getFields();
		
		foreach ($fields as $id => $field) {
			$fieldTca = $field->getRaw();
			// Don't add not configured fields
			if (empty($fieldTca["config"])) continue;
			$tca["columns"][$id] = $fieldTca;
		}
		
		// Build the show item string for this type
		$showItem = $this->dumpShowItem();
		$tca["types"][empty($this->typeKey) ? 0 : $this->typeKey]["showitem"] = $showItem["showitem"];
		
		// Build the show item string for the palettes
		foreach ($showItem["palettes"] as $k => $showItem)
			$tca["palettes"][$k]["showitem"] = $showItem;
		
		// Add the data handler actions
		$handlers = $this->__getDataHandlerActionHandlers();
		if (!empty($handlers))
			$tca["dataHandlerActions"] = $handlers["@table"];
		
		// Done
		return $tca;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function ensureInitialTab() {
		if (!empty($this->elements)) return;
		$this->getTab("0")->setLabel("betterApi.tab.general");
	}
	
	/**
	 * Internal helper to initialize all references of this table, based on the given tca
	 */
	protected function initializeInstance(): void {
		// Populate the logical tree
		$palettes = Arrays::getPath($this->config, "palettes.*.showitem", []);
		$types = Arrays::getPath($this->config, "types.*.showitem", []);
		$layout = reset($types);
		$this->typeKey = key($types);
		$this->populateByTcaLayout($layout, $palettes);
	}
	
	/**
	 * Internal helper which is used to initialize the logical layout tree based on a given layout string
	 *
	 * @param string $layout
	 * @param array  $palettes
	 *
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	protected function populateByTcaLayout(string $layout, array $palettes = []) {
		if (empty($layout)) return;
		
		// Make sure we only have a general tab if we really need one
		$this->elements = [];
		if (substr(trim($layout), 0, 7) !== "--div--") $this->ensureInitialTab();
		
		$tabId = NULL;
		foreach ($this->parseShowItemString($layout) as $item) {
			$layoutMeta = $item;
			$firstKey = reset($item);
			$position = empty($tabId) ? "" : "bottom:" . $tabId;
			
			// Check for modifiers
			if (substr($firstKey, 0, 2) === "--") {
				array_shift($layoutMeta);
				switch (strtolower(substr($firstKey, 2, -2))) {
					case "div":
						$tabId = count($this->getChildren());
						$tab = $this->getTab($tabId);
						$tab->setLayoutMeta($layoutMeta);
						if (!empty($item[1])) $tab->setLabel($item[1]);
						break;
					case "palette":
						$paletteId = end($item);
						
						// Ignore missing palettes
						if (!isset($palettes[$paletteId])) continue 2;
						
						// Ignore if palette already exists
						if ($this->hasPalette($paletteId)) continue 2;
						
						// Loop through all palette elements
						$palette = $this->getPalette($paletteId);
						$palette->moveTo($position);
						$palette->setLayoutMeta($layoutMeta);
						if (!empty($item[1])) $palette->setLabel($item[1]);
						foreach ($this->parseShowItemString($palettes[$paletteId]) as $field) {
							$layoutMeta = $field;
							$id = array_shift($field);
							$label = array_shift($field);
							
							// Check if we don't have this field
							if (!Arrays::hasPath($this->config, ["columns", $id])) {
								// Handle line breaks
								if (strtolower($id) === "--linebreak--") {
									$this->addLineBreak("bottom:_" . $paletteId);
									continue;
								}
								$this->config["columns"][$id] = [];
							}
							
							// Add the element
							$field = $this->getField($id);
							$field->moveTo("bottom:_" . $paletteId);
							$field->setLayoutMeta($layoutMeta);
							if (!empty($label)) $field->setLabel($label);
						}
						break;
					case "linebreak":
						$this->addLineBreak($position);
						break;
					default:
						throw new BackendFormException("Invalid special element was given: " . $item . " is not allowed!");
						break;
				}
			} else {
				// Add a field
				$id = array_shift($item);
				$label = array_shift($item);
				
				// Ignore if we don't have this field
				if (!Arrays::hasPath($this->config, ["columns", $id])) {
					// Check if we are inside a type
					if (isset($this->fieldTcaResolver)) {
						// Check if the parent knows this column
						$parentConfig = $this->getParent()->getRaw();
						if (!Arrays::hasPath($parentConfig, ["columns", $id])) continue;
					} else continue;
				}
				
				// Add the element
				$field = $this->getField($id);
				$field->moveTo($position);
				$field->setLayoutMeta($layoutMeta);
				if (!empty($label)) $field->setLabel($label);
			}
		}
		$this->ensureInitialTab();
	}
	
	/**
	 * Breaks up a show item string and returns a machine readable array of parts
	 *
	 * @param string $layout
	 *
	 * @return array
	 */
	protected function parseShowItemString(string $layout): array {
		$parts = array_filter(array_map("trim", explode(",", $layout)));
		foreach ($parts as $k => $part) {
			if (stripos($part, ";") !== FALSE) $parts[$k] = array_map("trim", explode(";", $part));
			else $parts[$k] = [$part];
		}
		return $parts;
	}
	
	/**
	 * Traverses the layout structure and generates a typo3 conform layout string out of the hierarchy
	 * Will also dump all linked palettes
	 * @return array
	 */
	protected function dumpShowItem(): array {
		$showItem = [];
		$paletteShowItem = [];
		foreach ($this->getLayoutArray() as $tab) {
			foreach ($tab as $k => $element) {
				/** @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement $element */
				// Handle node
				if ($k === "@node") {
					/** @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTab $element */
					$meta = $element->getLayoutMeta();
					$meta[0] = $element->getLabel();
					$showItem[] = "--div--;" . implode(";", $meta);
				} else {
					// Check if we got a field or a container
					if (is_array($element)) {
						// Got a container
						/** @var array $element */
						foreach ($element as $_k => $_element) {
							/** @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement $_element */
							if ($_k === "@node") {
								/** @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaPalette $_element */
								$meta = $_element->getLayoutMeta();
								if (!empty($meta[0])) $meta[0] = $_element->getLabel();
								if (!isset($meta[0])) $meta[0] = "";
								$meta[1] = $_element->getId();
								$showItem[] = "--palette--;" . implode(";", $meta);
								$paletteShowItem[$element["@node"]->getId()] = [];
							} else {
								// Handle line break
								if ($_element instanceof TcaPaletteLineBreak)
									$paletteShowItem[$element["@node"]->getId()][] = "--linebreak--";
								else {
									// Got a field
									/** @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField $_element */
									$meta = $_element->getLayoutMeta();
									$meta[0] = $_element->getId();
									if (!empty($meta[1])) $meta[1] = $_element->getLabel();
									$paletteShowItem[$element["@node"]->getId()][] = implode(";", $meta);
								}
							}
						}
					} else {
						// Got a field
						// Check if we got a valid field
						if (!$element instanceof TcaField) continue;
						$meta = $element->getLayoutMeta();
						$meta[0] = $element->getId();
						if (!empty($meta[1])) $meta[1] = $element->getLabel();
						$showItem[] = implode(";", $meta);
					}
				}
			}
		}
		
		// Done
		return [
			"showitem" => implode(",", $showItem),
			"palettes" => array_map(function ($v) {
				return implode(",", $v);
			}, $paletteShowItem),
		];
	}
	
	/**
	 * Helper to inject the table name into a type
	 *
	 * @param string $tableName
	 */
	protected function setTableName(string $tableName) {
		$this->tableName = $tableName;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getDataHandlerTableName(): string {
		return "@table";
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getDataHandlerFieldConstraints(): array {
		return [];
	}
}