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

namespace LaborDigital\T3BA\Event\Frontend;


use LaborDigital\T3BA\Event\Common\AssetEventTrait;
use LaborDigital\T3BA\Event\Common\AssetFilterEventAdapter;
use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;

/**
 * Class FrontendAssetFilterEvent
 *
 * Is triggered when the assets (css/js) of typo3 are compiled in the FRONTEND
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class FrontendAssetFilterEvent implements CoreHookEventInterface
{
    use AssetEventTrait;

    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return AssetFilterEventAdapter::class;
    }
}
