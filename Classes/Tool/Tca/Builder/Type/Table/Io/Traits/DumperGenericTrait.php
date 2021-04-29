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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Event\Tca\TableDumperTypeFilterEvent;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPalette;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPaletteLineBreak;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

trait DumperGenericTrait
{
    
    /**
     * Dumps the root tca array based on the originally loaded tca as well as the data of the default type
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return array
     */
    protected function dumpRootTca(TcaTable $table): array
    {
        /** @noinspection UnserializeExploitsInspection */
        $tca = unserialize(serialize($table->getRaw(true)));
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
     * Dumps the a slim tca object with the data of a specific type
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
        $hasFieldsOrPallets = false;
        
        foreach ($type->getAllChildren() as $child) {
            if ($child instanceof TcaTab) {
                $meta = $child->getLayoutMeta();
                $meta[0] = $child->getLabel();
                $pointer[] = '--div--;' . implode(';', $meta);
                
                continue;
            }
            
            if ($child instanceof TcaPalette) {
                $hasFieldsOrPallets = true;
                $meta = $child->getLayoutMeta();
                $meta[0] = $child->hasLabel() ? $child->getLabel() : '';
                $meta[1] = $currentPalette = substr($child->getId(), 1);
                $pointer[] = '--palette--;' . implode(';', $meta);
                
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
                $hasFieldsOrPallets = true;
                $meta = $child->getLayoutMeta();
                $meta[0] = $child->getId();
                $meta[1] = $child->hasLabel() ? $child->getLabel() : '';
                $pointer[] = rtrim(implode(';', $meta), ';');
            }
        }
        
        if (! empty($showItem) && $hasFieldsOrPallets) {
            $tca['types'][$type->getTypeName()]['showitem'] = implode(',', $showItem);
        }
        $tca['palettes'] = $palettes;
    }
}
