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
 * Last modified: 2020.03.18 at 15:32
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\Traits\BackendFormNodeEventTrait;

/**
 * Class BackendFormNodePostProcessorEvent
 *
 * This event is emitted once for every backend form node after it was rendered.
 * You can use this to modify or replace the result of the renderer dynamically
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class BackendFormNodePostProcessorEvent
{
    use BackendFormNodeEventTrait;
}
