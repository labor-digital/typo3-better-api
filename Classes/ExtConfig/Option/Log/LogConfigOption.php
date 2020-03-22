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
 * Last modified: 2020.03.19 at 11:54
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Log;

use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use LaborDigital\Typo3BetterApi\Log\BetterFileWriter;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Log\LogLevel;

class LogConfigOption extends AbstractExtConfigOption {
	
	/**
	 * Registers a new logfile writer in the system. It utilizes our internal
	 * better file writer that has built-in log rotation capabilities.
	 *
	 * @param string $name    A speaking name for your log -> used only for the file name generation
	 * @param array  $options Additional log configuration options
	 *                        - logLevel int (7|3): This is equivalent with one of the LogLevel constants.
	 *                        It defines the minimal viable severity that should be logged, all levels with a higher
	 *                        number that the given level will be be ignored
	 *                        - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
	 *                        This can be either a class name or a part of a php namespace. If an empty
	 *                        string is given the configuration is applied globally
	 *                        - writer array: the writer configuration array for the configured loglevel
	 *                        - processor array: the processor configuration array for the configured loglevel
	 *                        - logRotation bool (TRUE): By default the log files will be rotated once a day.
	 *                        If you want to disable the log rotation set this option to false.
	 *                        - filesToKeep int (5): If logRotation is enabled, this defines how many
	 *                        files will be kept before they are deleted
	 *
	 * @see \TYPO3\CMS\Core\Log\LogLevel
	 * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
	 */
	public function registerFileLog(string $name, array $options = []) {
		$options = $this->prepareLogOptions($options, [
			"logRotation" => [
				"type"    => "bool",
				"default" => TRUE,
			],
			"filesToKeep" => [
				"type"    => "int",
				"default" => 5,
			],
		]);
		$options["writer"] = [
			BetterFileWriter::class => [
				"logRotation" => $options["logRotation"],
				"filesToKeep" => $options["filesToKeep"],
				"name"        => $name,
			],
		];
		$this->applyLogConfiguration($options);
	}
	
	/**
	 * Registers any kind of log configuration based on your input.
	 *
	 * @param array $options  The options for your log configuration
	 *                        - logLevel int (7|3): This is equivalent with one of the LogLevel constants.
	 *                        It defines the minimal viable severity that should be logged, all levels with a higher
	 *                        number that the given level will be be ignored
	 *                        - namespace string (Vendor/ExtKey): The PHP namespace for the logger to be active in.
	 *                        This can be either a class name or a part of a php namespace. If an empty
	 *                        string is given the configuration is applied globally
	 *                        - writer array: the writer configuration array for the configured loglevel
	 *                        - processor array: the processor configuration array for the configured loglevel
	 *
	 * @see \TYPO3\CMS\Core\Log\LogLevel
	 * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/Logging/Configuration/Index.html
	 */
	public function registerLogConfig(array $options) {
		$options = $this->prepareLogOptions($options);
		$this->applyLogConfiguration($options);
	}
	
	/**
	 * Internal helper to build the options array based on the given input.
	 * Allows to add additional config definitions to be used for different log type
	 *
	 * @param array $options
	 * @param array $additionalDefinition
	 *
	 * @return array
	 */
	protected function prepareLogOptions(array $options, array $additionalDefinition = []): array {
		return Options::make($options, Arrays::merge([
			"logLevel"  => [
				"type"    => "string",
				"default" => $this->context->TypoContext->getEnvAspect()->isDev() ? LogLevel::DEBUG : LogLevel::ERROR,
				"values"  => [
					LogLevel::EMERGENCY, LogLevel::ERROR, LogLevel::CRITICAL, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG, LogLevel::ALERT,
				],
			],
			"namespace" => [
				"type"    => "string",
				"default" => function () {
					return implode("\\",
						array_filter([Inflector::toCamelCase($this->context->getVendor()),
									  Inflector::toCamelCase($this->context->getExtKey()),
						]));
				},
				"filter"  => function ($v) {
					return array_filter(explode("\\", $v));
				},
			],
			"writer"    => [
				"type"    => "array",
				"default" => [],
			],
			"processor" => [
				"type"    => "array",
				"default" => [],
			],
		], $additionalDefinition));
	}
	
	/**
	 * Injects the options into the globals super array
	 *
	 * @param array $options
	 */
	protected function applyLogConfiguration(array $options) {
		$config = [
			"writerConfiguration"    => [
				$options["logLevel"] => $options["writer"],
			],
			"processorConfiguration" => [
				$options["logLevel"] => $options["processor"],
			],
		];
		$GLOBALS["TYPO3_CONF_VARS"] = Arrays::setPath($GLOBALS,
			Arrays::mergePaths(["TYPO3_CONF_VARS", "LOG"], $options["namespace"]), $config)["TYPO3_CONF_VARS"];
	}
}