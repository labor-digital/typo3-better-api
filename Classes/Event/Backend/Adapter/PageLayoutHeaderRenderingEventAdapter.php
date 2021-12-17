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


namespace LaborDigital\T3ba\Event\Backend\Adapter;


use LaborDigital\T3ba\Event\Backend\PageLayoutHeaderRenderingEvent;
use LaborDigital\T3ba\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use TYPO3\CMS\Backend\Controller\PageLayoutController;

class PageLayoutHeaderRenderingEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][static::class]
            = static::class . '->emit';
    }
    
    public function emit(array $_, PageLayoutController $layoutController)
    {
        return static::$bus
            ->dispatch(new PageLayoutHeaderRenderingEvent($layoutController))
            ->getContentString();
    }
}