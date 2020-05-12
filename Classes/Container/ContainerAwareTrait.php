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
 * Last modified: 2020.05.12 at 11:46
 */

namespace LaborDigital\Typo3BetterApi\Container;

/**
 * Trait ContainerAwareTrait
 *
 * Makes any class container aware even if your class was loaded without dependency injection
 * the getContainer() method will return the container instance!
 *
 * @package LaborDigital\Typo3BetterApi\Container
 */
trait ContainerAwareTrait {
	
	/**
	 * Holds the container instance if it was injected
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $__container;
	
	/**
	 * Injects the container instance if possible
	 *
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function injectContainer(TypoContainerInterface $container) {
		$this->__container = $container;
	}
	
	/**
	 * Returns the instance of the container
	 *
	 * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected function Container(): TypoContainerInterface {
		return isset($this->__container) ? $this->__container :
			$this->__container = TypoContainer::getInstance();
	}
	
	/**
	 * You can use this method if you want to lazy load an object using the container instance.
	 *
	 * Note: You should try to avoid this method as hard as possible!
	 * This is the opposite of IoC and how you should use dependency injection.
	 * However: There are some good examples of where you might want to use it:
	 * Inside Models, or callbacks that don't support dependency injection for example.
	 *
	 * @param string $class The class or interface you want to retrieve the object for
	 * @param array  $args  Optional, additional constructor arguments
	 *
	 * @return mixed
	 */
	protected function getInstanceOf(string $class, array $args = []) {
		return $this->Container()->get($class, ["args" => $args]);
	}
}