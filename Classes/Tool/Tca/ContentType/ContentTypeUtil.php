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
 * Last modified: 2021.04.26 at 10:46
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\ContentType;


use LaborDigital\T3BA\Tool\Tca\ContentType\Domain\DefaultDataModel;

class ContentTypeUtil
{
    /**
     * Returns the mapping between a cType and its extension table
     *
     * @return array
     */
    public static function getTableMap(): array
    {
        return $GLOBALS['TCA']['tt_content']['ctrl']['contentType']['tables'] ?? [];
    }

    /**
     * Returns the mapping of all extension fields and their given field name
     *
     * @return array
     */
    public static function getColumnMap(): array
    {
        return $GLOBALS['TCA']['tt_content']['ctrl']['contentType']['columns'] ?? [];
    }

    /**
     * Returns the mapping of the extension fields to their given name, but only on a requested cType
     *
     * @param   string  $cType
     *
     * @return array
     */
    public static function getTypeColumnMap(string $cType): array
    {
        return $GLOBALS['TCA']['tt_content']['ctrl']['contentType']['typeColumns'][$cType] ?? [];
    }

    /**
     * Returns the configured model class for the given cType, or the default model class name,
     * if no concrete model class was registered for the type.
     *
     * @param   string  $cType
     *
     * @return string
     */
    public static function getModelClass(string $cType): string
    {
        return $GLOBALS['TCA']['tt_content']['ctrl']['contentType']['typeModels'][$cType] ?? DefaultDataModel::class;
    }

    /**
     * Retrieves the raw child row and re-maps the registered columns to their given column names.
     * The returned array then can be directly merged into the parent row
     *
     * @param   array   $childRow
     * @param   string  $cType
     *
     * @return array
     */
    public static function convertChildForParent(array $childRow, string $cType): array
    {
        $map     = static::getTypeColumnMap($cType);
        $columns = [];
        foreach (array_flip($map) as $childColumn => $parentColumn) {
            $columns[$parentColumn] = $childRow[$childColumn] ?? null;
        }

        return $columns;
    }

    /**
     * Receives the parent row, that includes the extension columns in their named space form.
     * It will extract all extension columns and return them in a remapped form ready to be inserted
     * into the extension table.
     *
     * @param   array   $row
     * @param   string  $cType
     *
     * @return array
     */
    public static function extractChildFromParent(array &$row, string $cType): array
    {
        $map = static::getTypeColumnMap($cType);
        if (empty($map)) {
            return [];
        }

        $childRow = [];
        foreach ($map as $parentColumn => $childColumn) {
            if (isset($row[$parentColumn])) {
                $childRow[$childColumn] = $row[$parentColumn];
            }
            unset($row[$parentColumn]);
        }

        return $childRow;
    }

    /**
     * Returns true if an extension table was registered for the ctype
     *
     * @param   string  $cType
     *
     * @return bool
     */
    public static function hasExtensionTable(string $cType): bool
    {
        return isset(static::getTableMap()[$cType]);
    }

    /**
     * Removes all registered extension column values from the given row
     *
     * @param   array  $row
     *
     * @return array
     */
    public static function removeAllExtensionColumns(array $row): array
    {
        $clean            = [];
        $extensionColumns = static::getColumnMap();
        foreach ($row as $k => $v) {
            if (isset($extensionColumns[$k])) {
                continue;
            }
            $clean[$k] = $v;
        }

        return $clean;
    }

    /**
     * For the most part the content type columns work out of the box, however when resolving file references in the
     * backend, or creating extbase objects there are issues where typo3 expects the "mapped" column names to exist in
     * the tca. To provide a polyfill this helper will rewrite the extension columns of tt_content to their given
     * column name so TYPO3 can resolve the columns without problems.
     *
     * @param   string    $cType
     * @param   callable  $wrapper
     *
     * @return mixed
     */
    public static function runWithRemappedTca($rowOrCType, callable $wrapper)
    {
        if (is_string($rowOrCType)) {
            $cType = $rowOrCType;
        } elseif (is_array($rowOrCType)) {
            $cType = $rowOrCType['CType'] ?? '';
        } else {
            $cType = '';
        }

        if (! static::hasExtensionTable($cType)) {
            return $wrapper();
        }

        $columnBackup = $GLOBALS['TCA']['tt_content']['columns'] ?? [];

        try {
            foreach (static::getTypeColumnMap($cType) as $nsColumnName => $columnName) {
                $GLOBALS['TCA']['tt_content']['columns'][$columnName]
                    = $GLOBALS['TCA']['tt_content']['columns'][$nsColumnName] ?? [];
            }

            return $wrapper();
        } finally {
            $GLOBALS['TCA']['tt_content']['columns'] = $columnBackup;
        }
    }

    /**
     * Retrieves a row, that contains the main record, as well as the child fields,
     * and remaps the existing extension fields from their namespaced name like ct_cType_... to the
     * name that was given in the form.
     *
     * @param   array   $row    The row to resolve the the virtual columns on
     * @param   string  $cType  The cType of content element to resolve the columns for
     *
     * @return array
     */
    public static function remapColumns(array $row, string $cType): array
    {
        if (! static::hasExtensionTable($cType)) {
            return $row;
        }

        foreach (static::getTypeColumnMap($cType) as $nsColumnName => $columnName) {
            if (array_key_exists($nsColumnName, $row)) {
                $row[$columnName] = $row[$nsColumnName];
                unset($row[$nsColumnName]);
            }
        }

        return $row;
    }
}
