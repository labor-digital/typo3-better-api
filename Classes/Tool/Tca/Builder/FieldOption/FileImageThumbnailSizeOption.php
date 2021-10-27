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
 * Last modified: 2021.10.26 at 12:04
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Adds an option to configure the thumbnail size of a FAL file field preview
 */
class FileImageThumbnailSizeOption extends AbstractOption
{
    /**
     * @var int|null
     */
    protected $defaultWidth;
    
    /**
     * @var int|null
     */
    protected $defaultHeight;
    
    public function __construct(?int $defaultWidth = null, ?int $defaultHeight = null)
    {
        $this->defaultWidth = $defaultWidth ?? 200;
        $this->defaultHeight = $defaultHeight ?? 150;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['thumbnailSize'] = [
            'type' => 'array',
            'default' => [$this->defaultWidth, $this->defaultHeight],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $config['appearance']['headerThumbnail']['width'] = $options['thumbnailSize'][0] ?? $this->defaultWidth;
        $config['appearance']['headerThumbnail']['height'] = $options['thumbnailSize'][1] ?? $this->defaultHeight;
    }
    
}