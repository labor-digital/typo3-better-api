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
 * Last modified: 2021.01.28 at 12:39
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPalette;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPaletteLineBreak;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab;

trait DumperGenericTrait
{

    /**
     * Dumps the basic TCA array from both TcaTable and TcaTableType objects into an array.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable  $table
     *
     * @return array
     * @throws \JsonException
     */
    protected function dumpBasicTca(AbstractTcaTable $table): array
    {
        // Create a clean tca
        $tca            = json_decode(
            json_encode(
                $table->getRaw(), JSON_THROW_ON_ERROR
            ), true, 512, JSON_THROW_ON_ERROR);
        $tca['columns'] = [];

        // Dump the columns
        foreach ($table->getFields() as $field) {
            $fTca = $field->getRaw();
            if (! empty($fTca['config'])) {
                $tca['columns'][$field->getId()] = $fTca;
            }
        }
        unset($fTca);

        // Dump layout
        $this->dumpShowItemStringAndPalettes($tca, $table);

        // Dump data hooks
        $handlers = $table->getRegisteredDataHooks();
        // @todo does this work?
        if (! empty($handlers)) {
            $raw[DataHookTypes::TCA_DATA_HOOK_KEY] = $handlers['@table'];
        }

        return $tca;
    }

    /**
     * Iterates all elements in the TCA object and converts them into their showitem layout string.
     *
     * @param   array                                                            $tca
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable  $table
     */
    protected function dumpShowItemStringAndPalettes(array &$tca, AbstractTcaTable $table): void
    {
        $showItem        = [];
        $palettes        = [];
        $currentPalette  = null;
        $paletteShowItem = [];
        $pointer         = &$showItem;

        foreach ($table->getAllChildren() as $child) {
            if ($child instanceof TcaTab) {
                $meta      = $child->getLayoutMeta();
                $meta[0]   = $child->getLabel();
                $pointer[] = '--div--;' . implode(';', $meta);

                continue;
            }

            if ($child instanceof TcaPalette) {
                $meta      = $child->getLayoutMeta();
                $meta[0]   = $child->hasLabel() ? $child->getLabel() : '';
                $meta[1]   = $currentPalette = substr($child->getId(), 1);
                $pointer[] = '--palette--;' . implode(';', $meta);

                $paletteShowItem = [];
                $pointer         = &$paletteShowItem;

                continue;
            }

            // This marks the end of a container/palette
            if ($child === null) {
                $palettes[$currentPalette]['showitem'] = implode(',', $paletteShowItem);
                $currentPalette                        = null;
                $pointer                               = &$showItem;
                continue;
            }

            if ($child instanceof TcaPaletteLineBreak) {
                $pointer[] = '--linebreak--';

                continue;
            }

            if ($child instanceof TcaField) {
                $meta      = $child->getLayoutMeta();
                $meta[0]   = $child->getId();
                $meta[1]   = $child->hasLabel() ? $child->getLabel() : '';
                $pointer[] = rtrim(implode(';', $meta), ';');
            }
        }

        $tca['types'][$table->getTypeName()]['showitem'] = implode(',', $showItem);
        $tca['palettes']                                 = $palettes;
    }
}
