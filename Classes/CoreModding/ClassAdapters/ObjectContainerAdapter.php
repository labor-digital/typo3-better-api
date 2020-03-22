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

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters;


use ReflectionObject;
use TYPO3\CMS\Extbase\Object\Container\Container;

/**
 * Class ObjectContainerAdapter
 *
 * ATTENTION: THIS CLASS WILL NOT STAY, IT IS TEMPORARY FOR v9
 * @package LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters
 */
class ObjectContainerAdapter extends Container {
	
	/**
	 * This is a temporary workaround to remove singletons in typo3 v9
	 * This will be removed in the v10 implementation
	 *
	 * @param \TYPO3\CMS\Extbase\Object\Container\Container $container
	 * @param string                                        $className
	 */
	public static function removeSingleton(Container $container, string $className) {
		$ref = new ReflectionObject($container);
		$prop = $ref->getProperty("singletonInstances");
		$prop->setAccessible(TRUE);
		$instanceList = $prop->getValue($container);
		unset($instanceList[$className]);
		$prop->setValue($container, $instanceList);
	}
}