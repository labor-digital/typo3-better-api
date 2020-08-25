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
 * Last modified: 2020.03.18 at 13:47
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event;

/**
 * Class TcaCompletelyLoadedEvent
 *
 * Dispatched when the TCA is being generated, after the base tca and the overrides have been loaded and before the TCA
 * is stored in the cache
 *
 * Special priorities:
 * 500: EXT_CONFIG_BEFORE_TCA_OVERRIDE
 * 400: EXT_CONFIG_TCA_OVERRIDE
 * 300: TCA_OVERRIDE_FILTER
 * 200: EXT_CONFIG_DYNAMIC_TYPO_SCRIPT
 * 100: PID_TCA_FILTER
 *
 * @package LaborDigital\T3BA\Core\Event
 */
class TcaCompletelyLoadedEvent
{
}
