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
 * Last modified: 2020.03.20 at 12:33
 */

namespace LaborDigital\Typo3BetterApi\FileAndFolder\TempFs;

use LaborDigital\Typo3BetterApi\Event\Events\CacheClearedEvent;
use LaborDigital\Typo3BetterApi\FileAndFolder\Permissions;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use Neunerlei\FileSystem\Fs;
use Neunerlei\PathUtil\Path;
use SplFileInfo;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class TempFs
 *
 * In earlier versions I used the caching framework extensively when it came
 * to storing dynamically generated content. However it is no longer allowed
 * to create caches while the ext_localconf and tca files are generated.
 *
 * Therefore all data that is dynamically generated is now stored in a separate
 * temporary directory tree, which is abstracted by this class.
 *
 * @package LaborDigital\Typo3BetterApi\FileAndFolder
 */
class TempFs implements LazyEventSubscriberInterface {
	
	/**
	 * Marker that is prepended in front of serialized file contents
	 */
	protected const SERIALIZED_MARKER = "__SERIALIZED__:";
	
	/**
	 * The baseDirectory as a path relative to the better api root directory.
	 * This is required for typo script and flex form registration.
	 * @var string|null
	 */
	protected $relativeBaseDirectory;
	
	/**
	 * The base directory where the file system will work in
	 * @var string
	 */
	protected $baseDirectory;
	
	/**
	 * TempFs constructor.
	 *
	 * @param string $path An additional root path inside the typo3 temp directory
	 *
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFsException
	 */
	public function __construct(string $path) {
		$this->baseDirectory = Path::unifyPath(BETTER_API_TYPO3_VAR_PATH, "/") . "tempFs/";
		$this->baseDirectory = $this->resolvePath("/" . ltrim(Path::unifyPath($path, "/"), "/"));
		
		// Validate the base directory
		if (is_file($this->baseDirectory))
			throw new TempFsException("The given base directory \"$this->baseDirectory\" resolves to a file!");
		if (!is_writable($this->baseDirectory)) {
			Fs::mkdir($this->baseDirectory);
			Permissions::setFilePermissions($this->baseDirectory);
		}
		if (!is_writable($this->baseDirectory) && !is_writable(dirname($this->baseDirectory)))
			throw new TempFsException("The temporary directory \"$this->baseDirectory\" is not writable by the web-server!");
	}
	
	/**
	 * @inheritDoc
	 */
	public static function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(CacheClearedEvent::class, "__onCacheClear");
	}
	
	/**
	 * Returns true if a file exists, false if not
	 *
	 * @param string $filePath
	 *
	 * @return bool
	 */
	public function hasFile(string $filePath): bool {
		$filePathReal = $this->resolvePath($filePath);
		return $this->hasFileInternal($filePathReal);
	}
	
	/**
	 * Returns the file object for the required file path
	 *
	 * @param string $filePath
	 *
	 * @return \SplFileInfo
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFsException
	 */
	public function getFile(string $filePath): SplFileInfo {
		$filePathReal = $this->resolvePath($filePath);
		if (!$this->hasFileInternal($filePathReal))
			throw new TempFsException("Could not get the file: \"$filePath\" because it does not exist!");
		return new SplFileInfo($filePathReal);
	}
	
	/**
	 * Returns the content of a required file.
	 * It will automatically unpack serialized values back into their PHP values
	 *
	 * @param string $filePath
	 *
	 * @return mixed
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFsException
	 */
	public function getFileContent(string $filePath) {
		
		// Try to load the content
		$filePathReal = $this->resolvePath($filePath);
		if (!$this->hasFileInternal($filePathReal))
			throw new TempFsException("Could not get the contents of file: \"$filePath\" because it does not exist!");
		$content = file_get_contents($filePathReal);
		
		// Deserialize serialized content
		if (substr($content, 0, strlen(static::SERIALIZED_MARKER)) === static::SERIALIZED_MARKER)
			$content = unserialize(substr($content, strlen(static::SERIALIZED_MARKER)));
		
		// Done
		return $content;
	}
	
	/**
	 * Is used to dump some content into a file
	 *
	 * @param string       $filePath The name of the file to dump the content to
	 * @param string|mixed $content  Either a string (will be dumped as string) or anything else (will be dumped as
	 *                               serialized value)
	 */
	public function setFileContent(string $filePath, $content): void {
		$filePathReal = $this->resolvePath($filePath);
		
		// Prepare the content
		if (!is_string($content) && !is_numeric($content))
			$content = static::SERIALIZED_MARKER . serialize($content);
		
		// Write the file
		Permissions::setFilePermissions($this->baseDirectory);
		Fs::writeFile($filePathReal, $content);
		Permissions::setFilePermissions($filePathReal);
	}
	
	/**
	 * Includes a file as a PHP resource
	 *
	 * @param string $filePath The name of the file to include
	 * @param bool   $once     by default we include the file with include_once, if you set this to FALSE the plain
	 *                         include is used instead.
	 *
	 * @return mixed
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFsException
	 */
	public function includeFile(string $filePath, bool $once = TRUE) {
		$filePathReal = $this->resolvePath($filePath);
		if (!$this->hasFileInternal($filePathReal))
			throw new TempFsException("Could not include file: \"$filePath\" because it does not exist!");
		return tempFsIncludeHelper($filePathReal, $once);
	}
	
	/**
	 * Returns the configured base directory, either as absolute, or as relative path (relative to the typo3_better_api
	 * root directory)
	 *
	 * @param bool $relative
	 *
	 * @return string
	 */
	public function getBaseDirectoryPath(bool $relative = FALSE): string {
		if (!$relative) return $this->baseDirectory;
		if (!empty($this->relativeBaseDirectory)) return $this->relativeBaseDirectory;
		return $this->relativeBaseDirectory = Path::makeRelative($this->baseDirectory,
				Path::unifyPath(realpath(ExtensionManagementUtility::extPath("typo3_better_api")))) . DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Completely removes the whole directory and all its files in it
	 */
	public function flush() {
		Fs::flushDirectory($this->baseDirectory);
	}
	
	/**
	 * Flushes the directory if the "all" Cache is cleared
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\CacheClearedEvent $event
	 */
	public function __onCacheClear(CacheClearedEvent $event) {
		if ($event->getGroup() !== "all") return;
		$this->flush();
	}
	
	/**
	 * Internal helper to resolve relative path's inside the base directory
	 *
	 * @param string $path
	 *
	 * @return string
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFsException
	 */
	protected function resolvePath(string $path): string {
		$path = Path::unifyPath($path);
		$pathAbs = Path::makeAbsolute(ltrim($path, DIRECTORY_SEPARATOR), $this->baseDirectory);
		if (stripos($pathAbs, $this->baseDirectory) !== 0 && stripos($pathAbs . "/", $this->baseDirectory) !== 0)
			throw new TempFsException("The requested path \"$path\" does resolve outside the base directory at \"$this->baseDirectory\". It would resolve to: \"$pathAbs\"!");
		return $pathAbs;
	}
	
	/**
	 * Internal helper to check if a file exists
	 *
	 * @param string $filePathReal
	 *
	 * @return bool
	 */
	protected function hasFileInternal(string $filePathReal): bool {
		return file_exists($filePathReal);
	}
	
	/**
	 * Factory method to create a new instance of myself
	 *
	 * @param string $path
	 *
	 * @return \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs
	 */
	public static function makeInstance(string $path): TempFs {
		return new static($path);
	}
}

/**
 * External helper to make sure the file does not inherit the $this context
 *
 * @param string $file
 * @param bool   $once
 *
 * @return mixed
 */
function tempFsIncludeHelper(string $file, bool $once) {
	if ($once) return include_once $file;
	return include $file;
}