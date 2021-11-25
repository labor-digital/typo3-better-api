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


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript;


use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Applier extends AbstractExtConfigApplier
{
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaCompletelyLoaded');
    }
    
    public function onExtLocalConfLoaded(): void
    {
        $this->applyUserTsConfig();
        $this->applyPageTsConfig();
        $this->applyPreParseFuncs();
    }
    
    public function onTcaCompletelyLoaded(): void
    {
        $this->applyStaticDirectoryRegistration();
        $this->applyTcaPageTsConfig();
    }
    
    protected function applyUserTsConfig(): void
    {
        ExtensionManagementUtility::addUserTSConfig($this->state->get('typo.typoScript.userTsConfig', ''));
    }
    
    protected function applyPageTsConfig(): void
    {
        ExtensionManagementUtility::addPageTSConfig($this->state->get('typo.typoScript.pageTsConfig', ''));
    }
    
    protected function applyTcaPageTsConfig(): void
    {
        foreach ($this->state->get('typo.typoScript.selectablePageTsFiles', []) as $file) {
            ExtensionManagementUtility::registerPageTSConfigFile(...$file);
        }
    }
    
    protected function applyStaticDirectoryRegistration(): void
    {
        foreach ($this->state->get('typo.typoScript.staticDirectories', []) as $directory) {
            if (empty($directory[2])) {
                $directory[2] = Inflector::toHuman($directory[0], true) . ' - Static TypoScript';
            }
            ExtensionManagementUtility::addStaticFile(...$directory);
        }
    }
    
    protected function applyPreParseFuncs(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tsparser.php']['preParseFunc']
            = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tsparser.php']['preParseFunc'] ?? [],
            $this->state->get('typo.typoScript.preParseFunctions', [])
        );
    }
}
