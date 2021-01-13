<?php
/*
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
 * Last modified: 2020.08.24 at 21:31
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Core;


use LaborDigital\T3BA\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\ExtConfig\AbstractExtConfigApplier;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class TypoCoreConfigApplier extends AbstractExtConfigApplier
{
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
    }

    /**
     * Performs the configuration application
     */
    public function onExtLocalConfLoaded(): void
    {
        $this->applyXClasses();
        $this->applyCacheConfig();
        $this->applyLogConfig();
    }

    /**
     * Applies the xClass configuration to the global array
     */
    protected function applyXClasses(): void
    {
        foreach ($this->state->get('typo.core.xClass', []) as $classToOverride => $classToOverrideWith) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][$classToOverride] = [
                'className' => $classToOverrideWith,
            ];
        }
    }

    /**
     * Applies the registered cache configuration to the global array
     */
    protected function applyCacheConfig(): void
    {
        foreach ($this->state->get('typo.core.cache', []) as $key => $options) {
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations'][$key] = $options;
        }
    }

    /**
     * Applies the registered log configuration to the global array
     */
    protected function applyLogConfig(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['LOG'] = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['LOG'],
            $this->state->get('typo.core.log', [])
        );
    }
}
