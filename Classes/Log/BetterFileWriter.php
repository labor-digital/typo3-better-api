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
 * Last modified: 2020.03.19 at 01:42
 */

namespace LaborDigital\Typo3BetterApi\Log;


use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Throwable;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\Writer\FileWriter;

class BetterFileWriter extends FileWriter {
	
	/**
	 * The base name of the internal directory / log file we want to rotate
	 * @var string
	 */
	protected $baseFileName;
	
	/**
	 * True if log rotation is enabled
	 * @var bool
	 */
	protected $useRotation = FALSE;
	
	/**
	 * The number of files we should keep when performing a rotation
	 * @var int
	 */
	protected $filesToKeep = 5;
	
	/**
	 * @inheritDoc
	 */
	public function __construct(array $options = []) {
		$this->useRotation = $options["logRotation"] === TRUE;
		$this->filesToKeep = max(1, (int)$options["filesToKeep"]);
		$this->baseFileName = Inflector::toFile($options["name"]);
		parent::__construct([
			"logFile" => $this->getLogDirectory() .
				$this->baseFileName . "-" . date("Y-m-d") . ".log",
		]);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function openLogFile() {
		
		// Perform log ration if required
		$this->doLogRotation();
		
		// Do the default action
		parent::openLogFile();
	}
	
	
	/**
	 * Generates the log file directory we are working with
	 * @return string
	 */
	protected function getLogDirectory(): string {
		return Environment::getVarPath() . DIRECTORY_SEPARATOR . "log" . DIRECTORY_SEPARATOR . $this->baseFileName . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Automatically removes all log files that are older than the specified number of days
	 */
	protected function doLogRotation() {
		// Check if log rotation is enabled
		if (!$this->useRotation) return;
		if (isset(self::$logFileHandlesCount[$this->logFile])) return;
		$logDir = $this->getLogDirectory();
		if (!file_exists($logDir) || !is_dir($logDir) || !is_writable($logDir)) return;
		
		// Get a sorted list of files
		$filesSorted = [];
		foreach (Fs::getDirectoryIterator($logDir) as $fileInfo)
			$filesSorted[$fileInfo->getBasename()] = $fileInfo->getPathname();
		if (count($filesSorted) <= $this->filesToKeep) return;
		ksort($filesSorted);
		
		// Remove files if required
		$filesToRemove = array_slice($filesSorted, 0, -$this->filesToKeep);
		foreach ($filesToRemove as $path) {
			try {
				Fs::remove($path);
			} catch (Throwable $e) {
			}
		}
	}
	
}