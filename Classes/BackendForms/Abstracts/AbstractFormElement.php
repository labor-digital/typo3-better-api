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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Abstracts;


use LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

abstract class AbstractFormElement {
	
	/**
	 * The context in which the tab was generated
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * Contains the label for the tab
	 * @var string
	 */
	protected $label;
	
	/**
	 * A id for this element
	 * @var string
	 */
	protected $id = 0;
	
	/**
	 * The parent element of this element (either the form, or a container element)
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer
	 */
	protected $parent;
	
	/**
	 * The parent form / table definition of this element
	 * @var AbstractForm|TcaTable|FlexForm
	 */
	protected $form;
	
	/**
	 * The configuration of this element
	 * @var array
	 */
	protected $config = [];
	
	/**
	 * AbstractFormElement constructor.
	 *
	 * @param string           $id
	 * @param ExtConfigContext $context
	 */
	public function __construct(string $id, ExtConfigContext $context) {
		$this->id = $id;
		$this->context = $context;
	}
	
	/**
	 * Can be used to move this element to another position.
	 *
	 * Position can be defined as "field", "container" or "0" (tabs) to move the element AFTER the defined element.
	 *
	 * You may also use the following modifiers:
	 *    - before:field positions the element in front of the element with "field" as id
	 *    - after:field positions the element after the element with "field" as id
	 *    - top:container positions the element as first element of a container/tab
	 *    - bottom:container positions the element as last element of a container/tab
	 *
	 * @param string $position Either the position to move the field to, or the field will be added to the end of the
	 *                         FIRST possible tab
	 *
	 * @return $this
	 */
	public function moveTo(string $position = "0") {
		$this->getForm()->moveElementInstance($this, $position);
		return $this;
	}
	
	/**
	 * Returns the parent element of this element.
	 * Either the form, a tab or a container element.
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * Internal helper which is used to inject the parent element of this element
	 *
	 * @param \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer $parent
	 */
	public function __setParent(AbstractFormContainer $parent) {
		$this->parent = $parent;
	}
	
	/**
	 * Returns the instance of the parent form / parent table
	 * @return AbstractForm|TcaTable|FlexForm
	 */
	public function getForm() {
		return $this->form;
	}
	
	/**
	 * Internal helper which is used to inject the reference of the parent form of this element
	 *
	 * @param \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractForm $form
	 */
	public function __setForm(AbstractForm $form) {
		$this->form = $form;
	}
	
	/**
	 * Returns the current id for this element
	 * @return string
	 */
	public function getId(): string {
		return $this->id;
	}
	
	/**
	 * Returns the currently set label for this tab
	 * @return string
	 */
	public function getLabel(): string {
		return (string)$this->label;
	}
	
	/**
	 * Can be used to set the label for this tab
	 *
	 * @param string|null $label
	 *
	 * @return $this
	 */
	public function setLabel(?string $label) {
		$this->label = $label;
		return $this;
	}
	
	/**
	 * Returns true if the element has a defined label, false if not
	 * @return bool
	 */
	public function hasLabel(): bool {
		return !is_null($this->label);
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
		
		// Special handling for the labels
		if (isset($this->config["label"])) $this->label = $this->config["label"];
		unset($this->config["label"]);
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
	 * Removes this element from the form
	 */
	public function remove(): void {
		$id = ($this instanceof AbstractFormContainer &&
			!$this instanceof AbstractFormTab ? "_" : "") . $this->getId();
		$this->getParent()->removeElement($id);
	}
}