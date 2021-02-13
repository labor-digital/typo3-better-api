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
 * Last modified: 2020.08.25 at 11:14
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\EventHandler;


use LaborDigital\T3BA\Event\TypoScript\FileImportFilterEvent;
use LaborDigital\T3BA\Tool\TypoScript\DynamicTypoScriptRegistry;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;

class DynamicTypoScriptEventHandler implements LazyEventSubscriberInterface
{
    /**
     * @var \LaborDigital\T3BA\Tool\TypoScript\DynamicTypoScriptRegistry
     */
    protected $registry;

    /**
     * DynamicTypoScriptHandler constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\TypoScript\DynamicTypoScriptRegistry  $registry
     */
    public function __construct(DynamicTypoScriptRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(FileImportFilterEvent::class, 'onTypoScriptFileImport');
    }

    /**
     * Watches imported typo script files (only with the new @import notation) getting included
     * and handles the rewrite of the filename to our dynamic typo script file
     *
     * @param   \LaborDigital\T3BA\Event\TypoScript\FileImportFilterEvent  $event
     */
    public function onTypoScriptFileImport(FileImportFilterEvent $event): void
    {
        if (stripos($event->getFilename(), 'dynamic:') === 0) {
            $event->setFilename(
                $this->registry->getFile(substr($event->getFilename(), 8))->getPathname()
            );
        }
    }
}
