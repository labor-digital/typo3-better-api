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
 * Last modified: 2020.03.20 at 16:41
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;


use TYPO3\CMS\Core\Package\PackageManager;

/**
 * Class RegisterRuntimePackagesEvent
 *
 * This event is called at boot-time, right after the runtime packages were registered
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class RegisterRuntimePackagesEvent {
	
	/**
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 */
	protected $packageManager;
	
	/**
	 * RegisterRuntimePackagesEvent constructor.
	 *
	 * @param \TYPO3\CMS\Core\Package\PackageManager $packageManager
	 */
	public function __construct(PackageManager $packageManager) {
		$this->packageManager = $packageManager;
	}
	
	/**
	 * Returns the package manager instance
	 * @return \TYPO3\CMS\Core\Package\PackageManager
	 */
	public function getPackageManager(): PackageManager {
		return $this->packageManager;
	}
}