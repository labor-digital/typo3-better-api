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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

trait DumperTypeGeneratorTrait
{
    
    /**
     * Generates an array containing the differences between two arrays and returns it.
     *
     * @param   array  $a
     * @param   array  $b
     * @param   array  $unsetIgnoreKeys  Internal to define a list of properties that should not be set to __UNSET
     *                                   even if they don't exist in $a
     *
     * @return array
     */
    protected function makeArrayDiff(array $a, array $b, array $unsetIgnoreKeys = []): array
    {
        $diff = [];
        foreach ($b as $k => $v) {
            if (! isset($a[$k])) {
                $diff[$k] = $v;
                continue;
            }
            
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($a[$k] === $v || (is_numeric($a[$k]) && is_numeric($v) && $a[$k] == $v)) {
                continue;
            }
            
            if (is_array($v) && is_array($a[$k])) {
                $_diff = $this->makeArrayDiff(
                    $a[$k], $v,
                    // This is a fix to prevent the "types" sub-key of overrideChildTca
                    // to be defined as "__UNSET", because TYPO will not resolve those keys
                    // through ArrayUtility, but access the array directly.
                    $k === 'overrideChildTca' ? ['types'] : []
                );
                if (! empty($_diff)) {
                    $diff[$k] = $_diff;
                }
                
                continue;
            }
            
            $diff[$k] = $v;
        }
        
        foreach ($a as $k => $v) {
            if (! isset($b[$k]) && ! in_array($k, $unsetIgnoreKeys, true)) {
                $diff[$k] = '__UNSET';
            }
        }
        
        return $diff;
    }
    
    /**
     * Generates the required column overrides for a specific TCA Type or extends the the main table's columns
     * if new columns have been added in a type
     *
     * @param                                                            $typeName
     * @param   array                                                    $tca
     * @param   array                                                    $typeTca
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return void
     */
    protected function dumpColumnOverrides($typeName, array &$tca, array $typeTca, TcaTable $table): void
    {
        $cols = $tca['columns'] ?? [];
        $typeCols = $typeTca['columns'] ?? [];
        $overrides = [];
        
        foreach ($typeCols as $id => $col) {
            // Column does not exist in parent
            if (! isset($cols[$id])) {
                // Make sure dummy columns (like editlock and co don't get added to the main tca)
                // They are not in the columns array because we don't configure them by default
                // but they are "theoretically" there. So check if we have a field for the id first
                if (! $table->getType()->hasField($id)) {
                    $cols[$id] = $col;
                } else {
                    // Add col completely as override
                    $overrides[$id] = $col;
                }
                continue;
            }
            
            // Check if the column equals the other column
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($cols[$id] == $col) {
                continue;
            }
            
            // Calculate difference
            $diff = $this->makeArrayDiff($cols[$id], $col);
            
            if (empty($diff)) {
                continue;
            }
            
            $overrides[$id] = $diff;
        }
        
        $tca['columns'] = $cols;
        
        if (! empty($overrides)) {
            $tca['types'][$typeName]['columnsOverrides'] = $overrides;
        }
    }
    
    /**
     * Checks if palettes have been changed for a type and therefore have to be replaced with a own, special palette
     * definition to avoid interference between types
     *
     * @param   string|int  $typeName
     * @param   array       $tca
     * @param   array       $typeTca
     */
    protected function dumpTypePalettes($typeName, array &$tca, array $typeTca): void
    {
        $palettes = $tca['palettes'] ?? [];
        $typePalettes = $typeTca['palettes'] ?? [];
        $typeShowitem = $typeTca['types'][$typeName]['showitem'] ?? '';
        
        // Loop over all type palettes
        foreach ($typePalettes as $k => $p) {
            $showitem = $p['showitem'];
            
            // Add new palette OR check if there is already a showitem for this palette
            if (! isset($palettes[$k]) ||
                (isset($palettes[$k]['showitem']) &&
                 $this->assertShowItemEquals($palettes[$k]['showitem'] ?? '', $showitem ?? '', $tca))) {
                $palettes[$k] = $p;
                continue;
            }
            
            // Create a new version of this palette for the type
            $newK = $typeName . '-' . $k;
            $palettes[$newK] = $p;
            $palettes[$newK]['showitem'] = $showitem;
            
            // Update type's show item...
            // Yay for string manipulation \o/...
            $typeShowitem = preg_replace(
                '/(--palette--;[^;,]*;)' . preg_quote((string)$k, '/') . '(,|$)/si',
                '${1}' . $newK . ',',
                $typeShowitem);
        }
        
        if (! empty($typeShowitem)) {
            $tca['types'][$typeName]['showitem'] = $typeShowitem;
        }
        $tca['palettes'] = $palettes;
    }
    
    /**
     * Validates that two showItem strings are the same
     *
     * @param   string  $a    The original show item string
     * @param   string  $b    The new show item string
     * @param   array   $tca  The TCA array of the table the strings apply to
     *
     * @return bool
     */
    protected function assertShowItemEquals(string $a, string $b, array $tca): bool
    {
        $a = trim(str_replace(PHP_EOL, '', $a), ' ,;');
        $b = trim(str_replace(PHP_EOL, '', $b), ' ,;');
        
        if (str_replace(' ', '', $a) === str_replace(' ', '', $b)) {
            return true;
        }
        
        $tokenizer = static function (string $v): array {
            // @todo an "array_filter" could prevent issues where stuff like "field,,otherField" exists?
            return array_map(static function (string $t): array {
                return array_map('trim', explode(';', $t));
            }, explode(',', $v));
        };
        
        $aTokens = $tokenizer($a);
        $bTokens = $tokenizer($b);
        
        if (count($aTokens) !== count($bTokens)) {
            return false;
        }
        
        foreach ($aTokens as $k => $aToken) {
            $bToken = $bTokens[$k];
            if (($aToken[0] ?? null) !== ($bToken[0] ?? null)) {
                return false;
            }
            
            if (str_starts_with($aToken[0] ?? '', '--')) {
                continue;
            }
            
            if (($aToken[1] ?? null) !== ($bToken[1] ?? null)) {
                $aLabel = $aToken[1] ?? $tca['columns'][$aToken[0]]['label'] ?? null;
                $bLabel = $bToken[1] ?? $tca['columns'][$bToken[0]]['label'] ?? null;
                if ($aLabel !== $bLabel) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
}
