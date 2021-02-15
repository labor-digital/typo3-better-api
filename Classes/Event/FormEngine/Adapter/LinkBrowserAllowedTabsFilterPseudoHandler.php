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
 * Last modified: 2020.10.06 at 12:28
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\FormEngine\Adapter;


use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;
use TYPO3\CMS\Recordlist\LinkHandler\LinkHandlerInterface;

class LinkBrowserAllowedTabsFilterPseudoHandler implements LinkHandlerInterface
{
    /**
     * @var AbstractLinkBrowserController
     */
    public static $currentController;

    /**
     * @inheritDoc
     */
    public function getLinkAttributes()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function modifyLinkAttributes(array $fieldDefinitions)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function initialize(AbstractLinkBrowserController $linkBrowser, $identifier, array $configuration)
    {
        static::$currentController = $linkBrowser;
    }

    /**
     * @inheritDoc
     */
    public function canHandleLink(array $linkParts)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function formatCurrentUrl()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function render(ServerRequestInterface $request)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function isUpdateSupported()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getBodyTagAttributes()
    {
        return [];
    }

}
