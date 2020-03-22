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
 * Last modified: 2020.03.19 at 02:53
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;


use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Arrays\Arrays;

/**
 * Class CustomElementContext
 * @package LaborDigital\Typo3BetterApi\BackendForms\CustomElements
 *
 * @property \TYPO3\CMS\Core\Imaging\IconFactory $IconFactory
 */
class CustomElementContext {
	use CommonServiceLocatorTrait;
	
	/**
	 * The raw data, stored on the root node element
	 * @var array
	 */
	protected $rawData;
	
	/**
	 * The real node in the form engine, that serves as a wrapper for our easier interface to form elements
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementNode
	 */
	protected $rootNode;
	
	/**
	 * Default width value for a couple of elements like text
	 *
	 * @var int
	 */
	protected $defaultInputWidth = 30;
	
	/**
	 * Minimum width value for a couple of elements like text
	 *
	 * @var int
	 */
	protected $minInputWidth = 10;
	
	/**
	 * Maximum width value for a couple of elements like text
	 *
	 * @var int
	 */
	protected $maxInputWidth = 50;
	
	/**
	 * If this is set to false, the outer html elements around the form element,
	 * including the field wizards will not be added to the result string!
	 * @var bool
	 */
	protected $applyOuterWrap = TRUE;
	
	/**
	 * CustomFormElementContext constructor.
	 *
	 * @param array $injection The list of properties that are injected into the context
	 */
	public function __construct(array $injection) {
		foreach ($injection as $k => $v)
			if (property_exists($this, $k)) $this->$k = $v;
		if (isset($injection["iconFactory"])) $this->addToServiceMap(["IconFactory" => $injection["iconFactory"]]);
	}
	
	/**
	 * Returns the raw data received by the root node
	 * @return array
	 */
	public function getRawData(): array {
		return $this->rawData;
	}
	
	/**
	 * Can be used to update the raw data of the rendering root node
	 *
	 * @param array $rawData
	 *
	 * @return CustomElementContext
	 */
	public function setRawData(array $rawData): CustomElementContext {
		$this->rawData = $rawData;
		return $this;
	}
	
	/**
	 * Returns the form factory node, that serves as a wrapper for your CustomFormElement
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementNode
	 */
	public function getRootNode(): CustomElementNode {
		return $this->rootNode;
	}
	
	/**
	 * Returns the default width value for a couple of elements like text
	 * @return int
	 */
	public function getDefaultInputWidth(): int {
		return $this->defaultInputWidth;
	}
	
	/**
	 * Returns the minimum width value for a couple of elements like text
	 * @return int
	 */
	public function getMinInputWidth(): int {
		return $this->minInputWidth;
	}
	
	/**
	 * Returns the maximum width value for a couple of elements like text
	 * @return int
	 */
	public function getMaxInputWidth(): int {
		return $this->maxInputWidth;
	}
	
	/**
	 * Returns the UID of the record this field is part of
	 * @return int
	 */
	public function getRecordUid(): int {
		return (int)Arrays::getPath($this->rawData, "vanillaUid");
	}
	
	/**
	 * Returns the page id of the record this field is part of
	 * @return int
	 */
	public function getRecordPid(): int {
		return (int)Arrays::getPath($this->rawData, "effectivePid");
	}
	
	/**
	 * Returns the currently set value for this field
	 * @return array|mixed|null
	 */
	public function getValue() {
		return Arrays::getPath($this->rawData, ["parameterArray", "itemFormElValue"]);
	}
	
	/**
	 * Returns the complete database record this field is part of
	 * @return array
	 */
	public function getRow(): array {
		return Arrays::getPath($this->rawData, "databaseRow", []);
	}
	
	/**
	 * Returns the name of the database table this field is part of
	 * @return string
	 */
	public function getTableName(): string {
		return Arrays::getPath($this->rawData, ["tableName"], "");
	}
	
	/**
	 * Returns the name of the database field in the record
	 * @return string
	 */
	public function getFieldName(): string {
		return Arrays::getPath($this->rawData, ["fieldName"], "");
	}
	
	/**
	 * Returns the field name that should be put as "name" attribute of the HTML tag,
	 * representing this field
	 * @return string
	 */
	public function getRenderName(): string {
		return Arrays::getPath($this->rawData, ["parameterArray", "itemFormElName"], "");
	}
	
	/**
	 * The HTML ID that should be set for this field
	 * @return string
	 */
	public function getRenderId(): string {
		return Arrays::getPath($this->rawData, ["parameterArray", "itemFormElID"], "");
	}
	
	/**
	 * Returns the prepared TCA configuration for this field
	 * @return array
	 */
	public function getConfig(): array {
		return Arrays::getPath($this->rawData, ["parameterArray", "fieldConf"], []);
	}
	
	/**
	 * Returns the list of additional options that were passed when the field
	 * was applied using the fieldPreset applier.
	 *
	 * @return array
	 */
	public function getOptions(): array {
		return Arrays::getPath($this->rawData, ["parameterArray", "fieldConf", "config", "customElementOptions"], []);
	}
	
	/**
	 * Can be used to return a single option, or returns the default value
	 *
	 * @param array|string $path    The key, or the path to look up
	 * @param null         $default An optional default value to return if the key/path was not found in the options
	 *                              array
	 *
	 * @return array|mixed|null
	 */
	public function getOption($path, $default = NULL) {
		return Arrays::getPath($this->getOptions(), $path, $default);
	}
	
	/**
	 * Returns the registered class of the registered custom-element for this field.
	 * If this returns an empty string, the space-time-continuum will explode in around 30 seconds...
	 * @return string
	 */
	public function getElementClass(): string {
		return Arrays::getPath($this->rawData, ["parameterArray", "fieldConf", "config", "customElementClass"], "");
	}
	
	/**
	 * Returns true if the outer html wrap around your form field will be rendered, false if not
	 * @return bool
	 */
	public function isApplyOuterWrap(): bool {
		return $this->applyOuterWrap;
	}
	
	/**
	 * Sets if the outer html wrap around your form field will be rendered or not
	 *
	 * @param bool $applyOuterWrap
	 *
	 * @return CustomElementContext
	 */
	public function setApplyOuterWrap(bool $applyOuterWrap): CustomElementContext {
		$this->applyOuterWrap = $applyOuterWrap;
		return $this;
	}
	
	/**
	 * Calling this method disables the outer html wrap around your form field
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext
	 */
	public function disableOuterWrap(): CustomElementContext {
		$this->applyOuterWrap = FALSE;
		return $this;
	}
}