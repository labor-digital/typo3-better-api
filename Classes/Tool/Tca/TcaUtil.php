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
 * Last modified: 2020.11.01 at 22:58
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca;


use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class TcaUtil
{
    /**
     * Contains the tca columns for the last 20 requested types to save performance
     *
     * @var array
     */
    protected static $resolvedTypeTca = [];

    /**
     * Runs the given callback where the tca type overrides are applied to the global tca array.
     * This allows us to render the correct labels even if we have overrides
     *
     * @param   array     $row       The database row to resolve the correct tca for
     * @param   string    $table     The name of the database table to resolve the tca for
     * @param   callable  $callback  The callback to execute
     *
     * @return mixed
     */
    public static function runWithResolvedTypeTca(array $row, string $table, callable $callback)
    {
        try {
            $rowType = BackendUtility::getTCAtypeValue($table, $row);
        } catch (\Throwable $e) {
            // This is a bugfix, because sometimes we might encounter an array where we would
            // normally expect a value. e.g. CType in the list label renderer... I don't know why this happens
            // but this will fix the issue
            $rowType = BackendUtility::getTCAtypeValue($table, array_map(static function ($v) {
                return is_array($v) ? reset($v) : $v;
            }, $row));
        }

        $tcaBackup = Arrays::getPath($GLOBALS, ['TCA', $table, 'columns']);
        $key       = $table . '_' . $rowType;

        try {
            if (! empty($rowType)) {
                if (isset(static::$resolvedTypeTca[$key])) {
                    $GLOBALS['TCA'][$table]['columns'] = static::$resolvedTypeTca[$key];

                    // Move the entry to the bottom -> Keep it longer in our short time memory
                    unset(static::$resolvedTypeTca[$key]);
                    static::$resolvedTypeTca[$key] = $GLOBALS['TCA'][$table]['columns'];
                } else {
                    $typeColumns                       = Arrays::getPath($GLOBALS,
                        ['TCA', $table, 'types', $rowType, 'columnsOverrides'], []);
                    $GLOBALS['TCA'][$table]['columns'] = Arrays::merge($tcaBackup, $typeColumns);

                    // Only keep the last 20 results -> Save a bit of memory here...
                    if (count(static::$resolvedTypeTca) > 20) {
                        array_shift(static::$resolvedTypeTca);
                    }
                    static::$resolvedTypeTca[$key] = $GLOBALS['TCA'][$table]['columns'];
                }
            }

            return $callback();
        } finally {
            $GLOBALS['TCA'][$table]['columns'] = $tcaBackup;
        }
    }
}
