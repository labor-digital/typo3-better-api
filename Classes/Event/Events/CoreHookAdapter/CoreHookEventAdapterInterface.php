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
 * Last modified: 2020.03.18 at 14:25
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;


use LaborDigital\Typo3BetterApi\Container\TypoContainerInterface;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\EventBus\EventBusInterface;

interface CoreHookEventAdapterInterface {
	/**
	 * Provides some dependencies that are likely to be required in the event adapter implementation
	 *
	 * @param \Neunerlei\EventBus\EventBusInterface                         $bus
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext          $context
	 * @param \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface $container
	 *
	 * @return void
	 */
	public static function prepare(EventBusInterface $bus, TypoContext $context, TypoContainerInterface $container);
	
	/**
	 * This method is called as soon as the first handler is registered for the matched event
	 *
	 * @return void
	 */
	public static function bind(): void;
}