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
 * Last modified: 2020.03.18 at 16:57
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class MiddlewareRegistrationEvent
 *
 * Is used to gather middlewares that will be injected into TYPO3's middleware stack
 *
 * Special priorities:
 * 500: INJECT_EXT_CONFIG_MIDDLEWARES
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class MiddlewareRegistrationEvent {
	
	/**
	 * The list of middlewares that were collected by this event
	 * @var array
	 */
	protected $middlewares = [];
	
	/**
	 * Returns the list of all currently gathered middlewares
	 * @return array
	 */
	public function getMiddlewares(): array {
		return $this->middlewares;
	}
	
	/**
	 * Updates the list of all currently gathered middlewares.
	 * The format is equivalent to the default configuration format you know from TYPO3
	 *
	 * @param array $middlewares
	 *
	 * @return MiddlewareRegistrationEvent
	 */
	public function setMiddlewares(array $middlewares): MiddlewareRegistrationEvent {
		$this->middlewares = $middlewares;
		return $this;
	}
}