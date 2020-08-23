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
 * Last modified: 2020.08.23 at 12:36
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use LaborDigital\T3BA\Core\Event\ExtLocalConfLoadedEvent;
use LaborDigital\T3BA\Core\Event\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class EnsureExtLocalConfOnTcaLoadStage implements BootStageInterface
{
    /**
     * True if the ext local conf files were loaded
     *
     * @var bool
     */
    protected $extLocalConfWasLoaded = false;

    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        // Load the ext config files when the TCA is loaded
        $eventBus->addListener(TcaWithoutOverridesLoadedEvent::class, function () {
            if ($this->extLocalConfWasLoaded) {
                return;
            }

            // Force load ext config files
            ExtensionManagementUtility::loadExtLocalconf(false);
        }, ['priority' => 1000]);


        // Listen if ext config was loaded
        $eventBus->addListener(ExtLocalConfLoadedEvent::class, function () {
            $this->extLocalConfWasLoaded = true;
        });
    }

}
