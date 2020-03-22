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
 * Last modified: 2020.03.19 at 03:03
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;


use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use Neunerlei\Arrays\Arrays;

class TcaField extends AbstractFormField {
	use LayoutMetaTrait;
	
	/**
	 * Holds the flexForm configuration if there is one
	 * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaFieldFlexForm
	 */
	protected $flexForm;
	
	/**
	 * Returns the database table name for the current field
	 * @return string
	 */
	public function getTableName(): string {
		return $this->getForm()->getTableName();
	}
	
	/**
	 * Returns the database name of the current field
	 * @return string
	 */
	public function getColumnName(): string {
		return $this->id;
	}
	
	/**
	 * With this you can define the sql syntax of your database column.
	 *
	 * The $definition should look like "varchar(512) DEFAULT ''  NOT NULL", or "tinyint(4)"
	 * The $definition should NOT contain the table or the column name!
	 *
	 * @param string $definition The column definition to set for this column
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
	 */
	public function setSqlDefinition(string $definition): TcaField {
		$this->context->SqlGenerator->setDefinitionFor(
			$this->getTableName(), $this->getId(), $definition);
		return $this;
	}
	
	/**
	 * Returns the sql configuration of this field, or an empty string if there is none
	 *
	 * @return string
	 */
	public function getSqlDefinition(): string {
		return $this->context->SqlGenerator->getDefinitionFor($this->getTableName(), $this->getId());
	}
	
	/**
	 * Removes this field from the sql table.
	 * You should only use this if you have a display-only field that should not store any data for itself
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
	 */
	public function useWithoutSqlField(): TcaField {
		$this->context->SqlGenerator->removeDefinitionFor($this->getTableName(), $this->getId());
		return $this;
	}
	
	/**
	 * Returns the flex form configuration for this field.
	 *
	 * Attention: If you use this method your field will automatically converted into a flex field!
	 * If you don't convert the field automatically, but check first: take a look at the hasFlexFormConfig() method.
	 *
	 * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaFieldFlexForm
	 */
	public function getFlexFormConfig(): TcaFieldFlexForm {
		// Check if we already have a flex form config
		if (isset($this->flexForm)) return $this->flexForm;
		
		// Make sure we are rendering as flex form
		$this->config["config"]["type"] = "flex";
		unset($this->config["config"]["renderType"]);
		$this->setSqlDefinition("mediumtext");
		
		// Create new flex form config
		return $this->flexForm = $this->context->getInstanceOf(TcaFieldFlexForm::class, [$this, $this->config, $this->context]);
	}
	
	/**
	 * Returns true if this field is configured using a flex form configuration object.
	 *
	 * @return bool
	 */
	public function hasFlexFormConfig(): bool {
		return isset($this->flexForm) && $this->config["config"]["type"] === "flex";
	}
	
	/**
	 * Returns true if the current field is considered a "flexForm" field, false if not
	 *
	 * @return bool
	 */
	public function isFlexForm(): bool {
		return $this->config["config"]["type"] === "flex";
	}
	
	/**
	 * @inheritDoc
	 */
	public function setRaw($key, $value = NULL) {
		// Store ds values to allow automatic config flushing
		$dsOld = json_encode(Arrays::getPath($this->config, "config.[ds,ds_pointerField]"));
		
		// Update the values
		parent::setRaw($key, $value);
		
		// Load sql configuration
		$sql = Arrays::getPath($this->config, "@sql", NULL);
		if ($sql !== NULL) $this->setSqlDefinition((string)$sql);
		unset($this->config["@sql"]);
		
		// Load flex form configuration
		if ($this->config["config"]["type"] === "flex" && !empty($this->flexForm)) {
			$dsNew = json_encode(Arrays::getPath($this->config, "config.[ds,ds_pointerField]"));
			// Reset the flex configuration
			if ($dsNew !== $dsOld) $this->flexForm = NULL;
		}
		
		// Done
		return $this;
	}
	
	/**
	 * Internal helper to prepare the field information and to return the generated tca config
	 *
	 * @return array
	 */
	public function __build(): array {
		return $this->getRaw();
	}
	
	/**
	 * @inheritDoc
	 */
	public function getRaw(): array {
		
		// Check if we have to build the flex form configuration
		if ($this->config["config"]["type"] === "flex" && !empty($this->flexForm)) {
			$config = $this->flexForm->__build();
			$this->config = Arrays::merge($this->config, $config);
		}
		
		// Do the default stuff
		return parent::getRaw();
	}
	
	
}