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
 * Last modified: 2020.03.19 at 02:27
 */

namespace LaborDigital\Typo3BetterApi\Event;


use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class EventConfigOption
 *
 * Can be used to bind event handlers and signal slots
 *
 * @package LaborDigital\Typo3BetterApi\Event
 */
class EventConfigOption extends AbstractExtConfigOption implements SingletonInterface {
	
	/**
	 * Binds a handler to a single, or multiple events
	 *
	 * @param array|string $events      Either an event as a string, or a list of events as array of string
	 * @param callable     $handler     A callback which is executed when the matching event is emitted
	 * @param array        $options     Additional options
	 *                                  - priority: int (0) Can be used to define the order of handlers when bound on
	 *                                  the same event. 0 is the default the "+ range" is a higher priority (earlier)
	 *                                  the "- range" is a lower priority (later)
	 *                                  - id: string (GENERATED) The identifier by which this listener should be known.
	 *                                  If not specified one will be generated. If the id was not given,
	 *                                  it will be present after the event was bound.
	 *                                  - before: string Can be used to define the id of another listener that this
	 *                                  listener should be added before. This overrides PRIORITY and AFTER. The new
	 *                                  listener is only guaranteed to come before the specified existing listener. No
	 *                                  guarantee is made regarding when it comes relative to any other listener.
	 *                                  - after: string Can be used to define the id of another listener that this
	 *                                  listener should be added after. This overrides PRIORITY. The new listener is
	 *                                  only guaranteed to come after the specified existing listener. No guarantee is
	 *                                  made regarding when it comes relative to any other listener.
	 *
	 * @return \LaborDigital\Typo3BetterApi\Event\EventConfigOption
	 * @see \Neunerlei\EventBus\EventBusInterface::addListener()
	 */
	public function registerListener($events, callable $handler, array $options = []): EventConfigOption {
		$this->context->EventBus->addListener($events, $handler, $options);
		return $this;
	}
	
	/**
	 * Adds the handlers registered in an event subscriber to the event bus
	 *
	 * @param \Neunerlei\EventBus\Subscription\EventSubscriberInterface $subscriber
	 *
	 * @return \LaborDigital\Typo3BetterApi\Event\EventConfigOption
	 * @see \Neunerlei\EventBus\EventBusInterface::registerSubscriber()
	 */
	public function registerSubscriber(EventSubscriberInterface $subscriber): EventConfigOption {
		$this->context->EventBus->addSubscriber($subscriber);
		return $this;
	}
	
	/**
	 * Adds the handlers registered in an event subscriber to the event bus
	 *
	 * @param string        $lazySubscriberClass
	 * @param callable|null $factory
	 *
	 * @return \LaborDigital\Typo3BetterApi\Event\EventConfigOption
	 * @see \Neunerlei\EventBus\EventBusInterface::addLazySubscriber()
	 */
	public function registerLazySubscriber(string $lazySubscriberClass, ?callable $factory = NULL): EventConfigOption {
		$this->context->EventBus->addLazySubscriber($lazySubscriberClass, $factory);
		return $this;
	}
}