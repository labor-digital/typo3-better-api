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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);
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

namespace LaborDigital\T3ba\Tool\Fal\FileInfo;

use InvalidArgumentException;
use LaborDigital\T3ba\Tool\Fal\FalService;

class ImageFileInfo
{
    
    /**
     * The file info object that represents this image file
     *
     * @var FileInfo
     */
    protected $parent;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Fal\FalService
     */
    protected $falService;
    
    /**
     * ImageFileInfo constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Fal\FileInfo\FileInfo  $parent
     * @param   \LaborDigital\T3ba\Tool\Fal\FalService         $falFileService
     */
    public function __construct(FileInfo $parent, FalService $falFileService)
    {
        $this->parent = $parent;
        $this->falService = $falFileService;
    }
    
    /**
     * Returns the alternative text to this image or an empty string
     *
     * @return string
     */
    public function getAlt(): string
    {
        return $this->parent->getFileReference() !== null
            ? (string)$this->parent->getFileReference()->getAlternative() : '';
    }
    
    /**
     * Returns the title text to this image or an empty string
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->parent->getFileReference() !== null
            ? (string)$this->parent->getFileReference()->getTitle() : '';
    }
    
    /**
     * Returns the description text to this file or an empty string
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->parent->getFileReference() !== null
            ? (string)$this->parent->getFileReference()->getDescription() : '';
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
            $ref = $this->parent->getFileReference();
            if ($ref === null) {
                return 'cc';
            }
            
            return $ref->getReferenceProperty('image_alignment');
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
        try {
            $ref = $this->parent->getFileReference();
            if ($ref === null) {
                return [];
            }
            
            $crop = $ref->getReferenceProperty('crop');
            
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
        
        return $this->falService->getResizedImageUrl($this->parent, ['crop' => $type]);
    }
}
