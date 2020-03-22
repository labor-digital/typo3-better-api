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
 * Last modified: 2020.03.18 at 16:36
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\EventBus\Subscription\EventSubscriberInterface;

interface ExtConfigOptionInterface extends EventSubscriberInterface {
	
	/**
	 * Is called after the option was created and is used to inject the current context into it
	 *
	 * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
	 *
	 * @return void
	 */
	public function setContext(ExtConfigContext $context);
	
}