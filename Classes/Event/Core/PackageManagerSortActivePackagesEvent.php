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
 * Last modified: 2021.11.30 at 10:13
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Core;


use TYPO3\CMS\Core\Package\PackageManager;

/**
 * Emitted when the "active packages" are requested from the package manager.
 * This allows you to sort packages on the fly without persisting them to the PackageState
 */
class PackageManagerSortActivePackagesEvent
{
    /**
     * @var array
     */
    protected $activePackages;
    
    /**
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected $packageManager;
    
    public function __construct(array $activePackages, PackageManager $packageManager)
    {
        $this->activePackages = $activePackages;
        $this->packageManager = $packageManager;
    }
    
    /**
     * Returns the list of packages to be sorted
     *
     * @return array
     */
    public function getActivePackages(): array
    {
        return $this->activePackages;
    }
    
    /**
     * Updates the list of packages to be sorted
     *
     * @param   array  $activePackages
     */
    public function setActivePackages(array $activePackages): void
    {
        $this->activePackages = $activePackages;
    }
    
    /**
     * Returns the package manager instance
     *
     * @return \TYPO3\CMS\Core\Package\PackageManager
     */
    public function getPackageManager(): PackageManager
    {
        return $this->packageManager;
    }
}