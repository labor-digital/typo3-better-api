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
 * Last modified: 2020.03.18 at 19:13
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;


use TYPO3\CMS\Extbase\Reflection\ClassSchema;

/**
 * Class ClassSchemaFilterEvent
 *
 * Triggered inside the ExtendedReflectionService to allow dynamic changes
 * to class schemata before the container creates a new instance of a class
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ClassSchemaFilterEvent {
	/**
	 * The instance of the class schema to be filtered
	 * @var ClassSchema
	 */
	protected $schema;
	
	/**
	 * Either the class name or the object to create a schema for
	 * @var mixed
	 */
	protected $classNameOrObject;
	
	/**
	 * ClassSchemaFilterEvent constructor.
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ClassSchema $schema
	 * @param                                           $classNameOrObject
	 */
	public function __construct(ClassSchema $schema, $classNameOrObject) {
		$this->schema = $schema;
		$this->classNameOrObject = $classNameOrObject;
	}
	
	/**
	 * Returns the instance of the class schema to be filtered
	 * @return \TYPO3\CMS\Extbase\Reflection\ClassSchema
	 */
	public function getSchema(): ClassSchema {
		return $this->schema;
	}
	
	/**
	 * Sets the instance of the class schema to be filtered
	 *
	 * @param \TYPO3\CMS\Extbase\Reflection\ClassSchema $schema
	 *
	 * @return ClassSchemaFilterEvent
	 */
	public function setSchema(ClassSchema $schema): ClassSchemaFilterEvent {
		$this->schema = $schema;
		return $this;
	}
	
	/**
	 * Returns either the class name or the object to create a schema for
	 * @return mixed
	 */
	public function getClassNameOrObject() {
		return $this->classNameOrObject;
	}
	
	/**
	 * Always returns the class name of the class to create a schema for
	 * @return string
	 */
	public function getClassName(): string {
		if (is_string($this->classNameOrObject)) return $this->classNameOrObject;
		return get_class($this->classNameOrObject);
	}
	
}