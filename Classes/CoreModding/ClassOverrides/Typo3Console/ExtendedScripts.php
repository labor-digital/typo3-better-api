<?php
/**
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
 * Last modified: 2020.03.20 at 16:47
 */

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\Typo3Console;

use Helhum\Typo3Console\Core\Booting\BetterApiClassOverrideCopy__Scripts;
use LaborDigital\Typo3BetterApi\Event\Events\RegisterRuntimePackagesEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\CacheManagerCreatedEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtendedScripts
 *
 * This class is an adapter for helhum's Typo3 Console package.
 * It creates a lot of the framework manually and does not rely on the bootstrap class we normally
 * extend for our means. So we use this additional override to provide support for our required events
 * @package LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides\Typo3Console
 */
class ExtendedScripts extends BetterApiClassOverrideCopy__Scripts
{
    
    /**
     * @inheritDoc
     */
    protected static function initializeCachingFramework(Bootstrap $bootstrap, bool $disableCaching = false)
    {
        parent::initializeCachingFramework($bootstrap, $disableCaching);
        
        // Trigger our event as we would in the bootstrap
        TypoEventBus::getInstance()->dispatch(new CacheManagerCreatedEvent(
            GeneralUtility::makeInstance(CacheManager::class),
            false
        ));
    }
    
    /**
     * @inheritDoc
     */
    protected static function initializePackageManagement(Bootstrap $bootstrap)
    {
        parent::initializePackageManagement($bootstrap);
        
        // Trigger our event as we would in the bootstrap
        TypoEventBus::getInstance()->dispatch(new RegisterRuntimePackagesEvent(
            GeneralUtility::makeInstance(PackageManager::class)
        ));
    }
}
