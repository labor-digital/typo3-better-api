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
 * Last modified: 2021.01.27 at 15:10
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits;


use LaborDigital\T3BA\Tool\Tca\Builder\Logic\FormElementContainingInterface;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab;
use LaborDigital\T3BA\Tool\Tca\TcaUtil;
use Neunerlei\Arrays\Arrays;

trait FactoryPopulatorTrait
{

    /**
     * Creates the child instances of the table based on the given type tca
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable  $table
     * @param   array                                                            $typeTca
     *
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
     */
    protected function populateElements(AbstractTcaTable $table, array $typeTca): void
    {
        // Load the columns
        $tca  = $table->getForm()->getInitialConfig();
        $cols = TcaUtil::applyColumnOverrides($tca['columns'] ?? [], $typeTca['columnsOverrides'] ?? []);

        // Load the palettes
        $palettes = Arrays::getPath($tca, 'palettes.*.showitem', []);

        // Load the showitem string
        $showItem = $this->parseShowItemString($typeTca['showitem'] ?? '');

        $tabCounter = 0;
        $target     = null;
        foreach ($showItem as $layoutMeta) {
            $id = reset($layoutMeta);

            // Check for modifiers
            if (strpos($id, '--') === 0) {
                array_shift($layoutMeta);
                switch (strtolower(substr($id, 2, -2))) {
                    case 'div':
                        $target = $this->populateTab($table, $layoutMeta, $tabCounter++);
                        break;
                    case 'palette':
                        $id = end($layoutMeta);

                        // Ignore the field if we don't have a configuration for it
                        // or the palette is already loaded
                        $config = Arrays::getPath($palettes, [$id]);
                        if (empty($config) || $table->hasPalette($id)) {
                            break;
                        }

                        $this->populatePalette(
                            $table,
                            $target,
                            $layoutMeta,
                            $id,
                            $cols,
                            $config
                        );

                        break;
                    case 'linebreak':
                        $table->addLineBreak();
                        break;
                    default:
                        throw new TcaBuilderException(
                            'Invalid special element was given: ' . implode(';', $layoutMeta) . ' is not allowed!');
                }

                continue;
            }

            // If we don't have a target, this is wrong!
            if ($target === null) {
                throw new TcaBuilderException('Invalid TCA! Fields have to be wrapped in a tab or container!');
            }

            // Ignore the field if we don't have a configuration for it
            $config = Arrays::getPath($cols, [$id], []);
            if (empty($config)) {
                continue;
            }

            // Add a new field
            $this->populateField(
                $table,
                $target,
                $layoutMeta,
                $id,
                $config
            );
        }

    }

    /**
     * Breaks up a show item string and returns a machine readable array of parts
     *
     * @param   string  $layout
     *
     * @return array
     */
    protected function parseShowItemString(string $layout): array
    {
        $parts = array_filter(array_map('trim', explode(',', $layout)));
        foreach ($parts as $k => $part) {
            if (strpos($part, ';') !== false) {
                $parts[$k] = array_map('trim', explode(';', $part));
            } else {
                $parts[$k] = [$part];
            }
        }

        return $parts;
    }

    /**
     * Creates a new tab instance in the table object
     *
     * @param   AbstractTcaTable  $table
     * @param   array             $layoutMeta
     * @param   int               $id
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab
     */
    protected function populateTab(AbstractTcaTable $table, array $layoutMeta, int $id): TcaTab
    {
        $i = $table->getTab($id);
        $i->setLayoutMeta($layoutMeta);
        if (! empty($layoutMeta[0])) {
            $i->setLabel($layoutMeta[0]);
        }

        return $i;
    }

    /**
     * Creates and populates a new palette / container instance in the table object
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable           $table
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Logic\FormElementContainingInterface  $target
     * @param   array                                                                     $layoutMeta
     * @param   string                                                                    $id
     * @param   array                                                                     $cols
     * @param   string                                                                    $config
     */
    protected function populatePalette(
        AbstractTcaTable $table,
        FormElementContainingInterface $target,
        array $layoutMeta,
        string $id,
        array $cols,
        string $config
    ): void {
        $target->addMultiple(function () use ($table, $id, $layoutMeta, $cols, $config) {
            $i = $table->getPalette($id);
            $i->setLayoutMeta($layoutMeta);

            if (! empty($layoutMeta[1])) {
                $i->setLabel($layoutMeta[1]);
            }

            foreach ($this->parseShowItemString($config) as $_layoutMeta) {
                $_id = reset($_layoutMeta);

                // Handle non-configured fields
                if (! $cols[$_id]) {
                    // Handle line breaks
                    if ($_id === '--linebreak--') {
                        $i->addMultiple(static function () use ($table) {
                            $table->addLineBreak();
                        });
                    }

                    continue;
                }

                // Populate the field
                $this->populateField(
                    $table,
                    $i,
                    $_layoutMeta,
                    $_id,
                    $cols[$_id]
                );
            }
        });
    }

    /**
     * Internal helper to create a new field in the table instance with the inherited config applied to it.
     *
     * @param   AbstractTcaTable                $table
     * @param   FormElementContainingInterface  $target
     * @param   array                           $layoutMeta
     * @param   string                          $id
     * @param   array                           $config
     */
    protected function populateField(
        AbstractTcaTable $table,
        FormElementContainingInterface $target,
        array $layoutMeta,
        string $id,
        array $config
    ): void {
        $target->addMultiple(static function () use ($table, $id, $layoutMeta, $config) {
            $i = $table->getField($id);
            $i->setLayoutMeta($layoutMeta);
            $i->setRaw($config);

            if (! empty($layoutMeta[1])) {
                $i->setLabel($layoutMeta[1]);
            }
        });
    }
}
