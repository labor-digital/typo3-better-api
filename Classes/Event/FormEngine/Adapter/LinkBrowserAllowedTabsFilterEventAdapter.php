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


namespace LaborDigital\T3ba\Event\FormEngine\Adapter;


use LaborDigital\T3ba\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use LaborDigital\T3ba\Event\FormEngine\LinkBrowserAllowedTabsFilterEvent;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Recordlist\Controller\AbstractLinkBrowserController;

class LinkBrowserAllowedTabsFilterEventAdapter extends AbstractCoreHookEventAdapter implements SingletonInterface
{
    protected const PSEUDO_LINK_HANDLER_KEY = 'noop_filter_event_pseudo_handler';
    
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['LinkBrowser']['hooks'][static::class] = [
            'handler' => static::class,
        ];
    }
    
    /**
     * Registers our pseudo handler so we can fetch the controller instance that holds the field configuration
     *
     * @param $linkHandlers
     *
     * @return array
     */
    public function modifyLinkHandlers($linkHandlers): array
    {
        $linkHandlers[static::PSEUDO_LINK_HANDLER_KEY . '.'] = [
            'handler' => LinkBrowserAllowedTabsFilterPseudoHandler::class,
        ];
        
        return $linkHandlers;
    }
    
    /**
     * The main filter method
     *
     * @param $allowedTabs
     * @param $currentLinkParts
     *
     * @return array
     */
    public function modifyAllowedItems($allowedTabs, $currentLinkParts): array
    {
        // Remove the pseudo handler
        $allowedTabs = array_filter($allowedTabs, static function (string $v) {
            return $v !== static::PSEUDO_LINK_HANDLER_KEY;
        });
        
        // There is something wrong if we don't have a controller here
        if (! LinkBrowserAllowedTabsFilterPseudoHandler::$currentController instanceof AbstractLinkBrowserController) {
            return $allowedTabs;
        }
        
        // Dispatch the event
        return static::$bus->dispatch(
            new LinkBrowserAllowedTabsFilterEvent(
                LinkBrowserAllowedTabsFilterPseudoHandler::$currentController,
                $allowedTabs, $currentLinkParts
            )
        )->getAllowedTabs();
    }
}
