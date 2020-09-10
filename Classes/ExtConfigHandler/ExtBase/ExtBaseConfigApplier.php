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
 * Last modified: 2020.09.09 at 18:46
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase;


use LaborDigital\T3BA\Event\ExtTablesLoadedEvent;
use LaborDigital\T3BA\Event\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\ExtConfig\AbstractExtConfigApplier;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ExtBaseConfigApplier extends AbstractExtConfigApplier
{

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaCompletelyLoaded');
        $subscription->subscribe(ExtTablesLoadedEvent::class, 'onExtTablesLoaded');
    }

    public function onExtTablesLoaded(): void
    {
        $this->registerModules();
    }

    public function onTcaCompletelyLoaded(): void
    {
    }

    /**
     * Registers the configured ext base modules in the backend
     */
    protected function registerModules(): void
    {
        $argDefinition = $this->state->get('typo.extBase.module.args', '');
        if (empty($argDefinition)) {
            return;
        }

        foreach (Arrays::makeFromJson($argDefinition) as $args) {
            ExtensionUtility::registerModule(...$args);
        }
    }
}
