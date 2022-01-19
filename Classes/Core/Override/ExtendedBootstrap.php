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

namespace LaborDigital\T3ba\Core\Override;

use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\BootstrapFailsafeDefinitionEvent;
use LaborDigital\T3ba\Event\BootstrapInitializesErrorHandlingEvent;
use LaborDigital\T3ba\Event\Core\BasicBootDoneEvent;
use LaborDigital\T3ba\Event\Core\PackageManagerCreatedEvent;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Core\T3BaCopyBootstrap;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtendedBootstrap extends T3BaCopyBootstrap
{
    protected static $emitDoneEvent = false;
    
    /**
     * @inheritDoc
     */
    public static function init(ClassLoader $classLoader, bool $failsafe = false): ContainerInterface
    {
        TypoEventBus::getInstance()
                    ->dispatch(new BootstrapFailsafeDefinitionEvent($failsafe));
        
        static::$emitDoneEvent = true;
        $container = parent::init($classLoader, $failsafe);
        static::$emitDoneEvent = false;
        
        return $container;
    }
    
    /**
     * @inheritDoc
     */
    public static function createPackageManager($packageManagerClassName, FrontendInterface $coreCache): PackageManager
    {
        $packageManager = parent::createPackageManager($packageManagerClassName, $coreCache);
        
        TypoEventBus::getInstance()
                    ->dispatch(new PackageManagerCreatedEvent($packageManager));
        
        return $packageManager;
    }
    
    /**
     * @inheritDoc
     */
    public static function loadBaseTca(bool $allowCaching = true, FrontendInterface $coreCache = null)
    {
        if (self::$emitDoneEvent) {
            TypoEventBus::getInstance()->dispatch(new BasicBootDoneEvent());
        }
        
        parent::loadBaseTca($allowCaching, $coreCache);
    }
    
    
    /**
     * @inheritDoc
     */
    protected static function initializeErrorHandling()
    {
        TypoEventBus::getInstance()
                    ->dispatch(new BootstrapInitializesErrorHandlingEvent());
        
        parent::initializeErrorHandling();
    }
    
    
}
