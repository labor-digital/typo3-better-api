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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\Common;


use LaborDigital\T3BA\Event\Backend\BackendAssetPostProcessorEvent;
use LaborDigital\T3BA\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use LaborDigital\T3BA\Event\Frontend\FrontendAssetPostProcessorEvent;

class AssetPostProcessorEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postProcess'][static::class]
            = static::class . '->emit';
    }
    
    public function emit(&$arguments, $pageRenderer)
    {
        $event = static::$context->env()->isBackend()
            ? new BackendAssetPostProcessorEvent($arguments, $pageRenderer)
            : new FrontendAssetPostProcessorEvent($arguments, $pageRenderer);
        static::$bus->dispatch($event);
        $arguments = $event->getAssets();
    }
}
