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


namespace LaborDigital\T3ba\Core\BootStage;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\Kernel;
use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaWithoutOverridesLoadedEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class EnsureExtLocalConfOnTcaLoadStage implements BootStageInterface
{
    /**
     * Internal toggle to disable this stage temporarily in the Main loader.
     * This flag may be removed without any notice
     *
     * @internal
     * @var bool
     */
    public static $enabled = true;
    
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
            if (! static::$enabled || $this->extLocalConfWasLoaded) {
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
