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
 * Last modified: 2020.03.31 at 21:54
 */

namespace LaborDigital\T3ba\Tool\Fal\FileInfo;

class VideoFileInfo
{
    
    /**
     * The file info object that represents this video file
     *
     * @var FileInfo
     */
    protected $parent;
    
    /**
     * VideoFileInfo constructor.
     *
     * @param   FileInfo  $parent
     */
    public function __construct(FileInfo $parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * Returns the title text to this video or an empty string
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
     * Returns true if this video has an auto-play or not
     *
     * @return bool
     */
    public function isAutoPlay(): bool
    {
        return $this->parent->getFileReference() !== null &&
               $this->parent->getFileReference()->getReferenceProperty('autoplay');
    }
    
    /**
     * Returns true if this is a youTube video, false if not
     *
     * @return bool
     */
    public function isYouTube(): bool
    {
        return $this->parent->getMimeType() === 'video/youtube';
    }
    
    /**
     * Returns true if this is a vimeo video, false if not
     *
     * @return bool
     */
    public function isVimeo(): bool
    {
        return $this->parent->getMimeType() === 'video/vimeo';
    }
    
    /**
     * Returns the video id on youtube or on vimeo or the url if the video is locally hosted
     *
     * @return string
     */
    public function getVideoId(): string
    {
        if ($this->isVimeo() || $this->isYouTube()) {
            return $this->parent->getFile()->getContents();
        }
        
        return $this->parent->getUrl();
    }
}
