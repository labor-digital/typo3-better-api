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
 * Last modified: 2020.08.25 at 10:22
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\TypoScript;


use LaborDigital\T3BA\Event\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\Event\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\ExtConfig\AbstractExtConfigApplier;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class TypoScriptConfigApplier extends AbstractExtConfigApplier
{

    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaCompletelyLoaded');
    }

    public function onExtLocalConfLoaded(): void
    {
        $this->applyUserTsConfig();
    }

    public function onTcaCompletelyLoaded(): void
    {
        $this->applyStaticDirectoryRegistration();
        $this->applyTcaPageTsConfig();
    }

    /**
     * Registers the user ts configuration
     */
    protected function applyUserTsConfig(): void
    {
        ExtensionManagementUtility::addUserTSConfig($this->state->get('typo.typoScript.userTsConfig', ''));
    }

    /**
     * Registers the static page ts configuration
     */
    protected function applyStaticPageTsConfig(): void
    {
        ExtensionManagementUtility::addPageTSConfig($this->state->get('typo.typoScript.userTsConfig', ''));
    }

    /**
     * Registers the registered, selectable page ts files
     */
    protected function applyTcaPageTsConfig(): void
    {
        foreach ($this->state->get('typo.typoScript.selectablePageTsFiles', []) as $file) {
            ExtensionManagementUtility::registerPageTSConfigFile(...$file);
        }
    }

    /**
     * Registers static typo script directories
     */
    protected function applyStaticDirectoryRegistration(): void
    {
        foreach ($this->state->get('typo.typoScript.staticDirectories') as $directory) {
            if (empty($directory[2])) {
                $directory[2] = Inflector::toHuman($directory[0], true) . ' - Static TypoScript';
            }
            ExtensionManagementUtility::addStaticFile(...$directory);
        }
    }
}