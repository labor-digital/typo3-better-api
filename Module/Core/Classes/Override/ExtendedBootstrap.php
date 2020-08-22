<?php
/*
 * Copyright 2020 Martin Neundorfer (Neunerlei)
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
 * Last modified: 2020.08.09 at 14:49
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

namespace LaborDigital\T3BA\Core\Override;

use Composer\Autoload\ClassLoader;
use LaborDigital\Typo3BetterApi\Event\Events\BootstrapFailsafeDefinitionEvent;
use LaborDigital\Typo3BetterApi\Event\Events\RegisterRuntimePackagesEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\BootstrapContainerFilterEvent;
use LaborDigital\Typo3BetterApi\Event\Events\Temporary\CacheManagerCreatedEvent;
use LaborDigital\Typo3BetterApi\Event\TypoEventBus;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Core\BetterApiClassOverrideCopy__Bootstrap;
use TYPO3\CMS\Core\Package\PackageManager;

class ExtendedBootstrap extends BetterApiClassOverrideCopy__Bootstrap
{
    /**
     * @inheritDoc
     */
    public static function init(ClassLoader $classLoader, bool $failsafe = false): ContainerInterface
    {
        TypoEventBus::getInstance()->dispatch(new BootstrapFailsafeDefinitionEvent($failsafe));
        $container = parent::init($classLoader, $failsafe);
        $e         = new BootstrapContainerFilterEvent($container, $failsafe);
        TypoEventBus::getInstance()->dispatch($e);

        return $e->getContainer();
    }

    /**
     * @inheritDoc
     */
    protected static function initializeRuntimeActivatedPackagesFromConfiguration(PackageManager $packageManager): void
    {
        parent::initializeRuntimeActivatedPackagesFromConfiguration($packageManager);
        TypoEventBus::getInstance()->dispatch(new RegisterRuntimePackagesEvent($packageManager));
    }
}
