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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Event\BackendPreview\Adapter;


use LaborDigital\T3ba\Event\BackendPreview\ContentListLabelRenderingEvent;
use LaborDigital\T3ba\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3ba\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;

class ListLabelRenderingEventAdapter extends AbstractCoreHookEventAdapter
{
    /**
     * @inheritDoc
     */
    public static function bind(): void
    {
        static::$bus->addListener(TcaCompletelyLoadedEvent::class, static function () {
            $GLOBALS['TCA']['tt_content']['ctrl']['label_userFunc'] = static::class . '->emit';
        }, ['priority' => 300]);
    }
    
    /**
     * Emit the hook for the content table
     *
     * @param   array  $args
     */
    public function emit(array &$args): void
    {
        $row = empty($args['row']) || ! is_array($args['row']) ? [] : $args['row'];
        
        $e = new ContentListLabelRenderingEvent(
            $args['table'],
            $row,
            $args['title'],
            $args['options']
        );
        static::$bus->dispatch($e);
        $args['title'] = $e->getTitle();
    }
}
