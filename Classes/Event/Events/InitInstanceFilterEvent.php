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
 * Last modified: 2020.03.20 at 18:03
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;


use LaborDigital\Typo3BetterApi\BetterApiInit;
use Neunerlei\EventBus\EventBusInterface;

/**
 * Class InitInstanceFilterEvent
 *
 * Called in the BetterApiInit class. It can be used to replace
 * the class instance of the bootstrap if required
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class InitInstanceFilterEvent {
	/**
	 * @var \LaborDigital\Typo3BetterApi\BetterApiInit
	 */
	protected $init;
	
	/**
	 * @var \Neunerlei\EventBus\EventBusInterface
	 */
	protected $eventBus;
	
	/**
	 * InitInstanceFilterEvent constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\BetterApiInit $init
	 * @param \Neunerlei\EventBus\EventBusInterface      $eventBus
	 */
	public function __construct(BetterApiInit $init, EventBusInterface $eventBus) {
		$this->init = $init;
		$this->eventBus = $eventBus;
	}
	
	/**
	 * Returns the instance of the event bus
	 * @return \Neunerlei\EventBus\EventBusInterface
	 */
	public function getEventBus(): EventBusInterface {
		return $this->eventBus;
	}
	
	/**
	 * Returns the init instance
	 * @return \LaborDigital\Typo3BetterApi\BetterApiInit|object
	 */
	public function getInitInstance() {
		return $this->init;
	}
	
	/**
	 * Can be used to overwrite the init instance
	 *
	 * @param object $init
	 */
	public function setInitInstance(object $init): void {
		$this->init = $init;
	}
	
	
}