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


namespace LaborDigital\T3ba\FormEngine\FieldPreset;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\IntegerType;
use LaborDigital\T3ba\FormEngine\UserFunc\InlineColPosHook;
use LaborDigital\T3ba\T3baFeatureToggles;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\EvalOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InlineAppearanceOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InlineForeignFieldOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxItemOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;

class Inline extends AbstractFieldPreset
{
    
    /**
     * This sets your field to be rendered as inline relational record field (IRRE).
     *
     * @param   string|object  $foreignTable  The name of the foreign table to use as entries
     * @param   array          $options       Additional options for the relation
     *                                        - minItems int (0): The minimum number of items required to be valid
     *                                        - maxItems int: The maximum number of items allowed in this field
     *                                        - required bool (FALSE): If set to true, the field requires at least 1
     *                                        item. This is identical with setting minItems to 1
     *                                        - allOpen bool (FALSE): If set to true, all inline elements will
     *                                        be expanded by default.
     *                                        - openMultiple bool (FALSE): If set to true, multiple inline elements can
     *                                        be expanded at a time.
     *                                        - noSorting bool (FALSE): If set to true, the "sort" options will be disabled
     *                                        - noDelete bool (FALSE): If set to true, the "delete" option will be disabled
     *                                        - noHide bool (FALSE): If set to true, the "visibility" option will be disabled
     *                                        - noInfo bool (FALSE): If set to true, the "info" option will be disabled
     *                                        - foreignField string (t3ba_inline): The foreign table gets extended
     *                                        by a field that holds the inline parent id. This defines the name
     *                                        of that field.
     *                                        - foreignSortByField string (t3ba_inline_sorting): The foreign table gets
     *                                        extended by a field that holds the sorting order on the inline parent
     *                                        record. This defines the name
     *                                        of that field.
     *
     *                                        Only available if the {@see T3baFeatureToggles::TCA_V11_INLINE_RELATIONS}
     *                                        feature toggle is enabled:
     *
     *                                        - foreignTableNameField string (t3ba_inline_table): The foreign table gets
     *                                        extended by a field that holds the parent table name, to determine the name
     *                                        of the parent table. This defines the name of that field.
     *                                        - foreignSortByField string (t3ba_inline_sorting): The foreign table gets
     *                                        extended by a field that holds the sorting order on the inline parent
     *                                        record. This defines the name of that field.
     */
    public function applyInline($foreignTable, array $options = []): void
    {
        if ($this->context->isInFlexFormSection()) {
            throw new TcaBuilderException(
                'You can\'t create an inline relation on field: '
                . $this->field->getId() . ' because they are not allowed in flex form sections!');
        }
        
        $o = $this->initializeOptions([
            new EvalOption(['required']),
            new MinMaxItemOption(),
            new InlineForeignFieldOption($foreignTable),
            new InlineAppearanceOption(),
        ]);
        
        $this->context->configureSqlColumn(
            static function (Column $column) {
                $column->setType(new IntegerType())
                       ->setDefault(0)
                       ->setLength(11);
            }
        );
        
        $o->validate($options);
        
        $this->field->addConfig(
            $o->apply([
                    'type' => 'inline',
                    'renderType' => '__UNSET',
                    'appearance' => [
                        'showPossibleLocalizationRecords' => true,
                        'showRemovedLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                    ],
                ]
            )
        );
        
    }
    
    /**
     * This sets your field to be a list of content elements using IRRE.
     * This is a wrapper of applyInline() with the tt_content table already preconfigured
     *
     * @param   array  $options               Additional options for the relation
     *                                        - minItems int (0): The minimum number of items required to be valid
     *                                        - maxItems int: The maximum number of items allowed in this field
     *                                        - required bool (FALSE): If set to true, the field requires at least 1
     *                                        item. This is identical with setting minItems to 1
     *                                        - foreignField string (t3ba_inline): The foreign table gets extended
     *                                        by a field that holds the inline parent id. This defines the name
     *                                        of that field.
     *                                        - foreignSortByField string (t3ba_inline_sorting): The foreign table gets
     *                                        extended by a field that holds the sorting order on the inline parent
     *                                        record. This defines the name
     *                                        of that field.
     *                                        - defaultCType string: Allows you to define the default CType value
     *                                        - defaultListType string: Allows you to define the default list type,
     *                                        using this option will disable defaultCType
     *                                        - newCeWizard bool: By default the element will replace the "create new"
     *                                        button with the a "new content element wizard" that opens up in a modal.
     *                                        To disable this feature set this option to FALSE.
     *                                        Note: The wizard will be disabled if either "defaultCType" or
     *                                        "defaultListType" is used.
     *
     * @see applyInline() if you want to use other records
     */
    public function applyInlineContent(array $options = []): void
    {
        $defaultCType = $options['defaultCType'] ?? null;
        $defaultListType = $options['defaultListType'] ?? null;
        $useNewCeWizard = ! ($defaultCType !== null || $defaultListType !== null) && ($options['newCeWizard'] ?? true);
        unset($options['defaultCType'], $options['defaultListType'], $options['newCeWizard']);
        
        $this->applyInline('tt_content', $options);
        
        // Apply default values for CType or list_type
        if ($defaultCType || $defaultListType) {
            if ($defaultListType) {
                $this->field->addConfig([
                    'overrideChildTca' => [
                        'columns' => [
                            'CType' => [
                                'config' => [
                                    'default' => 'list',
                                ],
                            ],
                            'list_type' => [
                                'config' => [
                                    'default' => $defaultListType,
                                ],
                            ],
                        ],
                    ],
                ]);
            } else {
                $this->field->addConfig([
                    'overrideChildTca' => [
                        'columns' => [
                            'CType' => [
                                'config' => [
                                    'default' => $defaultCType,
                                ],
                            ],
                        ],
                    ],
                ]);
            }
        }
        
        // When we are creating an inline content relation on the tt_content table,
        // we automatically inject an item proc func to the colPos column, so we can
        // hide the contents on the "page" view.
        $this->field->addConfig([
            'overrideChildTca' => [
                'columns' => [
                    'colPos' => [
                        'config' => [
                            'itemsProcFunc' => InlineColPosHook::class . '->itemsProcFunc',
                            'default' => '-88',
                        ],
                    ],
                ],
            ],
        ]);
        
        // Add the extended render type if the new content element wizard should be used
        if ($useNewCeWizard) {
            $this->field->addConfig([
                'renderType' => 't3baInlineWithNewCeWizard',
            ]);
        }
    }
    
}
