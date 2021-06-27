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


namespace LaborDigital\T3ba\Core\Adapter;


use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;

class PackageManagerAdapter extends PackageManager
{
    /**
     * Helper to register a package only in the current runtime
     *
     * @param   \TYPO3\CMS\Core\Package\PackageManager    $packageManager
     * @param   \TYPO3\CMS\Core\Package\PackageInterface  $package
     */
    public static function registerHookPackage(
        PackageManager $packageManager,
        PackageInterface $package
    ): void
    {
        // Register a new base path
        $packageManager->packagesBasePaths[$package->getPackageKey()] = $package->getPackagePath();
        
        // Activate the package
        $packageManager->packages[$package->getPackageKey()] = $package;
        $packageManager->registerTransientClassLoadingInformationForPackage($package);
        $activePackagesBackup = $packageManager->activePackages;
        $packageManager->registerActivePackage($package);
        $packageManager->activePackages = $activePackagesBackup;
    }
}
