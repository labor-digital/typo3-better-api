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
 * Last modified: 2020.03.19 at 13:04
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;


use Neunerlei\Inflection\Inflector;
use ReflectionMethod;
use ReflectionObject;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

trait AutomaticAspectGetTrait {
	
	/**
	 * The internal storage of possible properties to retrieve from this aspect.
	 * This storage is automatically generated based on the method names
	 * @var array|null
	 */
	protected $properties;
	
	/**
	 * Can be used inside the aspect's "get" method to automatically find the properties based on the public methods.
	 *
	 * @param string $name
	 *
	 * @return mixed
	 * @throws \TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException
	 */
	protected function handleGet(string $name) {
		$properties = $this->findPropertyList();
		if (isset($properties[$name])) return call_user_func([$this, $properties[$name]]);
		throw new AspectPropertyNotFoundException("There is no property called $name in this aspect.");
	}
	
	/**
	 * Internal helper to find the the list of possible properties by the public method names of the aspect class
	 * @return array
	 */
	protected function findPropertyList(): array {
		if (!is_null($this->properties)) return $this->properties;
		$properties = [];
		$ref = new ReflectionObject($this);
		foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			$methodName = $method->getName();
			if ($methodName === "get") continue;
			if (!preg_match("~^(is|has|get)~si", $methodName)) continue;
			foreach ($method->getParameters() as $param)
				if ($param->isDefaultValueAvailable()) continue 2;
			$propertyName = $methodName;
			if (stripos($methodName, "get") === 0) $propertyName = Inflector::toProperty($methodName);
			$properties[$propertyName] = $methodName;
		}
		$this->properties = $properties;
		return $properties;
	}
}