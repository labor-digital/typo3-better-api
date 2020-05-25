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
 * Last modified: 2020.03.31 at 13:33
 */

namespace LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo;

use LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters\ProcessedFileAdapter;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileServiceException;
use LaborDigital\Typo3BetterApi\LazyLoading\LazyLoading;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class FileInfo
{
    /**
     * Additional, detail info based on the current file type
     * @var \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\VideoFileInfo|\LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\ImageFileInfo
     */
    protected $nestedInfo;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService
     */
    protected $falFileService;
    
    /**
     * The file resource instance to gather the information for
     * @var File
     */
    protected $file;
    
    /**
     * Either the instance of the processed file or null if the file was not processed
     * @var ProcessedFile|null
     */
    protected $processedFile;
    
    /**
     * The file reference instance or null if a file was given without reference
     * @var FileReference|null
     */
    protected $fileReference;
    
    /**
     * FileInfo constructor.
     *
     * @param string|int|FileReference|File|mixed                  $file
     * @param FalFileService                                       $falFileService
     * @param \LaborDigital\Typo3BetterApi\LazyLoading\LazyLoading $lazyLoading
     *
     * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileServiceException
     */
    public function __construct($file, FalFileService $falFileService, LazyLoading $lazyLoading)
    {
        $this->falFileService = $falFileService;
        
        // Handle a processed file
        if ($file instanceof ProcessedFile) {
            $this->processedFile = $file;
            if ($file->hasProperty('@fileReference')) {
                $file = ProcessedFileAdapter::getRawProperty($file, '@fileReference');
            } else {
                $file = $file->getOriginalFile();
            }
        }
        
        // Try to load from database if a numeric value or string was passed
        if (is_numeric($file) || is_string($file)) {
            $file = $falFileService->getFile($file);
        }
        
        // Fail
        if (!is_object($file)) {
            throw new FalFileServiceException('Could not retrieve a file for the given selector!');
        }
        
        // Handle lazy objects
        $file = $lazyLoading->getRealValue($file);
        
        // Handle object storage
        if ($file instanceof ObjectStorage) {
            $file->rewind();
            $file = $file->current();
        }
        
        // Handle ext base wrapping
        if ($file instanceof \TYPO3\CMS\Extbase\Domain\Model\FileReference) {
            $file = $file->getOriginalResource();
        }
        
        // Get the file reference
        if ($file instanceof FileReference) {
            $this->fileReference = $file;
            $file = $file->getOriginalFile();
        }
        
        // Get the file itself
        if ($file instanceof File) {
            $this->file = $file;
        }
        
        // Die
        else {
            throw new FalFileServiceException('Could not retrieve a valid file or file reference for the given selector!');
        }
    }
    
    /**
     * Returns true if the file is handled as a "sys-file-reference" object
     * @return bool
     */
    public function isFileReference(): bool
    {
        return isset($this->fileReference);
    }
    
    /**
     * Returns true if the handled file is a processed file instance
     * @return bool
     */
    public function isProcessed(): bool
    {
        return isset($this->processedFile);
    }
    
    /**
     * Returns the unique id of either the file reference or the file
     * @return int
     */
    public function getUid(): int
    {
        return $this->isFileReference() ? $this->fileReference->getUid() : $this->file->getUid();
    }
    
    /**
     * Returns either the uid of the handled file reference or null if the file is not a file reference
     * @return int|null
     */
    public function getFileReferenceUid(): ?int
    {
        return $this->isFileReference() ? $this->fileReference->getUid() : null;
    }
    
    /**
     * Returns the uid if the low level file object
     * @return int
     */
    public function getFileUid(): int
    {
        return $this->file->getUid();
    }
    
    /**
     * Returns a cache buster string for the file
     * @return string
     */
    public function getHash(): string
    {
        if ($this->isProcessed()) {
            return md5($this->processedFile->getSha1());
        }
        $hash = $this->file->getProperty('identifier_hash') .
            $this->file->getProperty('sha1') .
            $this->file->getProperty('size') .
            $this->file->getProperty('modification_date') .
            $this->file->getProperty('folder_hash');
        return md5($hash);
    }
    
    /**
     * Returns the base name of the current file name
     * @return string
     */
    public function getFileName(): string
    {
        return $this->file->getName();
    }
    
    /**
     * Returns the url of the file handled as absolute url
     *
     * @param bool $withHash Set this to false to disable the cache buster hash that will be added to the file url
     *
     * @return string
     */
    public function getUrl(bool $withHash = true): string
    {
        $url = null;
        if ($this->isProcessed()) {
            $url = $this->processedFile->getPublicUrl();
        } else {
            $url = $this->file->getPublicUrl();
        }
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        return $this->falFileService->Links->getHost() . '/' . $url . ($withHash ? '?hash=' . $this->getHash() : '');
    }
    
    /**
     * Similar to getUrl() but always returns the default url even if the current file is a processed file instance
     *
     * @param bool $withHash Set this to false to disable the cache buster hash that will be added to the file url
     *
     * @return string
     */
    public function getOriginalUrl(bool $withHash = true): string
    {
        // Handle non-processed files
        if (!$this->isProcessed()) {
            return $this->getUrl($withHash);
        }
        
        // Handle processed file
        $backupProcessed = $this->processedFile;
        $this->processedFile = null;
        $url = $this->getUrl($withHash);
        $this->processedFile = $backupProcessed;
        unset($backupProcessed);
        return $url;
    }
    
    /**
     * Returns the mime type of the file
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->isProcessed() ? $this->processedFile->getMimeType() : $this->file->getProperty('mime_type');
    }
    
    /**
     * Returns the size of the handled file in bytes
     *
     * @return int
     */
    public function getSize(): int
    {
        return $this->isProcessed() ? $this->processedFile->getSize() : $this->file->getProperty('size');
    }
    
    /**
     * Returns the file extension of the handled file
     * @return string
     */
    public function getExtension(): string
    {
        return $this->isProcessed() ? $this->processedFile->getExtension() : $this->file->getProperty('extension');
    }
    
    /**
     * Returns the file type as they are defined in the File::FILETYPE_ constants
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->file->getType();
    }
    
    /**
     * Returns true if the handled file is an image
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return $this->getType() === File::FILETYPE_IMAGE;
    }
    
    /**
     * Returns true if the handled file is a video reference
     * @return bool
     */
    public function isVideo(): bool
    {
        return $this->getType() === File::FILETYPE_VIDEO;
    }
    
    /**
     * Returns the raw file instance this information object represents
     * @return \TYPO3\CMS\Core\Resource\File
     */
    public function getFile(): File
    {
        return $this->file;
    }
    
    /**
     * Returns either the currently linked file reference or null if there is none
     *
     * @return \TYPO3\CMS\Core\Resource\FileReference|null
     */
    public function getFileReference(): ?FileReference
    {
        return $this->isFileReference() ? $this->fileReference : null;
    }
    
    /**
     * Returns either the processed file object or null if the file was not processed
     *
     * @return \TYPO3\CMS\Core\Resource\ProcessedFile|null
     */
    public function getProcessedFile(): ?ProcessedFile
    {
        return $this->isProcessed() ? $this->processedFile : null;
    }
    
    /**
     * Returns either additional information if this file is a video or null if this file is not a video
     * @return \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\VideoFileInfo|null
     */
    public function getVideoInfo(): ?VideoFileInfo
    {
        if (!$this->isVideo()) {
            return null;
        }
        if (!empty($this->nestedInfo)) {
            return $this->nestedInfo;
        }
        return $this->nestedInfo = $this->falFileService->getInstanceOf(VideoFileInfo::class, [$this]);
    }
    
    /**
     * Returns either additional information if this file is an image or null if this file is not an image
     * @return \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\ImageFileInfo|null
     */
    public function getImageInfo(): ?ImageFileInfo
    {
        if (!$this->isImage()) {
            return null;
        }
        if (!empty($this->nestedInfo)) {
            return $this->nestedInfo;
        }
        return $this->nestedInfo = $this->falFileService->getInstanceOf(ImageFileInfo::class, [$this]);
    }
}
