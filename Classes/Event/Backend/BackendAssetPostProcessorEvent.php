<?php
/*
 * Copyright 2021 LABOR.digital
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\Backend;


use LaborDigital\T3BA\Event\Common\AssetEventTrait;
use LaborDigital\T3BA\Event\Common\AssetPostProcessorEventAdapter;
use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;

/**
 * Class BackendAssetPostProcessorEvent
 *
 * Is triggered, after typo3 compiled the assets for the BACKEND, before the page renderer builds the html
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class BackendAssetPostProcessorEvent implements CoreHookEventInterface
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
