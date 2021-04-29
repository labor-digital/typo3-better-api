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
 * Last modified: 2021.02.19 at 14:20
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Backend;


use LaborDigital\T3BA\Event\Backend\BackendAssetFilterEvent;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigApplier;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class Applier extends AbstractExtConfigApplier
{
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(BackendAssetFilterEvent::class, 'onBackendAssets');
    }
    
    public function onBackendAssets(BackendAssetFilterEvent $event): void
    {
        $list = $this->state->get('typo.backend.assets');
        if (! empty($list)) {
            $renderer = $event->getPageRenderer();
            foreach (Arrays::makeFromJson($list) as $asset) {
                call_user_func_array([$renderer, $asset[0]], $asset[1]);
            }
        }
    }
    
}
