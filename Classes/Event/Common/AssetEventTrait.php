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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\Common;

use TYPO3\CMS\Core\Page\PageRenderer;

trait AssetEventTrait
{
    /**
     * The list of all assets to be filtered
     *
     * @var array
     */
    protected $assets;
    
    /**
     * The instance of the page renderer object
     *
     * @var \TYPO3\CMS\Core\Page\PageRenderer
     */
    protected $pageRenderer;
    
    /**
     * AssetFilterTrait constructor.
     *
     * @param   array                              $assets
     * @param   \TYPO3\CMS\Core\Page\PageRenderer  $pageRenderer
     */
    public function __construct(array $assets, PageRenderer $pageRenderer)
    {
        $this->assets = $assets;
        $this->pageRenderer = $pageRenderer;
    }
    
    /**
     * Returns the list of all assets to be filtered
     *
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }
    
    /**
     * Updates the list of all assets to be filtered
     *
     * @param   array  $assets
     *
     * @return AssetEventTrait
     */
    public function setAssets(array $assets): AssetEventTrait
    {
        $this->assets = $assets;
        
        return $this;
    }
    
    /**
     * Returns the instance of the page renderer object
     *
     * @return \TYPO3\CMS\Core\Page\PageRenderer
     */
    public function getPageRenderer(): PageRenderer
    {
        return $this->pageRenderer;
    }
}
