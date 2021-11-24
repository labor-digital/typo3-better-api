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
 * Last modified: 2021.11.19 at 16:47
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\Addon;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3ba\Tool\TypoContext\StaticTypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;

class PidInForeignWhereClause implements NoDiInterface
{
    use StaticTypoContextAwareTrait;
    
    /**
     * Checks if the foreign_table_where string of a column contains a ###PID_LIST()
     * definition, which was injected by LimitToPidsOption and if so, resolves the pids
     * using the current page context
     *
     * @param   \LaborDigital\T3ba\Event\FormEngine\FormFilterEvent  $event
     */
    public static function onFormFilter(FormFilterEvent $event): void
    {
        $data = $event->getData();
        
        foreach ($data['processedTca']['columns'] as &$config) {
            $where = $config['config']['foreign_table_where'];
            if (! empty($where) && is_string($where) && str_contains($where, '###PID_LIST(')) {
                $config['config']['foreign_table_where']
                    = preg_replace_callback('~###PID_LIST\((.*?)\)###~', function ($m) {
                    if (empty($m[1])) {
                        return '0';
                    }
                    
                    $pids = Arrays::makeFromStringList((string)$m[1]);
                    if (empty($pids)) {
                        return '0';
                    }
                    
                    return '0,' . implode(',', static::getTypoContext()->pid()->getMultiple($pids));
                }, $where);
            }
        }
        unset($config);
        
        $event->setData($data);
    }
}