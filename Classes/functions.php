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
 * Last modified: 2020.03.20 at 16:35
 */


use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbService;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigService;


if (!function_exists("betterExtConfig")) {
	/**
	 * This method is used in your extension's ext_localconf.php and marks the entry point into the ext config logic.
	 * Ext config provides you with a class based, auto-complete friendly approach to configure your extension and
	 * the typo3 installation itself.
	 *
	 * @param string $extKeyWithVendor     This should be something like $_EXTKEY or "vendor." . $_EXTKEY if you want a
	 *                                     vendor name to be used for your extension.
	 * @param string $configurationClass   The class that is used to configure the extension.
	 *                                     The given class has to implement the ExtConfigInterface to configure the extension
	 *                                     and/or the installation.
	 *                                     If the class implements the ExtConfigExtensionInterface you can extend the ext
	 *                                     config framework with additional options.
	 * @param array  $options              Additional options to pass through
	 *                                     - before string|array: Can be used to register your configuration class before
	 *                                     another ext config class. The given class/classes don't have to exist
	 *                                     (inter extension compatibility)
	 *                                     - after string|array: Similar to "before", registers your configuration class
	 *                                     after another config class.
	 *
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigInterface
	 * @see \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionInterface
	 */
	function betterExtConfig(string $extKeyWithVendor, string $configurationClass, array $options = []) {
		// Validate if typo3 context is set
		if (!defined("TYPO3_MODE")) throw new BadMethodCallException("You are not running in Typo3 context");
		
		// Register the config in the loader
		ExtConfigService::__registerExtension($extKeyWithVendor, $configurationClass, $options);
	}
}

if (!function_exists("dbgQuery")) {
	function dbgQuery($query) {
		TypoContainer::getInstance()->get(DbService::class)->debugQuery($query);
	}
}