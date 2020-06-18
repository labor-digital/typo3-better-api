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
 * Last modified: 2020.03.31 at 22:13
 */

namespace LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo;

use InvalidArgumentException;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;

class ImageFileInfo
{
    
    /**
     * The file info object that represents this image file
     *
     * @var \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\FileInfo
     */
    protected $parent;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService
     */
    protected $falFileService;
    
    /**
     * ImageFileInfo constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\FileInfo  $parent
     */
    public function __construct(FileInfo $parent, FalFileService $falFileService)
    {
        $this->parent         = $parent;
        $this->falFileService = $falFileService;
    }
    
    /**
     * Returns the alternative text to this image or an empty string
     *
     * @return string
     */
    public function getAlt(): string
    {
        return $this->parent->isFileReference() ? (string)$this->parent->getFileReference()->getAlternative() : '';
    }
    
    /**
     * Returns the title text to this image or an empty string
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->parent->isFileReference() ? (string)$this->parent->getFileReference()->getTitle() : '';
    }
    
    /**
     * Returns the description text to this file or an empty string
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->parent->isFileReference() ? (string)$this->parent->getFileReference()->getDescription() : '';
    }
    
    /**
     * Returns the width of the image in pixels
     *
     * @return int
     */
    public function getWidth(): int
    {
        return (int)$this->parent->getFile()->getProperty('width');
    }
    
    /**
     * Returns the height of the image in pixels
     *
     * @return int
     */
    public function getHeight(): int
    {
        return (int)$this->parent->getFile()->getProperty('height');
    }
    
    /**
     * Returns the image alignment if it the matching field was registered in the sys_file_reference tca.
     * The field should be called "image_alignment"
     *
     * @return string
     */
    public function getImageAlignment(): string
    {
        if (! $this->parent->isFileReference()) {
            return 'cc';
        }
        try {
            return $this->parent->getFileReference()->getReferenceProperty('image_alignment');
        } catch (InvalidArgumentException $e) {
        }
        
        return 'cc';
    }
    
    /**
     * Returns the registered crop variants for this image by their key
     *
     * @return array
     */
    public function getCropVariants(): array
    {
        if (! $this->parent->isFileReference()) {
            return [];
        }
        try {
            $crop = $this->parent->getFileReference()->getReferenceProperty('crop');
            
            return \GuzzleHttp\json_decode($crop, true);
        } catch (InvalidArgumentException $e) {
        }
        
        return [];
    }
    
    /**
     * Returns the url of a variant of this image that was cropped based on the given type.
     * If the image couldn't been cropped or the variant with $type was not found, the original url will be returned
     *
     * @param   string  $type  The name of the crop variant to apply
     *
     * @return string
     */
    public function getCroppedUrl(string $type): string
    {
        if (! $this->parent->isFileReference()) {
            return $this->parent->getUrl();
        }
        $variants = $this->getCropVariants();
        if (! isset($variants[$type])) {
            return $this->parent->getUrl();
        }
        
        return $this->falFileService->getResizedImageUrl($this->parent->getFileReference(), ['crop' => $type]);
    }
}
