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
 * Last modified: 2020.03.19 at 02:51
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Abstracts;

use Exception;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;

abstract class AbstractForm extends AbstractFormContainer {
	
	/**
	 * Defines the default configuration array for a new field,
	 * that is not yet known in the configuration array
	 */
	public const DEFAULT_FIELD_CONFIG = [
		"exclude" => 1,
		"config"  => [],
	];
	
	/**
	 * Generic form config
	 * @var array
	 */
	protected $config = [];
	
	/**
	 * @inheritDoc
	 */
	public function __construct(string $id, ExtConfigContext $context) {
		if (empty($parent)) $this->parent = $this;
		parent::__construct($id, $context);
		$this->form = $this;
		$this->parent = $this;
		$this->ensureInitialTab();
	}
	
	/**
	 * Can be used to move any element inside the form to any other position.
	 *
	 * Position can be defined as "field", "container" or "0" (tabs) to move the element AFTER the defined element.
	 *
	 * You may also use the following modifiers:
	 *    - before:field positions the element in front of the element with "field" as id
	 *    - after:field positions the element after the element with "field" as id
	 *    - top:container positions the element as first element of a container/tab
	 *    - bottom:container positions the element as last element of a container/tab
	 *
	 * If top/bottom are used in combination with a field (and not a container element) it will be translated to before
	 * or after respectively.
	 *
	 * @param string $id
	 * @param string $position
	 *
	 * @return bool
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 */
	public function moveElement(string $id, string $position): bool {
		
		try {
			$el = $this->getElementInternal($id);
			return $this->moveElementInstance($el, $position);
		} catch (BackendFormException $exception) {
			return FALSE;
		}
	}
	
	/**
	 * The same as moveElement() but receives the element instance instead of an id to move
	 *
	 * @param AbstractFormElement $el
	 * @param string              $position
	 *
	 * @return bool
	 * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
	 *
	 * @see moveElement() for further details.
	 *
	 */
	public function moveElementInstance(AbstractFormElement $el, string $position): bool {
		// Ignore if there is no position given
		if (empty($position) && !is_numeric($position)) return FALSE;
		
		// Translate "0" -> First tab to sDef in flex forms
		if ($position === "0" && $this instanceof FlexForm)
			$position = "sDEF";
		
		// Prepare position
		$posParts = explode(":", $position);
		if (count($posParts) !== 2) {
			if (count($posParts) === 1) {
				// Automatically fill in the missing modifier
				array_unshift($posParts, "auto");
			} else throw new BackendFormException("Invalid position given, when trying to move element: $el->id (given position: $position)");
		}
		$modifier = strtolower(trim($posParts[0]));
		if (!in_array($modifier, ["auto", "top", "bottom", "before", "after"]))
			throw new BackendFormException("Invalid position given, only the values \"top\", \"bottom\", \"before\" and \"after\" are allowed! (given position: $position)");
		
		// Try to find the element references or skip
		try {
			$elId = ($this->elIsContainer($el) ? "_" : "") . $el->getId();
			unset($el->getParent()->elements[$elId]);
			$targetEl = $this->getElementInternal($posParts[1]);
		} catch (BackendFormException $e) {
			return FALSE;
		}
		
		// Detect the element types
		$elIsTab = $this->elIsTab($el);
		$elIsContainer = !$elIsTab && $this->elIsContainer($el);
		$targetIsTab = $this->elIsTab($targetEl);
		$targetIsContainer = !$targetIsTab && $this->elIsContainer($targetEl);
		
		// Prepare internal keys
		$targetElId = ($targetIsContainer ? "_" : "") . $targetEl->getId();
		
		// Resolve automatic modifier
		if ($modifier === "auto") $modifier = $targetIsContainer || $targetIsTab ? "bottom" : "after";
		
		if ($elIsTab) {
			// Element is a tab
			if (!$targetIsTab) {
				// Find the tab of the element
				$targetEl = $targetEl->getParent(); // Tab or container
				if (!$this->elIsTab($targetEl)) $targetEl = $targetEl->getParent(); // Tab for sure
				$targetElId = $targetEl->getId();
			}
			
			// Move the tab around
			$modifier = $this->applyModifierFallback($modifier);
			$targetEl = $targetEl->getParent();
			$targetEl->addElementAt($elId, $el, $modifier, $targetElId);
			
		} else {
			// Element is a field or a container
			if ($targetIsTab) {
				// Move the field/container to another tab
				/** @var AbstractFormContainer $targetEl */
				if ($modifier === "before") {
					$targetEl = $targetEl->getParent()->getSiblingElement($targetElId, FALSE);
					$modifier = "bottom";
				} else if ($modifier === "after") {
					$targetEl = $targetEl->getParent()->getSiblingElement($targetElId, TRUE);
					$modifier = "top";
				}
				$targetEl->addElementAt($elId, $el, $modifier);
			} else if ($targetIsContainer) {
				// Move the field/container into/around a container
				/** @var AbstractFormContainer $targetEl */
				
				// Special handling when moving containers
				if ($elIsContainer) $modifier = $this->applyModifierFallback($modifier);
				
				// Make sure to get the parent element when we are using before and after
				if ($modifier === "before" || $modifier === "after")
					$targetEl = $targetEl->getParent();
				$targetEl->addElementAt($elId, $el, $modifier, $targetElId);
			} else {
				// Move the fields / containers
				$modifier = $this->applyModifierFallback($modifier);
				$targetEl = $targetEl->getParent();
				$targetEl->addElementAt($elId, $el, $modifier, $targetElId);
			}
		}
		
		// Update the element's parent
		$el->parent = $targetEl;
		
		return TRUE;
	}
	
	/**
	 * Can be used to remove any given element from the list
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function removeElement(string $id): bool {
		$result = $this->removeElementInternal($id);
		$this->ensureInitialTab();
		return $result;
	}
	
	/**
	 * Removes all current elements from the form, leaving you with a clean slate
	 *
	 * @return bool
	 */
	public function removeAllElements(): bool {
		$result = TRUE;
		foreach ($this->elements as $element) {
			$r = $this->removeElementInternal($element->getId());
			if (!$r && $result) $result = FALSE;
		}
		$this->ensureInitialTab();
		return $result;
	}
	
	/**
	 * Return the list of all registered tab instances
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormTab[]
	 */
	public function getTabs(): array {
		return $this->getChildren();
	}
	
	/**
	 * Similar to getTabs() but returns only the tab keys instead of the whole object
	 *
	 * @return array
	 */
	public function getTabKeys(): array {
		return array_keys($this->getChildren());
	}
	
	/**
	 * Returns true if a given tab exists, false if not
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasTab(string $id): bool {
		return $this->hasElementInternal($id, static::TYPE_TAB);
	}
	
	/**
	 * Returns the list of all registered fields that are currently inside the layout
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField[]
	 */
	public function getFields(): array {
		return $this->getAllOfType(static::TYPE_ELEMENT);
	}
	
	/**
	 * Similar to getFields() but only returns the keys of the fields instead of the whole object
	 *
	 * @return array
	 */
	public function getFieldKeys(): array {
		return array_keys($this->getFields());
	}
	
	/**
	 * Returns true if a field with the given id is registered in this form
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasField(string $id): bool {
		return $this->hasElementInternal($id, static::TYPE_ELEMENT);
	}
	
	/**
	 * Can be used to set raw config values, that are not implemented in this facade.
	 * Set either key => value pairs, or an Array of key => value pairs
	 *
	 * @param array|string|int $key   Either a key to set the given $value for, or an array of $key => $value pairs
	 * @param null             $value The value to set for the given $key (if $key is not an array)
	 *
	 * @return $this
	 */
	public function setRaw($key, $value = NULL) {
		if (is_array($key)) $this->config = $key;
		else $this->config[$key] = $value;
		return $this;
	}
	
	/**
	 * Returns the raw configuration array for this object
	 * @return array
	 */
	public function getRaw(): array {
		return $this->config;
	}
	
	/**
	 * The logic requires that there is always a initial, "general" tab in existence as the form engine does not support
	 * forms without tabs. Implement this method to make sure the first element in $elements is a valid tab object
	 *
	 * @return void
	 */
	abstract protected function ensureInitialTab();
	
	/**
	 * Internal helper to make sure that top will get before and bottom gets after
	 * for non container elements
	 *
	 * @param string $modifier
	 *
	 * @return string
	 */
	protected function applyModifierFallback(string $modifier): string {
		if ($modifier === "top") return "before";
		if ($modifier === "bottom") return "after";
		return $modifier;
	}
	
	/**
	 * Internal helper which can be used to generate a list of elements contained in this form.
	 *
	 * @param int $type The type of the list to generate. Use the TYPE_... constants to specify the type
	 *
	 * @return array
	 */
	protected function getAllOfType(int $type): array {
		$finds = [];
		try {
			$this->getElementInternal("probablyNotExisting" . md5(microtime(TRUE)), $type, $finds);
		} catch (Exception $e) {
			switch ($type) {
				case static::TYPE_CONTAINER:
					return (array)$finds["containers"];
				case static::TYPE_TAB:
					return (array)$finds["tabs"];
				case static::TYPE_ELEMENT:
					return (array)$finds["elements"];
				default:
					return Arrays::attach(...array_values($finds));
			}
		}
		return $finds;
	}
	
	/**
	 * Converts the logical object hierarchy into a multidimensional array
	 *
	 * This is used to generate the typo3 tca showitem strings and the order of flexform fields
	 *
	 * @return array
	 */
	protected function getLayoutArray(): array {
		$layout = [];
		foreach ($this->getChildren() as $k => $tab) {
			/** @var AbstractFormTab $tab */
			$layout[$tab->getId()] = ["@node" => $tab];
			foreach ($tab->getChildren() as $_k => $child) {
				if ($child instanceof AbstractFormContainer) {
					$container = ["@node" => $child];
					foreach ($child->getChildren() as $__k => $element)
						$container[] = $element;
					$layout[$tab->getId()][$child->getId()] = $container;
				} else {
					$layout[$tab->getId()][] = $child;
				}
			}
		}
		return $layout;
	}
}