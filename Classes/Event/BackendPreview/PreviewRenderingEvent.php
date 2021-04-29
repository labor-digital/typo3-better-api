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

namespace LaborDigital\T3BA\Event\BackendPreview;

use LaborDigital\T3BA\Tool\BackendPreview\Hook\BackendPreviewUtils;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;

/**
 * Class BackendPreviewRenderingEvent
 *
 * Is called when the backend tries to draw a preview for a single content element.
 * Mostly for use in the backend preview renderer
 *
 * @package LaborDigital\T3BA\Event\BackendPreview
 */
class PreviewRenderingEvent
{
    /**
     * The column item that should be rendered
     *
     * @var \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem
     */
    protected $item;
    
    /**
     * The utility class for hooks into the TYPO3 core, default content preview renderer
     *
     * @var \LaborDigital\T3BA\Tool\BackendPreview\Hook\BackendPreviewUtils
     */
    protected $utils;
    
    /**
     * The header line that should be displayed for this item
     *
     * @var string|null
     */
    protected $header;
    
    /**
     * The body that should be displayed for this item
     *
     * @var string|null
     */
    protected $body;
    
    /**
     * The footer that should be displayed for this item
     *
     * @var string|null
     */
    protected $footer;
    
    /**
     * Either the signature of the plugin variant that is required or NULL if the default variant should be rendered
     *
     * @var string|null
     */
    protected $pluginVariant;
    
    /**
     * BackendPreviewRenderingEvent constructor.
     *
     * @param   \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem        $item
     * @param   \LaborDigital\T3BA\Tool\BackendPreview\Hook\BackendPreviewUtils  $utils
     * @param   string|null                                                      $pluginVariant
     */
    public function __construct(GridColumnItem $item, BackendPreviewUtils $utils, ?string $pluginVariant)
    {
        $this->item = $item;
        $this->utils = $utils;
        $this->pluginVariant = $pluginVariant;
    }
    
    /**
     * Returns the row of the tt_content record that should be rendered as backend preview
     *
     * @return array
     */
    public function getRow(): array
    {
        return (array)$this->item->getRecord();
    }
    
    /**
     * Returns either the signature of the plugin variant that is required or NULL if the default variant should be
     * rendered.
     *
     * @return string|null
     */
    public function getPluginVariant(): ?string
    {
        return $this->pluginVariant;
    }
    
    /**
     * Returns the header set for the backend preview
     *
     * @return string
     */
    public function getHeader(): ?string
    {
        return $this->header;
    }
    
    /**
     * Updates the header set for the backend preview
     *
     * @param   string  $header
     *
     * @return $this
     */
    public function setHeader(string $header): self
    {
        $this->header = $header;
        
        return $this;
    }
    
    /**
     * Returns the body that should be displayed for this item
     *
     * @return string|null
     */
    public function getBody(): ?string
    {
        return $this->body;
    }
    
    /**
     * Updates the body that should be displayed for this item
     *
     * @param   string|null  $body
     *
     * @return PreviewRenderingEvent
     */
    public function setBody(?string $body): PreviewRenderingEvent
    {
        $this->body = $body;
        
        return $this;
    }
    
    /**
     * Returns the footer that should be displayed for this item
     *
     * @return string|null
     */
    public function getFooter(): ?string
    {
        return $this->footer;
    }
    
    /**
     * Updates the footer that should be displayed for this item
     *
     * @param   string|null  $footer
     *
     * @return PreviewRenderingEvent
     */
    public function setFooter(?string $footer): PreviewRenderingEvent
    {
        $this->footer = $footer;
        
        return $this;
    }
    
    /**
     * Returns the column item that should be rendered
     *
     * @return \TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem
     */
    public function getItem(): GridColumnItem
    {
        return $this->item;
    }
    
    /**
     * Returns the utility class for hooks into the TYPO3 core, default content preview renderer
     *
     * @return \LaborDigital\T3BA\Tool\BackendPreview\Hook\BackendPreviewUtils
     */
    public function getUtils(): BackendPreviewUtils
    {
        return $this->utils;
    }
}
