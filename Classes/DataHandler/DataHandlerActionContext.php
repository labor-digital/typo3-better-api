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
 * Last modified: 2020.03.21 at 20:48
 */

namespace LaborDigital\Typo3BetterApi\DataHandler;

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;

class DataHandlerActionContext {
	use CommonServiceLocatorTrait;
	
	/**
	 * True if the value of this element was changed -> meaning we have to update it
	 * @var bool
	 */
	protected $valueDirty = FALSE;
	
	/**
	 * This is true if the current context applies to a table,
	 * and not a specific field in said table.
	 * @var bool
	 */
	protected $appliesToTable = FALSE;
	
	/**
	 * The action that is currently performed in the backend
	 * @var string
	 */
	protected $action = "save";
	
	/**
	 * The name of the table this element is part of
	 * @var string
	 */
	protected $tableName = "";
	
	/**
	 * The uid of the current element's record in the table defined in $tableName
	 * If this has a new record's context this will be a string of some sort
	 * @var int|string
	 */
	protected $uid = 0;
	
	/**
	 * The key of the field that represents the custom element
	 * @var string
	 */
	protected $key;
	
	/**
	 * The value for the current element either loaded from the database
	 * or given to the dataHandler in the backend save filter
	 *
	 * @var mixed
	 */
	protected $value;
	
	/**
	 * The row of the record we are currently working with.
	 * This does not have to be the whole row of the record!
	 * In the backend save filter this is probably just a part of the record we received
	 * @var array
	 */
	protected $row = [];
	
	/**
	 * The TCA config for this elements table column
	 * @var array
	 */
	protected $config = [];
	
	/**
	 * The instance of the event, which may contain additional data, that was not handled by this interface
	 * @var object
	 */
	protected $event;
	
	/**
	 * The path on which the value is stored
	 * @var array
	 */
	protected $path;
	
	/**
	 * Is used internally to inject the context array into this object
	 *
	 * @param array $context
	 */
	public function __setContextArray(array $context) {
		foreach ($context as $k => $v)
			if (property_exists($this, $k)) $this->$k = $v;
	}
	
	/**
	 * This method returns true if the current context applies to a table,
	 * and not a specific field in said table.
	 *
	 * @return bool
	 */
	public function isAppliesToTable(): bool {
		return $this->appliesToTable;
	}
	
	/**
	 * The action that is currently performed in the backend
	 *
	 * Possible values are:
	 *  - save: The element was saved, either a new element or an update to an existing element
	 *  - formFilter: Called when the backend is currently rendering the form
	 *  - copy: Creates a copy of an existing record
	 *  - move: Moves a record to another location
	 *  - delete: A record is currently deleted
	 *  - undelete: A record is restored from the recycle bin
	 *  - localize: ?
	 *  - copyToLanguage: ?
	 *  - inlineLocalizeSynchronize: ?
	 *  - version: ?
	 *
	 * @return string
	 */
	public function getAction(): string {
		return $this->action;
	}
	
	/**
	 * Returns the name of the table this element is part of
	 * @return string
	 */
	public function getTableName(): string {
		return $this->tableName;
	}
	
	/**
	 * Returns the uid of the current element's record in the table defined in $tableName
	 * If this has a new record's context this will be a string of some sort
	 * @return int|string
	 */
	public function getUid() {
		return $this->uid;
	}
	
	/**
	 * Returns the key of either a field, or the name of a table that is currently
	 * used by the context. If isAppliesToTable() returns true, this will return the table name,
	 * otherwise a field name.
	 *
	 * @return string
	 */
	public function getKey(): string {
		return $this->key;
	}
	
	/**
	 * Returns the value for the current element either loaded from the database
	 * or given to the dataHandler in the backend save filter
	 *
	 * If isAppliesToTable() returns true, this will return the table row.
	 *
	 * @return mixed
	 */
	public function getValue() {
		return $this->value;
	}
	
	/**
	 * Can be used to change the value of this field to anything else.
	 *
	 * @param mixed $value
	 *
	 * @return DataHandlerActionContext
	 */
	public function setValue($value) {
		$this->valueDirty = TRUE;
		$this->value = $value;
		return $this;
	}
	
	/**
	 * Returns the row of the record we are currently working with.
	 * This does not have to be the whole row of the record!
	 * In the backend save filter this is probably just a part of the record we received
	 *
	 * @return array
	 */
	public function getRow(): array {
		return $this->row;
	}
	
	/**
	 * Returns he TCA config for this elements table column if isAppliesToTable() returns false,
	 * if isAppliesToTable() returns true this will return the whole table TCA
	 *
	 * @return array
	 */
	public function getConfig(): array {
		return $this->config;
	}
	
	/**
	 * Returns the instance of the event, which may contain additional data, that was not handled by this interface
	 * @return object
	 */
	public function getEvent(): object {
		return $this->event;
	}
	
	/**
	 * Returns true if the value of this element was changed -> meaning we have to update it
	 *
	 * @return bool
	 */
	public function isValueDirty(): bool {
		return $this->valueDirty;
	}
	
	/**
	 * Returns the path, inside the current row on which the value is stored
	 * This returns an empty array if isAppliesToTable() returns true.
	 * @return array
	 */
	public function getPath(): array {
		return $this->path;
	}
	
}