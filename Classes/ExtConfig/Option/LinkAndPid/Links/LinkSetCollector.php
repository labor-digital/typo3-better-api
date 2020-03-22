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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links;


use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\Link\LinkSetDefinition;

class LinkSetCollector {
	
	/**
	 * The list of registered set definitions
	 * @var array
	 */
	protected $definitions = [];
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
	 */
	protected $container;
	
	/**
	 * LinkSetCollector constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 */
	public function __construct(TypoContainerInterface $container) {
		$this->container = $container;
	}
	
	/**
	 * Returns a link set definition object you may use to define the link set
	 * Note: If another extension already defined the set with the given key the existing instance will be returned!
	 * This can be used to override existing link sets
	 *
	 * @param string $key
	 *
	 * @return \LaborDigital\Typo3BetterApi\Link\LinkSetDefinition
	 */
	public function getSet(string $key): LinkSetDefinition {
		if (isset($this->definitions[$key])) return $this->definitions[$key];
		return $this->definitions[$key] = $this->container->get(LinkSetDefinition::class);
	}
	
	/**
	 * Can be used to check if a set exists or not
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasSet(string $key): bool {
		return isset($this->definitions[$key]);
	}
	
	/**
	 * Can be used to remove a set completely.
	 * Becomes useful if you want to completely change an existing set of an another extension
	 *
	 * @param string $key
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Links\LinkSetCollector
	 */
	public function removeSet(string $key): LinkSetCollector {
		unset($this->definitions[$key]);
		return $this;
	}
	
	/**
	 * Internal helper to extract all the definitions that we collected
	 * @return array
	 */
	public function __getDefinitions(): array {
		return $this->definitions;
	}
	
}