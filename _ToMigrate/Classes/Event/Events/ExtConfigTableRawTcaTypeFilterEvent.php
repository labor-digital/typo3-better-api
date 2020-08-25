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
 * Last modified: 2020.03.19 at 11:47
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\Traits\ExtConfigTableTcaTypeFilterTrait;

/**
 * Class ExtConfigTableRawTcaTypeFilterEvent
 *
 * Dispatched when a tca table instance is converted into it's array form
 * Can be used to filter the raw type tca before it is merged with the table defaults
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ExtConfigTableRawTcaTypeFilterEvent
{
    use ExtConfigTableTcaTypeFilterTrait;
}
