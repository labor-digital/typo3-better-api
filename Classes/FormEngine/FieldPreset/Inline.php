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
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use Neunerlei\Options\Options;

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
     *                                        - foreignField string (t3ba_inline): The foreign table gets extended
     *                                        by a field that holds the inline parent id. This defines the name
     *                                        of that field.
     *                                        - foreignSortByField string (t3ba_inline_sorting): The foreign table gets
     *                                        extended by a field that holds the sorting order on the inline parent
     *                                        record. This defines the name
     *                                        of that field.
     */
    public function applyInline($foreignTable, array $options = []): void
    {
        $fieldLengthValidator = static function ($v) {
            if (strlen($v) > 64) {
                return 'The configured field is too long, you a field name can have 64 characters at max!';
            }
            
            return true;
        };
        
        $options = Options::make(
            $options,
            $this->addMinMaxItemOptions(
                $this->addEvalOptions(
                    [
                        'foreignField' => [
                            'type' => 'string',
                            'default' => 't3ba_inline',
                            'validator' => $fieldLengthValidator,
                        ],
                        'foreignSortByField' => [
                            'type' => 'string',
                            'default' => 't3ba_inline_sorting',
                            'validator' => $fieldLengthValidator,
                        ],
                    ],
                    ['required']
                )
            )
        );
        
        $foreignTableName = $this->context->getRealTableName($foreignTable);
        
        $config = [
            'type' => 'inline',
            'renderType' => '__UNSET',
            'allowed' => $foreignTableName,
            'foreign_table' => $foreignTableName,
            'foreign_field' => $options['foreignField'],
            'foreign_sortby' => $options['foreignSortByField'],
            'appearance' => [
                'collapseAll' => true,
                'expandSingle' => true,
                'useSortable' => true,
                'showPossibleLocalizationRecords' => true,
                'showRemovedLocalizationRecords' => true,
                'showAllLocalizationLink' => true,
                'showSynchronizationLink' => true,
            ],
        ];
        
        $this->addMinMaxItemConfig($config, $options);
        $this->addEvalConfig($config, $options, ['required']);
        
        // Add columns to foreign table
        $table = $this->context->cs()->sqlRegistry->getTableOverride($foreignTableName);
        if (! $table->hasColumn($options['foreignField'], true)) {
            $table->addColumn($options['foreignField'], 'integer')
                  ->setLength(11);
        }
        if (! $table->hasColumn($options['foreignSortByField'], true)) {
            $table->addColumn($options['foreignSortByField'], 'integer')
                  ->setLength(11);
        }
        
        // Configure column on local table
        $this->configureSqlColumn(static function (Column $column) {
            $column->setType(new IntegerType())
                   ->setLength(11);
        });
        
        $this->field->addConfig($config);
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
     *
     * @see applyInline() if you want to use other records
     */
    public function applyInlineContent(array $options = []): void
    {
        $this->applyInline('tt_content', $options);
    }
    
}
