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
 * Last modified: 2020.03.20 at 11:38
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\SqlDefinitionFilterEventAdapter;

/**
 * Class SqlDefinitionFilterEvent
 *
 * Dispatched when the typo3 install tool builds the combined SQL definition for the SQL schema update
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class SqlDefinitionFilterEvent implements CoreHookEventInterface {
	
	/**
	 * The list of sql definitions that have been gathered
	 * @var array
	 */
	protected $definitions;
	
	/**
	 * @inheritDoc
	 */
	public static function getAdapterClass(): string {
		return SqlDefinitionFilterEventAdapter::class;
	}
	
	/**
	 * SqlDefinitionFilterEvent constructor.
	 *
	 * @param array $definitions
	 */
	public function __construct(array $definitions) {
		$this->definitions = $definitions;
	}
	
	/**
	 * Returns the list of sql definitions that have been gathered
	 * @return array
	 */
	public function getDefinitions(): array {
		return $this->definitions;
	}
	
	/**
	 * Updates the list of sql definitions that have been gathered
	 *
	 * @param array $definitions
	 *
	 * @return SqlDefinitionFilterEvent
	 */
	public function setDefinitions(array $definitions): SqlDefinitionFilterEvent {
		$this->definitions = $definitions;
		return $this;
	}
	
	/**
	 * Adds a new definition to the list of sql definitions that have been gathered
	 *
	 * @param string $definition
	 *
	 * @return \LaborDigital\Typo3BetterApi\Event\Events\SqlDefinitionFilterEvent
	 */
	public function addNewDefinition(string $definition): SqlDefinitionFilterEvent {
		$this->definitions[] = $definition;
		return $this;
	}
	
}