<?php
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
 * Last modified: 2020.10.06 at 12:17
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\Event\Events;


use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\LinkBrowserAllowedTabsFilterEventAdapter;
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
     * (?)
     * @todo what is this, I just get it from the hook object
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
    ) {
        $this->linkBrowserController = $linkBrowserController;
        $this->allowedTabs           = $allowedTabs;
        $this->currentLinkParts      = $currentLinkParts;
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
     * Returns something... I don't know what... but it might be important /o\
     *
     * @return array
     */
    public function getCurrentLinkParts(): array
    {
        return $this->currentLinkParts;
    }

}
