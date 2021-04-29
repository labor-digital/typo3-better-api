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


namespace LaborDigital\T3BA\ExtConfigHandler\Routing;


use LaborDigital\T3BA\Event\Core\SiteConfigFilterEvent;
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
        $subscription->subscribe(SiteConfigFilterEvent::class, 'onSiteConfigFilter');
    }
    
    /**
     * Injects the route enhancers into the site config array
     *
     * @param   \LaborDigital\T3BA\Event\Core\SiteConfigFilterEvent  $e
     */
    public function onSiteConfigFilter(SiteConfigFilterEvent $e): void
    {
        $routeEnhancers = $this->state->get('typo.site.*.routeEnhancers');
        $filtered = $e->getConfig();
        
        foreach ($filtered as $key => $config) {
            if (! is_string($routeEnhancers[$key])) {
                continue;
            }
            $filtered[$key]['routeEnhancers'] = Arrays::makeFromJson($routeEnhancers[$key]);
        }
        
        $e->setConfig($filtered);
    }
}
