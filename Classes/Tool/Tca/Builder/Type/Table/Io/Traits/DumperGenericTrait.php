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


use LaborDigital\T3ba\Event\Tca\TableDumperTypeFilterEvent;
use LaborDigital\T3ba\Tool\DataHook\DataHookTypes;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaPalette;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaPaletteLineBreak;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTab;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;

trait DumperGenericTrait
{
    
    /**
     * Dumps the root tca array based on the originally loaded tca as well as the data of the default type
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return array
     */
    protected function dumpRootTca(TcaTable $table): array
    {
        $tca = SerializerUtil::unserializeJson(
            SerializerUtil::serializeJson($table->getRaw(true))
        );
        $tca[DataHookTypes::TCA_DATA_HOOK_KEY] = $table->getRegisteredDataHooks();
        
        $defaultType = $table->getType();
        $defaultTypeTca = $this->dumpTcaTypeVariant($defaultType);
        
        foreach (['columns', 'palettes', 'types'] as $list) {
            foreach ($defaultTypeTca[$list] ?? [] as $k => $v) {
                $tca[$list][$k] = $v;
            }
        }
        
        return $tca;
    }
    
    /**
     * Dumps a slim tca object with the data of a specific type
     *
     * @param   TcaTableType  $type
     *
     * @return array
     */
    protected function dumpTcaTypeVariant(TcaTableType $type): array
    {
        // Create a clean tca
        $tca = [
            'columns' => [],
            'palettes' => [],
            'types' => [],
        ];
        
        // Dump the columns
        foreach ($type->getFields() as $field) {
            $fTca = $field->getRaw();
            if (! empty($fTca['config'])) {
                $tca['columns'][$field->getId()] = $fTca;
            }
        }
        unset($fTca);
        
        // Dump layout
        $this->dumpTypeShowItemAndPalettes($tca, $type);
        
        // Allow filtering
        $this->eventBus->dispatch($e = new TableDumperTypeFilterEvent(
            $tca, $type, $type->getParent()
        ));
        
        return $e->getTypeTca();
    }
    
    /**
     * Iterates all elements in the TCA object and converts them into their showitem layout string.
     *
     * @param   array         $tca
     * @param   TcaTableType  $type
     */
    protected function dumpTypeShowItemAndPalettes(array &$tca, TcaTableType $type): void
    {
        $showItem = [];
        $palettes = [];
        $currentPalette = null;
        $paletteShowItem = [];
        $pointer = &$showItem;
        $hasFieldsOrPalettes = false;
        
        foreach ($type->getAllChildren() as $child) {
            if ($child instanceof TcaTab) {
                $meta = $child->getLayoutMeta();
                
                // Special handling if the first tab is untitled -> don't print it to enforce the TYPO3 defaults
                if (empty($showItem) && $child->getLabel() === 't3ba.tab.untitled') {
                    continue;
                }
                
                $meta[0] = $child->getLabel();
                $pointer[] = '--div--;' . implode(';', $meta);
                
                continue;
            }
            
            if ($child instanceof TcaPalette) {
                $hasFieldsOrPalettes = true;
                $meta = $child->getLayoutMeta();
                // The ORDER of the items in the meta array is absolutely critical,
                // so note to self: PLEASE DON'T delete this again!
                $pointer[] = '--palette--;' . implode(';', [
                        $meta[0] ?? '',
                        $meta[1] ?? substr($child->getId(), 1),
                    ]);
                
                $currentPalette = $meta[1];
                $palettes[$currentPalette] = $child->getRaw();
                
                $paletteShowItem = [];
                $pointer = &$paletteShowItem;
                
                continue;
            }
            
            // This marks the end of a container/palette
            if ($child === null) {
                $palettes[$currentPalette]['showitem']
                    = empty($paletteShowItem) ? null : implode(',', $paletteShowItem);
                $currentPalette = null;
                $pointer = &$showItem;
                continue;
            }
            
            if ($child instanceof TcaPaletteLineBreak) {
                $pointer[] = '--linebreak--';
                
                continue;
            }
            
            if ($child instanceof TcaField) {
                $hasFieldsOrPalettes = true;
                $fieldId = $child->getId();
                $meta = $child->getLayoutMeta();
                
                // If meta.1 (the label) is the same as the one configured -> don't define it in the string
                if (! empty($meta[1]) && $meta[1] === $tca['columns'][$fieldId]['label']) {
                    unset($meta[1]);
                }
                
                $meta[0] = $fieldId;
                ksort($meta);
                $pointer[] = rtrim(implode(';', $meta), ';');
                unset($fieldId);
            }
        }
        
        if (! empty($showItem) && $hasFieldsOrPalettes) {
            $tca['types'][$type->getTypeName()]['showitem'] = implode(',', $showItem);
        }
        $tca['palettes'] = $palettes;
    }
}
