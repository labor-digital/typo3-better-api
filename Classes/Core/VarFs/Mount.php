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
 * Last modified: 2021.06.04 at 16:30
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\VarFs;


use LaborDigital\T3ba\Core\Util\FilePermissionUtil;
use LaborDigital\T3ba\Core\VarFs\Exception\FileNotFoundException;
use LaborDigital\T3ba\Core\VarFs\Exception\InvalidFilePathException;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\FileSystem\Fs;
use Neunerlei\PathUtil\Path;
use SplFileInfo;
use Throwable;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class Mount
{
    /**
     * Marker that is prepended in front of serialized file contents
     */
    protected const SERIALIZED_MARKER = '__SERIALIZED__:';
    
    /**
     * The path to the directory where the mount should be stored
     *
     * @var string
     */
    protected $mountPath;
    
    /**
     * The baseDirectory as a path relative to the better api root directory.
     * This is required for typo script and flex form registration.
     *
     * @var string|null
     */
    protected $relativeBaseDirectory;
    
    /**
     * True if the local file system was checked and the mount is initialized
     *
     * @var bool
     */
    protected $isInitialized = false;
    
    /**
     * Mount constructor.
     *
     * @param   string  $mountPath
     */
    public function __construct(string $mountPath)
    {
        $this->mountPath = $mountPath;
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
     * @throws \LaborDigital\T3ba\Core\VarFs\Exception\FileNotFoundException
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
     * @throws \LaborDigital\T3ba\Core\VarFs\Exception\FileNotFoundException
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
            return SerializerUtil::unserialize(substr($content, strlen(static::SERIALIZED_MARKER)));
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
        FilePermissionUtil::setFilePermissions($this->mountPath);
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
     * @throws \LaborDigital\T3ba\Core\VarFs\Exception\FileNotFoundException
     */
    public function includeFile(string $filePath, bool $once = true)
    {
        $filePathReal = $this->resolvePath($filePath);
        if (! $this->hasFileInternal($filePathReal)) {
            throw new FileNotFoundException(
                'Could not include file: "' . $filePath . '" because it does not exist!'
            );
        }
        
        return varFsIncludeHelper($filePathReal, $once);
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
            return $this->mountPath;
        }
        if (! empty($this->relativeBaseDirectory)) {
            return $this->relativeBaseDirectory;
        }
        
        return $this->relativeBaseDirectory = Path::makeRelative(
                $this->mountPath,
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
     * Completely removes all files and directories inside this mount
     */
    public function flush(): void
    {
        Fs::flushDirectory($this->mountPath);
    }
    
    /**
     * Internal helper to resolve relative path's inside the base directory
     *
     * @param   string  $path
     *
     * @return string
     * @throws \LaborDigital\T3ba\Core\VarFs\Exception\InvalidFilePathException
     */
    protected function resolvePath(string $path): string
    {
        if (! $this->isInitialized) {
            $this->isInitialized = true;
            if (! is_dir($this->mountPath)) {
                try {
                    Fs::remove($this->mountPath);
                } catch (Throwable $e) {
                    if (! is_dir($this->mountPath)) {
                        Fs::remove($this->mountPath);
                    }
                }
            }
            
            if (! is_writable($this->mountPath)) {
                Fs::mkdir($this->mountPath);
                FilePermissionUtil::setFilePermissions($this->mountPath);
            }
        }
        
        $path = Path::unifyPath($path);
        $pathAbs = Path::makeAbsolute(ltrim($path, DIRECTORY_SEPARATOR), $this->mountPath);
        if (stripos($pathAbs, $this->mountPath) !== 0 && stripos($pathAbs . '/', $this->mountPath) !== 0) {
            throw new InvalidFilePathException(
                'The path "' . $path . '" does not lead to a file inside the registered mount directory at: "'
                . $this->mountPath . '", instead it would lead to: "' . $pathAbs . '"!');
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
}

/**
 * External helper to make sure the file does not inherit the $this context
 *
 * @param   string  $file
 * @param   bool    $once
 *
 * @return mixed
 */
function varFsIncludeHelper(string $file, bool $once)
{
    if ($once) {
        /** @noinspection UsingInclusionOnceReturnValueInspection */
        return include_once $file;
    }
    
    return include $file;
}
