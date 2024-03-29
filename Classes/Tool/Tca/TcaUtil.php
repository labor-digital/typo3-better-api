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
 * Last modified: 2021.07.25 at 20:46
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Throwable;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\ItemProcessingService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaUtil implements NoDiInterface
{
    /**
     * Contains the tca columns for the last 20 requested types to save performance
     *
     * @var array
     */
    protected static $resolvedTypeTca = [];
    
    /**
     * Helper to apply "columnOverrides" to either whole TCA or the list of columns
     *
     * @param   array  $tca        The whole TCA of a table, or a list of columns
     * @param   array  $overrides  The list of "columnsOverrides" or a TCA type definition array
     *
     * @return array
     */
    public static function applyColumnOverrides(array $tca, array $overrides): array
    {
        if (isset($overrides['columnsOverrides'])) {
            $overrides = $overrides['columnsOverrides'];
        }
        
        $hasColumns = is_array($tca['columns'] ?? null);
        
        $columns = $hasColumns ? $tca['columns'] : $tca;
        ArrayUtility::mergeRecursiveWithOverrule($columns, $overrides);
        
        if ($hasColumns) {
            $tca['columns'] = $columns;
            
            return $tca;
        }
        
        return $columns;
    }
    
    /**
     * Resolves the "type" value of a specific record row
     *
     * @param   array         $row    The database row to resolve the correct tca for
     * @param   string|mixed  $table  The name of the database table to resolve the tca for
     *
     * @return string
     * @see NamingUtil::resolveTableName() on allowed options for the $table parameter
     */
    public static function getRecordType(array $row, $table): string
    {
        $tableName = NamingUtil::resolveTableName($table);
        
        try {
            // This is a bugfix, because sometimes we might encounter an array where we would
            // normally expect a value. e.g. CType in the list label renderer... I don't know why this happens
            // but this will fix the issue
            $rowPrepared = array_map(static function ($v) {
                // The fields in question are always an array of a single element with a numeric index
                if (is_array($v) && isset($v[0]) && count($v) === 1) {
                    return reset($v);
                }
                
                return $v;
            }, $row);
            
            return BackendUtility::getTCAtypeValue($tableName, $rowPrepared);
        } catch (Throwable $e) {
            // Forcefully reset all tca fields that contain arrays
            return BackendUtility::getTCAtypeValue($table, array_map(static function ($v) {
                return is_array($v) ? reset($v) : $v;
            }, $row));
        }
    }
    
    /**
     * Runs the given callback where the tca type overrides are applied to the global tca array.
     * This allows us to render the correct labels even if we have overrides
     *
     * @param   array         $row       The database row to resolve the correct tca for
     * @param   string|mixed  $table     The table-name, table-class or repository-class that defines the table of $row
     * @param   callable      $callback  The callback to execute. The callback receives the modified table TCA as parameter
     *
     * @return mixed Returns the result of $callback()
     * @see NamingUtil::resolveTableName() on allowed options for the $table parameter
     */
    public static function runWithResolvedTypeTca(array $row, $table, callable $callback)
    {
        $tableName = NamingUtil::resolveTableName($table);
        $rowType = static::getRecordType($row, $tableName);
        
        $tcaBackup = $GLOBALS['TCA'][$tableName]['columns'] ?? [];
        $key = $tableName . '_' . $rowType . '_' . md5(implode('-', array_keys($tcaBackup)));
        
        try {
            if (! empty($rowType)) {
                if (isset(static::$resolvedTypeTca[$key])) {
                    $GLOBALS['TCA'][$tableName]['columns'] = static::$resolvedTypeTca[$key];
                    
                    // Move the entry to the bottom -> Keep it longer in our short time memory
                    unset(static::$resolvedTypeTca[$key]);
                } else {
                    $typeTca = $GLOBALS['TCA'][$tableName]['types'][$rowType] ?? [];
                    
                    if (is_array($typeTca['columnsOverrides'] ?? null)) {
                        $GLOBALS['TCA'][$tableName]['columns'] = static::applyColumnOverrides($tcaBackup, $typeTca);
                    }
                    
                    // Only keep the last 20 results -> Save a bit of memory here...
                    if (count(static::$resolvedTypeTca) > 20) {
                        array_shift(static::$resolvedTypeTca);
                    }
                }
                
                static::$resolvedTypeTca[$key] = $GLOBALS['TCA'][$tableName]['columns'];
            }
            
            return $callback($GLOBALS['TCA'][$tableName]);
        } finally {
            $GLOBALS['TCA'][$tableName]['columns'] = $tcaBackup;
        }
    }
    
    /**
     * Generates the full list of all possible items in a field, based on the configured itemProcFunc and injects it
     * into the TCA, as if they were configured manually. It executed $callback and restores the TCA back to the original state.
     *
     * @param   array         $row        The database row to resolve the items for
     * @param   string|mixed  $table      The table-name, table-class or repository-class that defines the table of $row
     * @param   string        $fieldName  The name of the TCA field for which the items should be resolved
     * @param   callable      $callback   The callback to execute. The callback receives
     *
     * @return mixed Returns the result of $callback()
     * @see NamingUtil::resolveTableName() on allowed options for the $table parameter
     */
    public static function runWithResolvedItemProcFunc(array $row, $table, string $fieldName, callable $callback)
    {
        $tableName = NamingUtil::resolveTableName($table);
        try {
            if (is_array($GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? [])) {
                $fieldTca = $GLOBALS['TCA'][$tableName]['columns'][$fieldName];
                
                if (! empty($fieldTca['config']['itemsProcFunc'])) {
                    $tcaBackup = $fieldTca;
                    $config = $fieldTca['config'] ?? [];
                    $items = $config['items'] ?? [];
                    $items = GeneralUtility::makeInstance(ItemProcessingService::class)->getProcessingItems(
                        $tableName,
                        0,
                        $fieldName,
                        $row,
                        $config,
                        $items
                    );
                    $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config']['items'] = $items;
                }
            }
            
            return $callback($GLOBALS['TCA'][$tableName]['columns'][$fieldName] ?? []);
        } finally {
            if (isset($tcaBackup)) {
                $GLOBALS['TCA'][$tableName]['columns'][$fieldName] = $tcaBackup;
            }
        }
    }
    
    /**
     * Helper to extract a value from a given row, which handles all possible oddities of TYPO3
     *
     * @param   array   $row  The database row to extract the value from
     * @param   string  $key  The column name that should be extracted
     *
     * @return int|string|array
     */
    public static function getRowValue(array $row, string $key)
    {
        $value = $row[$key] ?? '';
        
        if (is_array($value)) {
            $value = reset($value);
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        if (! is_string($value) && ! is_numeric($value)) {
            $value = '';
        }
        
        return $value;
    }
    
    /**
     * Helper to extract the language value of a given database row.
     *
     * @param   array         $row    The row to extract the language value from.
     * @param   string|mixed  $table  The name of the database table to resolve the tca for
     *
     * @return int|null Returns either the language id, or null if the language field was not
     *                  present in the row, or did not contain a numeric value
     */
    public static function getLanguageUid(array $row, $table): ?int
    {
        $tableName = NamingUtil::resolveTableName($table);
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] ?? 'sys_language_uid';
        $val = static::getRowValue($row, $languageField);
        if (! is_numeric($val)) {
            return null;
        }
        
        return (int)$val;
    }
}
