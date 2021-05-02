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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3ba\Tool\BackendPreview;

use LaborDigital\T3ba\Event\BackendPreview\PreviewRenderingEvent;
use LaborDigital\T3ba\Tool\BackendPreview\Hook\BackendPreviewUtils;

class BackendPreviewRendererContext
{
    
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
     * Holds the rendered footer of this element's backend preview
     *
     * @var string
     */
    protected $footer = '';
    
    /**
     * The raw typo event instance
     *
     * @var \LaborDigital\T3ba\Event\BackendPreview\PreviewRenderingEvent
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
     * By default the configured description for this content element will be shown
     * If this is set to false it is hidden
     *
     * @var bool
     */
    protected $showDescription = true;
    
    /**
     * BackendPreviewRendererContext constructor.
     *
     * @param   \LaborDigital\T3ba\Event\BackendPreview\PreviewRenderingEvent  $event
     */
    public function __construct(PreviewRenderingEvent $event)
    {
        $this->event = $event;
    }
    
    /**
     * Returns either the signature of the plugin variant that is required or NULL if the default variant should be
     * rendered.
     *
     * @return string|null
     */
    public function getPluginVariant(): ?string
    {
        return $this->event->getPluginVariant();
    }
    
    /**
     * Returns the row of data that was given to this element
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->event->getRow();
    }
    
    /**
     * Some additional utilities that help when rendering a backend preview
     *
     * @return \LaborDigital\T3ba\Tool\BackendPreview\Hook\BackendPreviewUtils
     */
    public function getUtils(): BackendPreviewUtils
    {
        return $this->event->getUtils();
    }
    
    /**
     * Returns the raw typo event instance
     *
     * @return \LaborDigital\T3ba\Event\BackendPreview\PreviewRenderingEvent
     */
    public function getEvent(): PreviewRenderingEvent
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
     * @return $this
     */
    public function setHeader(string $header): self
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
     * @return $this
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        
        return $this;
    }
    
    /**
     * Returns either the set footer or an empty string
     *
     * @return string
     */
    public function getFooter(): string
    {
        return $this->footer;
    }
    
    /**
     * Sets the rendered footer of this element's backend preview
     *
     * @param   string  $footer
     *
     * @return $this
     */
    public function setFooter(string $footer): self
    {
        $this->footer = $footer;
        
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
    
    /**
     * Returns true if the preview should include the content element description, false if not
     *
     * @return bool
     */
    public function showDescription(): bool
    {
        return $this->showDescription;
    }
    
    /**
     * Allows you to set if the description text (registered for the new-content-element-wizard)
     * should be rendered (default) or not.
     *
     * @param   bool  $showDescription
     *
     * @return BackendPreviewRendererContext
     */
    public function setShowDescription(bool $showDescription = true): BackendPreviewRendererContext
    {
        $this->showDescription = $showDescription;
        
        return $this;
    }
}
