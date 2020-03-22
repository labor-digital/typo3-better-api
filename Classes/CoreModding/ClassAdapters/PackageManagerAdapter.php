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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters;


use TYPO3\CMS\Core\Package\Package;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;

class PackageManagerAdapter extends PackageManager {
	
	/**
	 * This extension digs to deep into the core to be disabled...
	 * If it's installed you will have to use it, I fear...
	 *
	 * @param \TYPO3\CMS\Core\Package\PackageManager $manager
	 */
	public static function forcedSelfActivation(PackageManager $manager) {
		
		// Inject ourselves into the package manager
		$packageKey = "typo3_better_api";
		unset($manager->packages[$packageKey]);
		unset($manager->activePackages[$packageKey]);
		unset($manager->runtimeActivatedPackages[$packageKey]);
		unset($manager->packageStatesConfiguration['packages'][$packageKey]);
		$manager->activatePackageDuringRuntime($packageKey);
		$package = $manager->getPackage($packageKey);
		
		// Install myself as priority extension...
		$packageAdapter = new class extends Package {
			/** @noinspection PhpMissingParentConstructorInspection */
			public function __construct(?PackageManager $packageManager = NULL, $packageKey = NULL, $packagePath = NULL) { }
			
			/**
			 * @param Package|PackageInterface $package
			 */
			public function setAsPriority($package) {
				$package->partOfFactoryDefault = TRUE;
				$package->partOfMinimalUsableSystem = TRUE;
				$package->protected = TRUE;
			}
		};
		$packageAdapter->setAsPriority($package);
	}
}