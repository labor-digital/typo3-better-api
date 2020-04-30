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
 * Last modified: 2020.04.29 at 22:57
 */

namespace LaborDigital\Typo3BetterApi\Module\ConfigRepository;


use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class InformationRepository
 *
 * @property ExtensionConfiguration $ExtensionConfiguration
 * @package LaborDigital\Typo3BetterApi\Information
 */
class ConfigRepository implements SingletonInterface {
	use CommonServiceLocatorTrait;
	
	public function __construct() {
		$this->addToServiceMap([
			"ExtensionConfiguration" => ExtensionConfiguration::class,
		]);
	}
	
	/**
	 * Returns information based on the Extension Configuration (defined in the ext_conf_template.txt)
	 *
	 * @param string|null       $extensionName The extension name / key to read the configuration for
	 * @param null|string|array $key           The key / path to read from the configuration. This can either be
	 *                                         a key or a path like "first.second.third" depending on the configuration
	 *                                         in your ext_conf_template.txt file.
	 * @param null              $default       If either the $extensionName or the $key could not be found
	 *                                         this value is returned instead. If this parameter is omitted NULL
	 *                                         is returned in those cases.
	 *
	 * @return mixed|null
	 * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ExtensionArchitecture/ConfigurationOptions/Index.html
	 */
	public function getExtensionConfiguration(string $extensionName, $key = NULL, $default = NULL) {
		$path = Arrays::parsePath($key);
		try {
			return $this->ExtensionConfiguration->get($extensionName, implode("/", $path));
		} catch (ExtensionConfigurationExtensionNotConfiguredException $e) {
			return $default;
		} catch (ExtensionConfigurationPathDoesNotExistException $e) {
			return $default;
		}
	}
	
	/**
	 * Returns the plugin / extension configuration for ext base extensions
	 *
	 * @param string|null $extensionName The extension name / key to read the configuration for
	 * @param string|null $pluginName    Optional plugin to look up.
	 *
	 * @return array
	 */
	public function getExtBaseConfig(?string $extensionName = NULL, ?string $pluginName = NULL) {
		return $this->TypoScript->getExtBaseSettings($extensionName, $pluginName);
	}
	
	/**
	 * Shortcut to find a TypoScript configuration value using the TypoScriptService
	 *
	 * @param null       $path    Either a key or a path like "config.lang" to query the hierarchy. If left
	 *                            empty, the method will return the complete typoScript array.
	 * @param null|mixed $default By default the method returns null, if the queried value
	 *                            was not found in the configuration. If this option is set, the given value
	 *                            will be returned instead.
	 * @param array      $options Additional options
	 *                            - pid (integer): An optional pid to query the typoScript for.
	 *                            - separator (string) ".": A separator trough which the path parts are
	 *                            separated from each other
	 *                            - getType (bool) FALSE: If set to TRUE the method will try return
	 *                            the typoScript object's type instead of it's value.
	 *                            The Type is normally stored as: key.key.type
	 *                            while the value is stored as: key.key.type. <- Note the period
	 *                            Not all elements have a type. If we don't fine one we will return the
	 *                            "default" value Otherwise we will try to get the value, and if not set return
	 *                            the type
	 *
	 * @return array|mixed|null
	 */
	public function getTypoScriptConfig($path = NULL, $default = NULL, array $options = []) {
		if (!is_null($default)) $options["default"] = $default;
		return $this->TypoScript->get($path, $options);
	}
}