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
 * Last modified: 2020.03.19 at 19:59
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\BackendPreview\Adapter;


use LaborDigital\T3BA\Event\BackendPreview\ListLabelRenderingEvent;
use LaborDigital\T3BA\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3BA\Event\CoreHookAdapter\AbstractCoreHookEventAdapter;
use TYPO3\CMS\Backend\Utility\BackendUtility;

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
    public function emit(array &$args)
    {
        $row = empty($args['row']) || ! is_array($args['row']) ? [] : $args['row'];

        // The TcaSelectItems converts all select fields into arrays, which we don't want
        // we have to re-fetch the record from the database. TYPO3 sucks so hard...
        if (is_numeric($row['uid'] ?? null)) {
            $row = BackendUtility::getRecord($args['table'], $row['uid']);
        }

        $e = new ListLabelRenderingEvent(
            $args['table'],
            $row,
            $args['title'],
            $args['options']
        );
        static::$bus->dispatch($e);
        $args['title'] = $e->getTitle();
    }
}
