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


namespace LaborDigital\Typo3BetterApi\TypoScript\DynamicTypoScript;

use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigInitEvent;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use TYPO3\CMS\Core\Cache\CacheManager;

class DynamicTypoScriptEventHandler implements LazyEventSubscriberInterface
{
    /**
     * @var DynamicTypoScriptRegistry
     */
    protected $registry;

    /**
     * DynamicTypoScriptHandler constructor.
     *
     * @param   DynamicTypoScriptRegistry  $registry
     */
    public function __construct(DynamicTypoScriptRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtConfigInitEvent::class, 'onExtConfigLoaded', ['priority' => 5000]);
        $subscription->subscribe(FileImportFilterEvent::class, 'onTypoScriptFileImport');
    }

    public function onExtConfigLoaded(): void
    {
        // No longer required, only here for backward compatibility
    }

    /**
     * Watches imported typo script files (only with the new (at)import notation) getting included
     * and handles the rewrite of the filename to our dynamic typo script file
     *
     * @param   FileImportFilterEvent  $event
     */
    public function onTypoScriptFileImport(FileImportFilterEvent $event): void
    {
        if (! $this->registry->hasMemory()) {
            TypoContainer::getInstance()->get(CacheManager::class)->flushCaches();
            throw new \RuntimeException('Dynamic-Typoscript failed to read from memory...');
        }

        if (stripos($event->getFilename(), 'dynamic:') === 0) {
            $event->setFilename(
                $this->registry->getFile(substr($event->getFilename(), 8))->getPathname()
            );
        }
    }
}
