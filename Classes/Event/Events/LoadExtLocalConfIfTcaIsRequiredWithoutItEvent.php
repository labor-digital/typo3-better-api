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
 * Last modified: 2020.03.20 at 16:43
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

/**
 * Class LoadExtLocalConfIfTcaIsRequiredWithoutItEvent
 *
 * Emitted when the TCA is loaded, before the event bus emit's the tca events.
 * This is here to make sure the typo better api is initialized completely at this point.
 * This is required to fix a bug when the backend triggers TCA validation in the install tool
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class LoadExtLocalConfIfTcaIsRequiredWithoutItEvent
{
}
