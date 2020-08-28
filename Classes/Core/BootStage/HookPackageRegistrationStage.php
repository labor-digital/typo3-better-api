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
 * Last modified: 2020.08.22 at 23:28
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use LaborDigital\T3BA\Core\Adapter\PackageManagerAdapter;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Event\PackageManagerCreatedEvent;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Package\Package;

class HookPackageRegistrationStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(PackageManagerCreatedEvent::class, static function (PackageManagerCreatedEvent $event) {
            $packageManager = $event->getPackageManager();

            $packageKey = 'T3BA_hook';
            if ($packageManager->isPackageActive($packageKey)) {
                $packageManager->deactivatePackage($packageKey);
            }

            $package = new Package($packageManager, $packageKey,
                Path::join(dirname(__DIR__, 3), 'HookExtension', $packageKey) . '/');

            PackageManagerAdapter::registerHookPackage($packageManager, $package);
        });
    }

}