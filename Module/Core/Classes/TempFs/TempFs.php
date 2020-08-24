<?php
declare(strict_types=1);
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
 * Last modified: 2020.08.22 at 21:56
 */

namespace LaborDigital\T3BA\Core\TempFs;

use LaborDigital\T3BA\Core\TempFs\Exception\FileNotFoundException;
use LaborDigital\T3BA\Core\TempFs\Exception\InvalidFilePathException;
use LaborDigital\T3BA\Core\TempFs\Exception\InvalidRootPathException;
use LaborDigital\T3BA\Core\Util\FilePermissionUtil;
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
class TempFs
{

    /**
     * Marker that is prepended in front of serialized file contents
     */
    protected const SERIALIZED_MARKER = '__SERIALIZED__:';

    /**
     * The baseDirectory as a path relative to the better api root directory.
     * This is required for typo script and flex form registration.
     *
     * @var string|null
     */
    protected $relativeBaseDirectory;

    /**
     * The base directory where the file system will work in
     *
     * @var string
     */
    protected $rootPath;

    /**
     * TempFs constructor.
     *
     * @param   string  $rootPath  An additional root path inside the typo3 temp directory
     *
     * @throws \LaborDigital\T3BA\Core\TempFs\Exception\InvalidRootPathException
     * @noinspection SuspiciousAssignmentsInspection
     */
    public function __construct(string $rootPath)
    {
        $this->rootPath = Path::unifyPath(BETTER_API_TYPO3_VAR_PATH, '/') . 'tempFs/';
        $this->rootPath = $this->resolvePath('/' . ltrim(Path::unifyPath($rootPath, '/'), '/'));

        if (is_file($this->rootPath)) {
            throw new InvalidRootPathException(
                'The resolved root directory path: "' . $this->rootPath . '" seems to lead to a file!');
        }
        if (! is_writable($this->rootPath)) {
            Fs::mkdir($this->rootPath);
            FilePermissionUtil::setFilePermissions($this->rootPath);
        }
        if (! is_writable($this->rootPath) && ! is_writable(dirname($this->rootPath))) {
            throw new InvalidRootPathException(
                'The resolved root directory path: "' . $this->rootPath . '" is not writable by the web-server!');
        }
    }

    /**
     * Returns true if a file exists, false if not
     *
     * @param   string  $filePath  The name / relative path of the file to check
     *
     * @return bool
     */
    public function hasFile(string $filePath): bool
    {
        $filePathReal = $this->resolvePath($filePath);

        return $this->hasFileInternal($filePathReal);
    }

    /**
     * Returns the file object for the required file path
     *
     * @param   string  $filePath  The name / relative path of the file to retrieve
     *
     * @return \SplFileInfo
     * @throws \LaborDigital\T3BA\Core\TempFs\Exception\FileNotFoundException
     */
    public function getFile(string $filePath): SplFileInfo
    {
        $filePathReal = $this->resolvePath($filePath);
        if (! $this->hasFileInternal($filePathReal)) {
            throw new FileNotFoundException('Could not get the file: "' . $filePath . '" because it does not exist!');
        }

        return new SplFileInfo($filePathReal);
    }

    /**
     * Returns the content of a required file.
     * It will automatically unpack serialized values back into their PHP values
     *
     * @param   string  $filePath  The name / relative path of the file to read
     *
     * @return mixed
     * @throws \LaborDigital\T3BA\Core\TempFs\Exception\FileNotFoundException
     */
    public function getFileContent(string $filePath)
    {
        // Try to load the content
        $filePathReal = $this->resolvePath($filePath);
        if (! $this->hasFileInternal($filePathReal)) {
            throw new FileNotFoundException(
                'Could not get the contents of file: "' . $filePath . '" because it does not exist!'
            );
        }
        $content = Fs::readFile($filePathReal);

        // Deserialize serialized content
        if (str_starts_with($content, static::SERIALIZED_MARKER)) {
            /** @noinspection UnserializeExploitsInspection */
            $content = unserialize(substr($content, strlen(static::SERIALIZED_MARKER)));
        }

        // Done
        return $content;
    }

    /**
     * Is used to dump some content into a file.
     * Automatically serializes non-string/numeric content before writing it as a file
     *
     * @param   string        $filePath  The name / relative path of the file to dump the content to
     * @param   string|mixed  $content   Either a string (will be dumped as string) or anything else (will be dumped as
     *                                   serialized value)
     */
    public function setFileContent(string $filePath, $content): void
    {
        $filePathReal = $this->resolvePath($filePath);

        // Prepare the content
        if (! is_string($content) && ! is_numeric($content)) {
            $content = static::SERIALIZED_MARKER . serialize($content);
        }

        // Write the file
        FilePermissionUtil::setFilePermissions($this->rootPath);
        Fs::writeFile($filePathReal, $content);
        FilePermissionUtil::setFilePermissions($filePathReal);
    }

    /**
     * Includes a file as a PHP resource
     *
     * @param   string  $filePath  The name of the file to include
     * @param   bool    $once      by default we include the file with include_once, if you set this to FALSE the plain
     *                             include is used instead.
     *
     * @return mixed
     * @throws \LaborDigital\T3BA\Core\TempFs\Exception\FileNotFoundException
     */
    public function includeFile(string $filePath, bool $once = true)
    {
        $filePathReal = $this->resolvePath($filePath);
        if (! $this->hasFileInternal($filePathReal)) {
            throw new FileNotFoundException(
                'Could not include file: "' . $filePath . '" because it does not exist!'
            );
        }

        return tempFsIncludeHelper($filePathReal, $once);
    }

    /**
     * Returns the configured base directory, either as absolute, or as relative path (relative to the typo3_better_api
     * root directory)
     *
     * @param   bool  $relative  Set this to true if you want to retrieve the relative path based on the better api
     *                           extension. Useful for compiling typoScript or flexForm files
     *
     * @return string
     */
    public function getBaseDirectoryPath(bool $relative = false): string
    {
        if (! $relative) {
            return $this->rootPath;
        }
        if (! empty($this->relativeBaseDirectory)) {
            return $this->relativeBaseDirectory;
        }

        return $this->relativeBaseDirectory = Path::makeRelative(
                $this->rootPath,
                Path::unifyPath(realpath(ExtensionManagementUtility::extPath('T3BA')))
            ) . DIRECTORY_SEPARATOR;
    }

    /**
     * Removes a single file or directory from the file system
     *
     * @param   string  $filePath
     *
     * @return bool
     */
    public function delete(string $filePath): bool
    {
        if ($this->hasFile($filePath)) {
            Fs::remove($this->resolvePath($filePath));

            return true;
        }

        return false;
    }

    /**
     * Completely removes the whole directory and all files in it
     */
    public function flush(): void
    {
        Fs::flushDirectory($this->rootPath);
    }

    /**
     * Internal helper to resolve relative path's inside the base directory
     *
     * @param   string  $path
     *
     * @return string
     * @throws \LaborDigital\T3BA\Core\TempFs\Exception\InvalidFilePathException
     */
    protected function resolvePath(string $path): string
    {
        $path    = Path::unifyPath($path);
        $pathAbs = Path::makeAbsolute(ltrim($path, DIRECTORY_SEPARATOR), $this->rootPath);
        if (stripos($pathAbs, $this->rootPath) !== 0 && stripos($pathAbs . '/', $this->rootPath) !== 0) {
            throw new InvalidFilePathException(
                'The requested path "' . $path . '" does not lead to a file inside the registered root directory at: "'
                . $this->rootPath . '", instead it would lead to: "' . $pathAbs . '"!');
        }

        return $pathAbs;
    }

    /**
     * Internal helper to check if a file exists
     *
     * @param   string  $filePathReal
     *
     * @return bool
     */
    protected function hasFileInternal(string $filePathReal): bool
    {
        return file_exists($filePathReal);
    }

    /**
     * Factory method to create a new instance of myself
     *
     * @param   string  $path
     *
     * @return \LaborDigital\T3BA\Core\TempFs\TempFs
     */
    public static function makeInstance(string $path): self
    {
        return new static($path);
    }

    /**
     * Factory method to create a new low level file system cache instance
     *
     * @param   string  $key
     *
     * @return \LaborDigital\T3BA\Core\TempFs\TempFsCache
     */
    public static function makeCache(string $key): TempFsCache
    {
        return new TempFsCache(static::makeInstance('Cache/' . str_replace(['/', '\\'], '-', $key)));
    }
}

/**
 * External helper to make sure the file does not inherit the $this context
 *
 * @param   string  $file
 * @param   bool    $once
 *
 * @return mixed
 */
function tempFsIncludeHelper(string $file, bool $once)
{
    if ($once) {
        /** @noinspection UsingInclusionOnceReturnValueInspection */
        return include_once $file;
    }

    return include $file;
}
