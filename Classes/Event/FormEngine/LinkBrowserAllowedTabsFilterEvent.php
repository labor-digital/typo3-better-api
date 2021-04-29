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


namespace LaborDigital\T3BA\Event\FormEngine;


use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3BA\Event\FormEngine\Adapter\LinkBrowserAllowedTabsFilterEventAdapter;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;

class LinkBrowserAllowedTabsFilterEvent implements CoreHookEventInterface
{
    
    /**
     * The instance of the link browser controller that is currently rendered
     *
     * @var \TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController
     */
    protected $linkBrowserController;
    
    /**
     * The list of allowed link handler/tab identifiers
     *
     * @var array
     */
    protected $allowedTabs;
    
    /**
     * The parsed parts of the currently selected link
     *
     * @var array
     */
    protected $currentLinkParts;
    
    /**
     * LinkBrowserAllowedItemsFilterEvent constructor.
     *
     * @param   AbstractLinkBrowserController  $linkBrowserController
     * @param   array                          $allowedTabs
     * @param   array                          $currentLinkParts
     */
    public function __construct(
        AbstractLinkBrowserController $linkBrowserController,
        array $allowedTabs,
        array $currentLinkParts
    )
    {
        $this->linkBrowserController = $linkBrowserController;
        $this->allowedTabs = $allowedTabs;
        $this->currentLinkParts = $currentLinkParts;
    }
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return LinkBrowserAllowedTabsFilterEventAdapter::class;
    }
    
    /**
     * Returns the instance of the link browser controller that is currently rendered
     *
     * @return \TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController
     */
    public function getLinkBrowserController(): AbstractLinkBrowserController
    {
        return $this->linkBrowserController;
    }
    
    /**
     * Returns the tca configuration of the field which required the link browser
     *
     * @return array
     */
    public function getFieldConfig(): array
    {
        return $this->linkBrowserController->getParameters();
    }
    
    /**
     * Returns the list of allowed link handler/tab identifiers
     *
     * @return array
     */
    public function getAllowedTabs(): array
    {
        return $this->allowedTabs;
    }
    
    /**
     * Used to update the list of allowed link handler/tab identifiers
     *
     * @param   array  $allowedTabs
     *
     * @return LinkBrowserAllowedTabsFilterEvent
     */
    public function setAllowedTabs(array $allowedTabs): LinkBrowserAllowedTabsFilterEvent
    {
        $this->allowedTabs = $allowedTabs;
        
        return $this;
    }
    
    /**
     * Returns the parsed parts of the currently selected link
     *
     * @return array
     */
    public function getCurrentLinkParts(): array
    {
        return $this->currentLinkParts;
    }
    
}
