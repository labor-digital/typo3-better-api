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
use LaborDigital\T3ba\Event\Core\PackageManagerSortActivePackagesEvent;

/**
 * @todo rename this stage in v11 as we no longer have a hook package
 */
class HookPackageRegistrationStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(PackageManagerSortActivePackagesEvent::class, [$this, 'onPackageSorting']);
    }
    
    /**
     * Ensures that the t3ba extension is always the last package in the list
     *
     * @param   \LaborDigital\T3ba\Event\Core\PackageManagerSortActivePackagesEvent  $event
     */
    public function onPackageSorting(PackageManagerSortActivePackagesEvent $event): void
    {
        $packages = $event->getActivePackages();
        $packageKey = 't3ba';
        $self = $packages[$packageKey] ?? null;
        unset($packages[$packageKey]);
        
        if ($self === null) {
            return;
        }
        
        $packages[$packageKey] = $self;
        $event->setActivePackages($packages);
    }
}
