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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\EventHandler;


use LaborDigital\T3ba\Event\Backend\LowLevelControllerConfigFilterEvent;
use LaborDigital\T3ba\Event\Core\ExtConfigLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Loader\MainLoader;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class ExtConfig implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3ba\ExtConfig\Loader\MainLoader
     */
    protected $loader;
    
    /**
     * ExtConfigEventHandler constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfig\Loader\MainLoader  $loader
     */
    public function __construct(MainLoader $loader)
    {
        $this->loader = $loader;
    }
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtConfigLoadedEvent::class, 'onExtConfigLoaded', ['priority' => 100]);
        $subscription->subscribe(LowLevelControllerConfigFilterEvent::class, 'onLowLevelFilter');
    }
    
    /**
     * Executes the ext config loader
     */
    public function onExtConfigLoaded(): void
    {
        $this->loader->load();
    }
    
    /**
     * Used when the lowLevel extension is installed to hook the ext config into the list of display types
     *
     * @param   \LaborDigital\T3ba\Event\Backend\LowLevelControllerConfigFilterEvent  $event
     */
    public function onLowLevelFilter(LowLevelControllerConfigFilterEvent $event): void
    {
        $config = TypoContext::getInstance()->config()->getConfigState();
        
        $event->addData(
            't3ba.extConfig',
            $this->unpackJsonValues($config->getAll()),
            't3ba.lowLevel.configLabel'
        );
    }
    
    /**
     * Internal helper to unpack all JSON entries in the given list
     *
     * @param   array  $list
     *
     * @return array
     */
    protected function unpackJsonValues(array $list): array
    {
        foreach ($list as $k => $v) {
            if (is_array($v)) {
                $list[$k] = $this->unpackJsonValues($v);
                continue;
            }
            
            if (! is_string($v)) {
                continue;
            }
            
            if (str_starts_with($v, '[') || str_starts_with($v, '{')) {
                try {
                    $list[$k] = array_merge(
                        ['@info' => 'This value is stored as JSON'],
                        json_decode($v, true, 512, JSON_THROW_ON_ERROR)
                    );
                } catch (\JsonException $e) {
                }
            }
        }
        
        return $list;
    }
}
