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
 * Last modified: 2020.03.19 at 03:04
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;


use Closure;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement;
use Neunerlei\Arrays\Arrays;

class TcaTableType extends AbstractTcaTable {
	/**
	 * The list of fields that were loaded using the getField method
	 * @var array
	 */
	protected $loadedFields = [];
	
	/**
	 * The list of palettes that have been loaded using the getPalette() method
	 * @var array
	 */
	protected $loadedPalettes = [];
	
	/**
	 * We use this resolver as a load field tca values either directly from the configured parent object
	 * or from its tca configuration. The type objects don't have direct access to the column's tca config,
	 * because that could cause some unwanted behaviour if the default type gets edited. In that case the
	 * type object would be out of sync. This way we will pull the tca config in the moment we require it,
	 * that is not perfect, but the freshest dataset we can get...
	 *
	 * @var \Closure
	 */
	protected $fieldTcaResolver;
	
	/**
	 * @inheritDoc
	 */
	public function getField(string $id): TcaField {
		// Load the field definition from the linked table
		if (!isset($this->loadedFields[$id])) {
			$this->loadedFields[$id] = TRUE;
			$localConfig = $this->config["columns"][$id];
			if (empty($localConfig)) $localConfig = [];
			$this->config["columns"][$id] = Arrays::merge(call_user_func($this->fieldTcaResolver, $id), $localConfig);
		}
		return parent::getField($id);
	}
	
	/**
	 * You may use this method if you want to resync the configuration of a given field
	 * with the default type. Note: The initially set columnOverrides will be applied again!
	 *
	 * @param string $id
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
	 */
	public function reloadFieldConfig(string $id): TcaField {
		$this->config["columns"][$id] = call_user_func($this->fieldTcaResolver, $id);
		unset($this->loadedFields[$id]);
		$field = $this->getElementInternal($id, static::TYPE_ELEMENT);
		$field->setRaw($this->config["columns"][$id]);
		return $field;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPalette(string $id): TcaPalette {
		$localPalette = parent::getPalette($id);
		$id = ($id[0] !== "_" ? "_" : "") . $id;
		if (!isset($this->loadedPalettes[$id])) {
			$this->loadedPalettes[$id] = TRUE;
			if (in_array($id, $this->getPalettes())) return $localPalette;
			
			// Try to load the palette from the parent
			$parent = $this->getForm()->getParent();
			if ($parent instanceof TcaTable && $parent->hasPalette($id)) {
				$parentPalette = $parent->getPalette($id);
				foreach ($parentPalette->getChildren() as $child) {
					if ($child instanceof TcaPaletteLineBreak) {
						$this->addLineBreak("bottom:" . $id);
					} else {
						/** @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField $child */
						$this->getField($child->getId())->setLabel($child->getLabel())->moveTo("bottom:" . $id);
					}
				}
			}
		}
		return $localPalette;
	}
	
	
	/**
	 * This method can be used to copy the content of a whole tab from the base type to a child type.
	 * This is quite useful if you want to copy tabs like "access" or "language"
	 *
	 * @param string $id The same id you would use to select the tab on the default type.
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTab
	 */
	public function copyTab(string $id): TcaTab {
		
		// Prepare the cloning
		/** @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable $parent */
		$parent = $this->getParent();
		$nextTabId = 0;
		foreach ($this->getTabs() as $tab) {
			if (is_numeric($tab->getId())) $nextTabId = ((int)$tab->getId()) + 1;
			else $nextTabId++;
		}
		
		// Create a new tab on this type
		$parentTab = $parent->getTab($id);
		$tab = $this->getTab($nextTabId);
		$tab->setLabel($parentTab->getLabel());
		foreach ($parentTab->getChildren() as $k => $child) {
			/** @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement $clone */
			$clone = clone $child;
			$clone->parent = $tab;
			$clone->form = $this;
			
			// Handle container elements
			if ($clone instanceof AbstractFormContainer) {
				$clonedElements = [];
				foreach ($clone->getChildren() as $_k => $_child) {
					$_clone = clone $_child;
					$_clone->parent = $clone;
					$_clone->form = $this;
					$clonedElements[$_k] = $_clone;
				}
				$clone->elements = $clonedElements;
			}
			
			// Register the clone
			$tab->elements[$k] = $clone;
		}
		
		// Done
		return $tab;
	}
	
	/**
	 * Internal helper to inject the tca field resolver
	 *
	 * @param \Closure $closure
	 *
	 * @internal
	 */
	public function __setFieldTcaResolver(Closure $closure) {
		$this->fieldTcaResolver = $closure;
	}
	
	/**
	 * Remove the internal load-state references when an element is removed
	 *
	 * @param \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement $el
	 */
	protected function __elRemovalHook(AbstractFormElement $el): void {
		if ($this->elIsContainer($el)) unset($this->loadedPalettes["_" . $el->getId()]);
		else unset($this->loadedFields[$el->getId()]);
	}
}