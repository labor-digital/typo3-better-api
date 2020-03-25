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
 * Last modified: 2020.03.21 at 16:52
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\OptionList;


use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtConfigOptionInterface;

class ExtConfigOptionList {
	use ExtConfigOptionListTrait;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * The list of instantiated options in the list
	 * @var array
	 */
	protected $options = [];
	
	/**
	 * The instance of the trait generator -> always use getExtensionHandler()!
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionTraitGenerator|null
	 */
	protected $extensionHandler;
	
	/**
	 * ExtConfigOptionList constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext       $context
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function __construct(ExtConfigContext $context, TypoContainerInterface $container) {
		$this->container = $container;
		$this->context = $context;
	}
	
	/**
	 * Returns the context object for the current extension configuration,
	 * containing multiple references used by the config loader.
	 */
	public function getContext(): ExtConfigContext {
		return $this->context;
	}
	
	/**
	 * Returns true if a certain option exists, false if not.
	 * This is useful for checking if a certain extension is installed.
	 *
	 * @param string $optionName
	 *
	 * @return bool
	 */
	public function hasOption(string $optionName): bool {
		return method_exists($this, $optionName) ||
			!empty($this->getExtensionHandler()->getClassNameForOption($optionName));
	}
	
	/**
	 * Fallback for all non existing options
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtConfigOptionInterface
	 * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
	 */
	public function __call($name, $arguments) {
		// Try to find a not-yet compiled option -> When an extension is activated
		$class = $this->getExtensionHandler()->getClassNameForOption($name);
		if (!empty($class)) return $this->getOrCreateOptionInstance($class);
		
		// No fallback found
		throw new ExtConfigException("Could not find option \"$name\", because it was not registered!");
	}
	
	/**
	 * Returns the instance of the extension handler
	 * This is used to check if a new option has been added (e.g. when the extension is activated)
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\OptionList\ExtConfigOptionTraitGenerator
	 */
	protected function getExtensionHandler(): ExtConfigOptionTraitGenerator {
		if (!empty($this->extensionHandler)) return $this->extensionHandler;
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->extensionHandler =
			$this->context->ExtensionRegistry->getExtensionHandler(ExtConfigExtensionInterface::TYPE_OPTION_LIST_ENTRY);
	}
	
	/**
	 * Internal helper to create and instantiate a new option object
	 *
	 * @param string $className
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtConfigOptionInterface
	 */
	protected function getOrCreateOptionInstance(string $className): ExtConfigOptionInterface {
		if (isset($this->options[$className])) return $this->options[$className];
		/** @var ExtConfigOptionInterface $i */
		$i = $this->container->get($className);
		$i->setContext($this->context);
		$this->context->EventBus->addSubscriber($i);
		return $this->options[$className] = $i;
	}
}