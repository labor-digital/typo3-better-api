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
 * Last modified: 2020.03.19 at 01:59
 */

namespace LaborDigital\Typo3BetterApi\BackendPreview;

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutView;

class BackendPreviewRendererContext
{
    use CommonServiceLocatorTrait;
    
    /**
     * The row of data that was given to this element
     *
     * @var array
     */
    protected $row;
    
    /**
     * An additional header that will be placed above the rendered body
     *
     * @var string
     */
    protected $header = '';
    
    /**
     * Holds the rendered body of this element's backend preview
     *
     * @var string
     */
    protected $body = '';
    
    /**
     * The page layout view that requested this rendering
     *
     * @var \TYPO3\CMS\Backend\View\PageLayoutView
     */
    protected $pageLayoutView;
    
    /**
     * The raw typo event instance
     *
     * @var \LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent
     */
    protected $event;
    
    /**
     * By default the preview content will be wrapped in a link tag.
     * The link will lead the editor directly to the editing mode of the clicked element.
     * If you set this to false, the link will not be generated.
     *
     * @var bool
     */
    protected $linkPreview = true;
    
    /**
     * BackendPreviewRendererContext constructor.
     *
     * @param   \TYPO3\CMS\Backend\View\PageLayoutView                                  $pageLayoutView
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent  $event
     * @param   array                                                                   $row
     */
    public function __construct(PageLayoutView $pageLayoutView, BackendPreviewRenderingEvent $event, array $row)
    {
        $this->pageLayoutView = $pageLayoutView;
        $this->event          = $event;
        $this->row            = $row;
    }
    
    /**
     * Returns the row of data that was given to this element
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }
    
    /**
     * Returns the page layout view that requested this rendering
     *
     * @return \TYPO3\CMS\Backend\View\PageLayoutView
     */
    public function getPageLayoutView(): PageLayoutView
    {
        return $this->pageLayoutView;
    }
    
    /**
     * Returns the raw typo event instance
     *
     * @return \LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent
     */
    public function getEvent(): BackendPreviewRenderingEvent
    {
        return $this->event;
    }
    
    /**
     * Returns either the given header or an empty string
     *
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }
    
    /**
     * Can be used to set an additional header that will be placed above the rendered body
     *
     * @param   string  $header
     *
     * @return BackendPreviewRendererContext
     */
    public function setHeader(string $header): BackendPreviewRendererContext
    {
        $this->header = $header;
        
        return $this;
    }
    
    /**
     * Returns either the set body or an empty string
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }
    
    /**
     * Sets the rendered body of this element's backend preview
     *
     * @param   string  $body
     *
     * @return BackendPreviewRendererContext
     */
    public function setBody(string $body): BackendPreviewRendererContext
    {
        $this->body = $body;
        
        return $this;
    }
    
    /**
     * Returns true if the preview content should be wrapped in a link tag, false if not
     *
     * @return bool
     */
    public function isLinkPreview(): bool
    {
        return $this->linkPreview;
    }
    
    /**
     * By default the preview content will be wrapped in a link tag.
     * The link will lead the editor directly to the editing mode of the clicked element.
     * If you set this to false, the link will not be generated.
     *
     * @param   bool  $linkPreview
     */
    public function setLinkPreview(bool $linkPreview): void
    {
        $this->linkPreview = $linkPreview;
    }
}
