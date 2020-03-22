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
 * Last modified: 2020.03.20 at 16:41
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class SignalSlotEvent
 *
 * A generic event that can be dispatched to dispatch an event in the signal slot dispatcher using
 * the event bus implementation.
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class SignalSlotEvent {
	
	/**
	 * The name of the class to emit the signal for
	 * @var string
	 */
	protected $className;
	
	/**
	 * The signal to emit for the given class
	 * @var string
	 */
	protected $signalName;
	
	/**
	 * The arguments to pass to the callbacks
	 * @var array
	 */
	protected $args;
	
	public function __construct(string $className, string $signalName, array $args = []) {
		$this->className = $className;
		$this->signalName = $signalName;
		$this->args = $args;
	}
	
	/**
	 * Returns the name of the class for the signal
	 * @return string
	 */
	public function getClassName(): string {
		return $this->className;
	}
	
	/**
	 * Returns the name of the signal
	 * @return string
	 */
	public function getSignalName(): string {
		return $this->signalName;
	}
	
	
	/**
	 * Returns the list of arguments that should be passed to the callbacks
	 * @return array
	 */
	public function getArgs(): array {
		return $this->args;
	}
	
	/**
	 * Updates the arguments transferred by the event object
	 *
	 * @param array $args
	 *
	 * @return SignalSlotEvent
	 */
	public function setArgs(array $args): SignalSlotEvent {
		$this->args = $args;
		return $this;
	}
	
	
}