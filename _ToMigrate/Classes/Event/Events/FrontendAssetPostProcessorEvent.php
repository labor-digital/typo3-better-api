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
 * Last modified: 2020.03.20 at 16:44
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\AssetPostProcessorEventAdapter;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\Traits\AssetEventTrait;

/**
 * Class FrontendAssetPostProcessorEvent
 *
 * Is triggered, after typo3 compiled the assets for the FRONTEND, before the page renderer builds the html
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class FrontendAssetPostProcessorEvent implements CoreHookEventInterface
{
    use AssetEventTrait;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return AssetPostProcessorEventAdapter::class;
    }
}