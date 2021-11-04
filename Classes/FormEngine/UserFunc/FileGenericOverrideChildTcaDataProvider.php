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
 * Last modified: 2021.10.26 at 17:22
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Resource\File;

class FileGenericOverrideChildTcaDataProvider implements FormDataProviderInterface
{
    public const CONFIG_KEY = 't3baOverrideChildShowItem';
    public const DISABLE_FAL_FIELDS_KEY = 't3baOverrideChildDisableCols';
    
    protected static $preparedShowItemList;
    
    /**
     * @inheritDoc
     */
    public function addData(array $result): array
    {
        if (! is_array($result['processedTca']['columns'] ?? null)) {
            return $result;
        }
        
        TcaUtil::runWithResolvedTypeTca($result['databaseRow'], $result['tableName'],
            function () use (&$result) {
                $tca = $GLOBALS['TCA'][$result['tableName']];
                foreach ($tca['columns'] as $key => $col) {
                    if (! isset($result['processedTca']['columns'][$key])) {
                        continue;
                    }
                    
                    if (($col['config'][static::CONFIG_KEY] ?? null) === true) {
                        $this->injectIntoTca($result['processedTca']['columns'][$key]);
                    }
                    
                    if (($col['config']['type'] ?? null) === 'flex'
                        && is_array($result['processedTca']['columns'][$key]['config']['ds'] ?? null)) {
                        $this->injectIntoFlexConfig(
                            $result['processedTca']['columns'][$key]['config']['ds']);
                    }
                }
            }
        );
        
        return $result;
    }
    
    /**
     * Injects the prepared showItem strings into the override child tca if it is not already set.
     *
     * @param   array  $tca
     */
    protected function injectIntoTca(array &$tca): void
    {
        if (! isset($tca['config']['overrideChildTca']['types'])) {
            $tca['config']['overrideChildTca']['types'] = [];
        }
        
        if (! empty($tca['config'][static::DISABLE_FAL_FIELDS_KEY])) {
            if ($tca['config'][static::DISABLE_FAL_FIELDS_KEY] === true) {
                // Nothing should be shown -> set to filePalette and skip the other stuff
                foreach ($this->getShowItemList() as $type => $showItem) {
                    $tca['config']['overrideChildTca']['types'][$type]['showitem'] = '--palette--;;filePalette';
                }
                
                return;
            }
            
            if (is_array($tca['config'][static::DISABLE_FAL_FIELDS_KEY])) {
                foreach ($tca['config'][static::DISABLE_FAL_FIELDS_KEY] as $field) {
                    if (! is_string($field)) {
                        continue;
                    }
                    
                    $tca['config']['overrideChildTca']['columns'][$field]['config']['type'] = 'passthrough';
                    $tca['config']['overrideChildTca']['columns'][$field]['config']['renderType'] = 'passthrough';
                }
            }
        }
        
        foreach ($this->getShowItemList() as $type => $showItem) {
            $tca['config']['overrideChildTca']['types'][$type]['showitem'] = $showItem;
        }
    }
    
    /**
     * Iterates the flex data structure and updates the child override in the same way we modify the processed tca
     *
     * @param   array  $flexConfig
     */
    protected function injectIntoFlexConfig(array &$flexConfig): void
    {
        if (! is_array($flexConfig['sheets'] ?? null)) {
            return;
        }
        
        foreach ($flexConfig['sheets'] as &$sheet) {
            if (! isset($sheet['ROOT']['el'])) {
                continue;
            }
            foreach ($sheet['ROOT']['el'] as &$field) {
                // Inline FAL fields are not supported in sections -> so we can skip this
                if ($field['section'] ?? false) {
                    continue;
                }
                
                if ((int)(string)($field['config'][static::CONFIG_KEY] ?? null) === 1) {
                    $this->injectIntoTca($field);
                }
            }
        }
    }
    
    /**
     * Generates the actual show item list based on the TCA of the sys_file_reference table
     *
     * @return array
     */
    protected function getShowItemList(): array
    {
        if (isset(static::$preparedShowItemList)) {
            return static::$preparedShowItemList;
        }
        
        $types = Arrays::getList($GLOBALS['TCA']['sys_file_reference']['types'] ?? [], 'showitem') ?? [];
        
        $showItemList = [];
        
        foreach (
            [
                File::FILETYPE_UNKNOWN,
                File::FILETYPE_TEXT,
                File::FILETYPE_IMAGE,
                File::FILETYPE_AUDIO,
                File::FILETYPE_VIDEO,
                File::FILETYPE_APPLICATION,
            ] as $type
        ) {
            if (isset($types[$type])) {
                continue;
            }
            $types[$type] = '--palette--;;basicoverlayPalette,--palette--;;filePalette';
        }
        
        foreach ($types as $type => $showItem) {
            $changedPalette = 'imageoverlayPalette';
            
            if ($type === File::FILETYPE_VIDEO) {
                $changedPalette = 'videoOverlayPalette';
            } elseif ($type === File::FILETYPE_AUDIO) {
                $changedPalette = 'audioOverlayPalette';
            }
            
            $showItemList[$type] = str_replace(';basicoverlayPalette', ';' . $changedPalette, $showItem);
        }
        
        return static::$preparedShowItemList = $showItemList;
    }
}