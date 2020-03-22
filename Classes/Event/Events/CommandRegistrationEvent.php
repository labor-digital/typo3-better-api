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
 * Last modified: 2020.03.18 at 16:53
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class ExtConfigCommandEvent
 *
 * Is used to inject the registered commands into TYPO3's symfony command handler
 *
 * @package LaborDigital\Typo3BetterApi\ExtConfig\Event
 */
class CommandRegistrationEvent {
	/**
	 * The list of commands that were gathered using this event
	 * @var array
	 */
	protected $commands = [];
	
	/**
	 * Adds a new command to the list of registered commands
	 *
	 * @param string $commandClass
	 * @param string $commandName
	 * @param bool   $isSchedulable
	 */
	public function addCommand(string $commandClass, string $commandName, bool $isSchedulable): void {
		$this->commands[$commandName] = [
			"class"       => $commandClass,
			"schedulable" => $isSchedulable,
		];
	}
	
	/**
	 * Returns the list of commands that were gathered using this event
	 * @return array
	 */
	public function getCommands(): array {
		return $this->commands;
	}
	
	/**
	 * Updates the list of commands that were gathered using this event
	 *
	 * @param array $commands
	 *
	 * @return CommandRegistrationEvent
	 */
	public function setCommands(array $commands): CommandRegistrationEvent {
		$this->commands = $commands;
		return $this;
	}
}