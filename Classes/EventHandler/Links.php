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
 * Last modified: 2020.10.06 at 12:57
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\EventHandler;

use LaborDigital\T3BA\Event\FormEngine\LinkBrowserAllowedTabsFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class Links implements LazyEventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(LinkBrowserAllowedTabsFilterEvent::class, 'onAllowedItemsFilter');
    }
    
    /**
     * Removes all not required link set handlers from the gui.
     *
     * We "utilize"/"rape" the "blindLinkOptions" parameter here, so we don't need to put our own
     * javascript everywhere.
     *
     * @param   \LaborDigital\T3BA\Event\FormEngine\LinkBrowserAllowedTabsFilterEvent  $event
     */
    public function onAllowedItemsFilter(LinkBrowserAllowedTabsFilterEvent $event): void
    {
        $allowedTabs = $event->getAllowedTabs();
        $config = $event->getFieldConfig();
        
        // All link sets are allowed in rte fields
        if (isset($config['richtextConfigurationName'])) {
            return;
        }
        
        // Extract the "@linkSets:" key from the blindLinkOptions parameter
        $blindOptions = Arrays::makeFromStringList($config['params']['blindLinkOptions'] ?? '');
        $linkSetOptions = null;
        foreach ($blindOptions as $option) {
            if (strpos($option, '@linkSets:') === 0) {
                $linkSetOptions = $option;
                break;
            }
        }
        
        if ($linkSetOptions === null) {
            $linkSetOptions = false;
        } else {
            $linkSetOptions = \GuzzleHttp\json_decode(substr($linkSetOptions, 10));
        }
        
        // Filter out the link sets, either none (TRUE), all (FALSE) or just keep some (ARRAY)
        $event->setAllowedTabs(
            array_filter($allowedTabs, static function (string $v) use ($linkSetOptions): bool {
                if ($linkSetOptions === true) {
                    return true;
                }
                
                if (strpos($v, 'linkSet_') === 0) {
                    if ($linkSetOptions === false) {
                        return false;
                    }
                    
                    return is_array($linkSetOptions) && in_array($v, $linkSetOptions, true);
                }
                
                return true;
            })
        );
    }
}
