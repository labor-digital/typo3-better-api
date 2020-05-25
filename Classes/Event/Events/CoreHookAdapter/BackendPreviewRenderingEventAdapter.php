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
 * Last modified: 2020.03.19 at 20:09
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter;

use LaborDigital\Typo3BetterApi\Event\Events\BackendPreviewRenderingEvent;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;

class BackendPreviewRenderingEventAdapter extends AbstractCoreHookEventAdapter implements PageLayoutViewDrawItemHookInterface
{
    
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']
        ['tt_content_drawItem'][static::class] = static::class;
    }
    
    /**
     * @inheritDoc
     */
    public function preProcess(PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row)
    {
        static::$bus->dispatch(($e =
            new BackendPreviewRenderingEvent($row, $headerContent, $itemContent, $drawItem, $parentObject)));
        $drawItem = !$e->isRendered();
        $headerContent = $e->getHeader();
        $itemContent = $e->getContent();
    }
}
