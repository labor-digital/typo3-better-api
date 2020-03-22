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
 * Last modified: 2020.03.19 at 13:24
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use Closure;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerRecordInfoFilterEvent
 *
 * Dispatched when the data handler requests information about a record from the database
 * Can be used to modify the requested fields or table name
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class DataHandlerRecordInfoFilterEvent {
	
	/**
	 * The name of the table to request the information from
	 * @var string
	 */
	protected $tableName;
	
	/**
	 * UID of the record from $tableName
	 * @var int
	 */
	protected $id;
	
	/**
	 * The field list for the SELECT query, eg. "*" or "uid,pid,...
	 * @var string
	 */
	protected $fieldList;
	
	/**
	 * The currently executed data handler instance
	 * @var \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	protected $dataHandler;
	
	/**
	 * The closure that is used to request the record information from the database
	 * Can be modified to change the lookup method
	 * @var \Closure
	 */
	protected $concreteInfoProvider;
	
	/**
	 * DataHandlerRecordInfoFilterEvent constructor.
	 *
	 * @param string                                   $tableName
	 * @param int                                      $id
	 * @param string                                   $fieldList
	 * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
	 * @param \Closure                                 $concreteInfoProvider
	 */
	public function __construct(string $tableName, int $id, string $fieldList, DataHandler $dataHandler, Closure $concreteInfoProvider) {
		$this->tableName = $tableName;
		$this->id = $id;
		$this->fieldList = $fieldList;
		$this->dataHandler = $dataHandler;
		$this->concreteInfoProvider = $concreteInfoProvider;
	}
	
	/**
	 * Returns the id of the entry that is requested
	 * @return int|string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Returns the name of the table that is currently requested
	 * @return string
	 */
	public function getTableName(): string {
		return $this->tableName;
	}
	
	/**
	 * Returns the currently executed data handler instance
	 * @return \TYPO3\CMS\Core\DataHandling\DataHandler
	 */
	public function getDataHandler(): DataHandler {
		return $this->dataHandler;
	}
	
	/**
	 * Returns the field list for the SELECT query, eg. "*" or "uid,pid,...
	 * @return string
	 */
	public function getFieldList(): string {
		return $this->fieldList;
	}
	
	/**
	 * Updates the field list for the SELECT query, eg. "*" or "uid,pid,...
	 *
	 * @param string $fieldList
	 *
	 * @return DataHandlerRecordInfoFilterEvent
	 */
	public function setFieldList(string $fieldList): DataHandlerRecordInfoFilterEvent {
		$this->fieldList = $fieldList;
		return $this;
	}
	
	/**
	 * Returns the closure that is used to request the record information from the database
	 * @return \Closure
	 */
	public function getConcreteInfoProvider(): Closure {
		return $this->concreteInfoProvider;
	}
	
	/**
	 * Updates the closure that is used to request the record information from the database
	 *
	 * @param \Closure $concreteInfoProvider
	 *
	 * @return DataHandlerRecordInfoFilterEvent
	 */
	public function setConcreteInfoProvider(Closure $concreteInfoProvider): DataHandlerRecordInfoFilterEvent {
		$this->concreteInfoProvider = $concreteInfoProvider;
		return $this;
	}
}