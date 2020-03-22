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
 * Last modified: 2020.03.19 at 11:28
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;


use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry;

/**
 * Class ExtConfigClassListFilterEvent
 *
 * Dispatched after the ext config classes were registered, prepared and validated.
 * Can be used to inject late modifications before the extendable features are compiled
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Event
 */
class ExtConfigClassListFilterEvent {
	/**
	 * The list of all registered, prepared and validated ext config classes
	 * @var array
	 */
	protected $configList;
	
	/**
	 * The context instance that is passed between the ext config classes
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	protected $context;
	
	/**
	 * The registry to hold the information about extendable ext config features
	 * @var \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	protected $extensionRegistry;
	
	/**
	 * ExtConfigClassListFilterEvent constructor.
	 *
	 * @param array                                                                       $configList
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                     $context
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry $extensionRegistry
	 */
	public function __construct(array $configList, ExtConfigContext $context, ExtConfigExtensionRegistry $extensionRegistry) {
		$this->configList = $configList;
		$this->context = $context;
		$this->extensionRegistry = $extensionRegistry;
	}
	
	/**
	 * Returns the list of all registered, prepared and validated ext config classes
	 * @return array
	 */
	public function getConfigList(): array {
		return $this->configList;
	}
	
	/**
	 * Can be used to update the list of all registered, prepared and validated ext config classes
	 *
	 * @param array $configList
	 *
	 * @return ExtConfigClassListFilterEvent
	 */
	public function setConfigList(array $configList): ExtConfigClassListFilterEvent {
		$this->configList = $configList;
		return $this;
	}
	
	/**
	 * Returns the context instance that is passed between the ext config classes
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
	 */
	public function getContext(): ExtConfigContext {
		return $this->context;
	}
	
	/**
	 * Can be used to update the context instance that is passed between the ext config classes
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
	 *
	 * @return ExtConfigClassListFilterEvent
	 */
	public function setContext(ExtConfigContext $context): ExtConfigClassListFilterEvent {
		$this->context = $context;
		return $this;
	}
	
	/**
	 * Returns the registry to hold the information about extendable ext config features
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
	 */
	public function getExtensionRegistry(): ExtConfigExtensionRegistry {
		return $this->extensionRegistry;
	}
	
	/**
	 * Can be used to update the registry to hold the information about extendable ext config features
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry $extensionRegistry
	 *
	 * @return ExtConfigClassListFilterEvent
	 */
	public function setExtensionRegistry(ExtConfigExtensionRegistry $extensionRegistry): ExtConfigClassListFilterEvent {
		$this->extensionRegistry = $extensionRegistry;
		return $this;
	}
	
	
}