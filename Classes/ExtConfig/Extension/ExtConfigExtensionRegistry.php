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
 * Last modified: 2020.03.21 at 16:44
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Extension;


use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\FormPresetInterface;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigExtendableFeatureFilterEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use Neunerlei\EventBus\EventBusInterface;

class ExtConfigExtensionRegistry {
	
	/**
	 * The list of all registered ext config extendable feature definition classes
	 * @var array
	 */
	protected $extensions = [];
	
	/**
	 * The list of registered extension handlers by their extension type
	 * @var ExtConfigExtensionHandlerInterface[]
	 */
	protected $extensionHandlers = [];
	
	/**
	 * @var \Neunerlei\EventBus\EventBusInterface
	 */
	protected $eventBus;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * ExtConfigExtensionRegistry constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
	 * @param \Neunerlei\EventBus\EventBusInterface                   $eventBus
	 */
	public function __construct(ExtConfigContext $context, EventBusInterface $eventBus) {
		$this->eventBus = $eventBus;
		$this->context = $context;
	}
	
	/**
	 * Registers a new extension class for a certain extension type.
	 *
	 * @param string $type    The type to extend with this class
	 * @param string $class   The class that is used as extension definition.
	 * @param array  $options Generic option that depend of the type of extension you are providing.
	 *                        See the documentation of the element you are extending on how to use this parameter.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	public function registerExtension(string $type, string $class, array $options = []): ExtConfigExtensionRegistry {
		$this->extensions[$type][$class] = [
			"extKey"  => $this->context->getExtKey(),
			"vendor"  => $this->context->getVendor(),
			"class"   => $class,
			"options" => $options,
		];
		return $this;
	}
	
	/**
	 * Shortcut to register a new ext config option list entry.
	 *
	 * @param string $class   The class to implement a new config option
	 * @param array  $options Additional options
	 *                        - optionName string: by default the option name is generated based on the class
	 *                        name you provide. If you want to override it, supply the name here.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	public function registerOptionListEntry(string $class, array $options = []): ExtConfigExtensionRegistry {
		return $this->registerExtension(ExtConfigExtensionInterface::TYPE_OPTION_LIST_ENTRY, $class, $options);
	}
	
	/**
	 * Shortcut to register a new ext config table preset class
	 *
	 * @param string $class The preset applier class. The given class has to implement the FormPresetInterface
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 * @see FormPresetInterface
	 *
	 */
	public function registerFieldPreset(string $class): ExtConfigExtensionRegistry {
		return $this->registerExtension(ExtConfigExtensionInterface::TYPE_FORM_FIELD_PRESET, $class, []);
	}
	
	/**
	 * Checks if either a type exists, or a certain class has been registered for a type
	 *
	 * @param string      $type  The type to check for
	 * @param string|null $class If given, defines a class to check for in the type.
	 *
	 * @return bool
	 */
	public function hasExtension(string $type, ?string $class = NULL): bool {
		if (!isset($this->extensions[$type])) return FALSE;
		if (empty($class)) return TRUE;
		return isset($this->extensions[$type][$class]);
	}
	
	/**
	 * Returns the registered extensions for a certain type.
	 *
	 * @param string $type
	 *
	 * @return array
	 * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
	 */
	public function getExtensions(string $type): array {
		if (!isset($this->extensions[$type])) throw new ExtConfigException("There are no extensions for the given type: $type");
		return $this->extensions[$type];
	}
	
	/**
	 * Removes either a single extension class, or a whole type of classes from the registry
	 *
	 * @param string      $type  The type to remove / to remove the given class from
	 * @param string|null $class The class to remove from a certain type
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	public function removeExtension(string $type, ?string $class): ExtConfigExtensionRegistry {
		if (empty($class)) unset($this->extensions[$type]);
		else if (isset($this->extensions[$type])) {
			unset($this->extensions[$type][$class]);
			if (empty($this->extensions[$type])) unset($this->extensions[$type]);
		}
		return $this;
	}
	
	/**
	 * Returns the list of registered extension type keys
	 * @return array
	 */
	public function getTypes(): array {
		return array_keys($this->extensions);
	}
	
	/**
	 * Registers a new extension handler for a certain extension type.
	 *
	 * @param string                             $type    The type to to call this handler for
	 * @param ExtConfigExtensionHandlerInterface $handler The handler which receives the list of registered extensions
	 *                                                    and is used to create a dynamic-something based on your
	 *                                                    needs.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	public function registerExtensionHandler(string $type, ExtConfigExtensionHandlerInterface $handler): ExtConfigExtensionRegistry {
		$this->extensionHandlers[$type] = $handler;
		return $this;
	}
	
	/**
	 * Returns the instance of an extension handler or null if it was not registered
	 *
	 * @param string $type
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionHandlerInterface|null
	 */
	public function getExtensionHandler(string $type): ?ExtConfigExtensionHandlerInterface {
		return $this->extensionHandlers[$type];
	}
	
	/**
	 * Internal method which is used to trigger the registered extension handlers
	 */
	public function notifyExtensionHandlers(): void {
		// Allow filtering
		$e = new ExtConfigExtendableFeatureFilterEvent($this->extensions, $this->extensionHandlers);
		$this->eventBus->dispatch($e);
		$handlers = $e->getHandlers();
		$registeredExtensions = $e->getRegisteredExtensions();
		
		// Loop through the handlers
		foreach ($handlers as $type => $handler) {
			if (!isset($registeredExtensions[$type])) continue;
			$handler->generate($registeredExtensions[$type]);
		}
	}
}