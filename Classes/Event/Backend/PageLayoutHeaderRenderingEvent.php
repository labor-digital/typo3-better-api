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
 * Last modified: 2021.12.17 at 11:09
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Backend;


use LaborDigital\T3ba\Event\Backend\Adapter\PageLayoutHeaderRenderingEventAdapter;
use LaborDigital\T3ba\Event\CoreHookAdapter\CoreHookEventInterface;
use TYPO3\CMS\Backend\Controller\PageLayoutController;

/**
 * Dispatched when the TYPO3 backend executes the page layout header hook.
 * Registered at: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook']
 */
class PageLayoutHeaderRenderingEvent implements CoreHookEventInterface
{
    /**
     * @var \TYPO3\CMS\Backend\Controller\PageLayoutController
     */
    protected PageLayoutController $pageLayoutController;
    
    /**
     * A list of contents that have been appended
     *
     * @var array
     */
    protected $content = [];
    
    public function __construct(PageLayoutController $pageLayoutController)
    {
        $this->pageLayoutController = $pageLayoutController;
    }
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return PageLayoutHeaderRenderingEventAdapter::class;
    }
    
    /**
     * Adds a new piece of content to be rendered as a header
     *
     * @param   string  $content
     *
     * @return $this
     */
    public function appendContent(string $content): self
    {
        $this->content[] = $content;
        
        return $this;
    }
    
    /**
     * Replaces all current contents with the given array of strings
     *
     * @param   array  $contents
     *
     * @return $this
     */
    public function replaceContent(array $contents): self
    {
        $this->content = $contents;
        
        return $this;
    }
    
    /**
     * Returns the list of all registered contents for the header
     *
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }
    
    /**
     * Returns the concatenated string of all registered contents
     *
     * @return string
     */
    public function getContentString(): string
    {
        return implode('', array_map(function ($v): string {
            return trim((string)$v);
        }, $this->content));
    }
    
    /**
     * Returns the instance of the page layout controller which is currently being executed
     *
     * @return \TYPO3\CMS\Backend\Controller\PageLayoutController
     */
    public function getPageLayoutController(): PageLayoutController
    {
        return $this->pageLayoutController;
    }
}