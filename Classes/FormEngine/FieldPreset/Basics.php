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
use Doctrine\DBAL\Types\StringType;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\DefaultOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\EvalOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InputSizeOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxItemOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxLengthOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\SelectItemsOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\UserFuncOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;

class Basics extends AbstractFieldPreset
{
    /**
     * Configures the field as a "none" type and removes the connected database column.
     * The DataHandler discards values send for type none and never persists or updates them in the database.
     * Type none is the only type that does not necessarily need a database field.
     *
     * @return void
     */
    public function applyNone(): void
    {
        $this->field->addConfig([
            'type' => 'none',
        ]);
        
        if ($this->field instanceof TcaField) {
            $this->field->removeColumn();
        }
    }
    
    /**
     * Configures the field as a passThrough type. Its value, which is sent to the DataHandler is just kept,
     * as is and put into the database field. Default FormEngine however never sends values.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/Passthrough/Index.html
     */
    public function applyPassThrough(): void
    {
        $this->field->addConfig([
            'type' => 'passthrough',
        ]);
    }
    
    /**
     * Converts the field into a checkbox
     *
     * @param   array  $options  Additional options for this preset
     *                           - toggle bool (FALSE): If set to true, this field is rendered as toggle and not as
     *                           checkbox
     *                           - inverted bool (FALSE): If set to true checked / unchecked state are swapped in view:
     *                           A checkbox is marked checked if the database bit is not set and vice versa.
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default bool (FALSE): A default value for your input field
     *                           use the setDefault() method on a field instead
     */
    public function applyCheckbox(array $options = []): void
    {
        $o = $this->initializeOptions([
            'toggle' => [
                'type' => 'bool',
                'default' => false,
            ],
            'inverted' => [
                'type' => 'bool',
                'default' => false,
            ],
            new DefaultOption(false, ['bool']),
        ]);
        
        $options = $o->validate($options);
        
        $this->context->configureSqlColumn(
            static function (Column $column) {
                $column
                    ->setType(new IntegerType())
                    ->setLength(4)
                    ->setDefault(0);
            }
        );
        
        if (! is_bool($this->field->getDefault())) {
            $this->field->setDefault(false);
        }
        
        $this->field->addConfig(
            $o->apply(
                [
                    'type' => 'check',
                    'renderType' => $options['toggle'] ? 'checkboxToggle' : null,
                    'items' => $options['inverted'] ? [[0 => '', 1 => '', 'invertStateDisplay' => true]] : null,
                ]
            )
        );
    }
    
    /**
     * Configures the current input element as a text area optionally with a rte configuration
     *
     * @param   array  $options  Additional options
     *                           - required, trim bool: Any of these values can be passed
     *                           to define their matching "eval" rules
     *                           - maxLength int (65000): The max length of a text (also affects the length of the db
     *                           field)
     *                           - minLength int (0): The min length of an input
     *                           - cols int|string (100%) Defines the width of a field inside its column.
     *                           Can be either an integer from 10-50 or a percentage from 0-100 suffixed by
     *                           the "%" sign, as a string.
     *                           - rows int (5): The height of the rendered field in html rows
     *                           - rte bool (FALSE): If set to true this field will be rendered as RTE editor
     *                           - rteConfig string: For TYPO3 > v7 Can be used to select which rte config is to apply
     *                           to this field
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string: A default value for your input field
     *                           use the setDefault() method on a field instead
     */
    public function applyTextArea(array $options = []): void
    {
        $o = $this->initializeOptions([
            'rows' => [
                'type' => 'int',
                'default' => 5,
            ],
            'rte' => [
                'type' => 'bool',
                'default' => false,
            ],
            'rteConfig' => [
                'type' => 'string',
                'default' => '',
            ],
            new InputSizeOption('cols'),
            new DefaultOption(),
            new MinMaxLengthOption(60000, 0, true),
            new EvalOption(),
        ]);
        
        $options = $o->validate($options);
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'text',
                'rows' => $options['rows'],
                'enableRichtext' => $options['rte'] ? true : null,
                'richtextConfiguration' => $options['rte'] && ! empty($options['rteConfig'])
                    ? $options['rteConfig'] : null,
            ])
        );
    }
    
    /**
     * Sets the current field as a simple select field.
     *
     * @param   array  $items    The items you want to set for this select field, as an array
     *                           with the "value" as key and the "label" as value.
     *
     *                           NOTE: You can prove an array as "label" value to define two special cases.
     *                           The first entry ($label[0]) MUST ALWAYS be the label to be displayed.
     *                           The second entry ($label[1]) can be either one of these:
     *                           A.) A string that provides an icon identifier
     *                           B.) TRUE if you want to create an "option group" with this label as headline.
     * @param   array  $options  Additional options for this preset
     *                           - minItems int (0): The minimum number of items required to be valid
     *                           - maxItems int (1): The maximum number of items allowed in this field
     *                           - required bool: If set this field will be required to be filled
     *                           - userFunc string: Can be given like any select itemProcFunc in TYPO3 as:
     *                           vendor\className->methodName and is used as a filter for the items in the select field
     *
     *                           DEPRECATED: Will be removed in v12
     *                           - default string: A default value for your input field
     *                           use the setDefault() method on a field instead
     */
    public function applySelect(array $items, array $options = []): void
    {
        $o = $this->initializeOptions([
            new UserFuncOption(),
            new SelectItemsOption($items),
            new DefaultOption(null, ['string', 'number', 'null']),
            new MinMaxItemOption(1),
            new EvalOption(['required']),
        ]);
        
        $options = $o->validate($options);
        
        $this->context->configureSqlColumn(
            static function (Column $column) {
                $column->setType(new StringType())
                       ->setLength(1024)
                       ->setDefault('');
            }
        );
        
        $this->field->addConfig(
            $o->apply([
                'type' => 'select',
                'renderType' => $options['maxItems'] <= 1 ? 'selectSingle' : 'selectCheckBox',
                'size' => 1,
            ])
        );
    }
    
    /**
     * Creates a select field that has 9 possible positions from top-left over middle-middle to bottom-right.
     * It can be used to create an image alignment configuration.
     *
     * If you add this field preset to the sys_file_reference table with the field name of "image_alignment",
     * the fal file service will automatically find and return the alignment property when you request
     * file information.
     */
    public function applyImageAlignment(): void
    {
        if (! $this->field->hasLabel()) {
            $this->field->setLabel('t3ba.t.sys_file_reference.imageAlignment');
        }
        
        $this->applySelect(
            [
                'tl' => 't3ba.t.sys_file_reference.imageAlignment.topLeft',
                'tc' => 't3ba.t.sys_file_reference.imageAlignment.topCenter',
                'tr' => 't3ba.t.sys_file_reference.imageAlignment.topRight',
                'cl' => 't3ba.t.sys_file_reference.imageAlignment.centerLeft',
                'cc' => 't3ba.t.sys_file_reference.imageAlignment.centerCenter',
                'cr' => 't3ba.t.sys_file_reference.imageAlignment.centerRight',
                'bl' => 't3ba.t.sys_file_reference.imageAlignment.bottomLeft',
                'bc' => 't3ba.t.sys_file_reference.imageAlignment.bottomCenter',
                'br' => 't3ba.t.sys_file_reference.imageAlignment.bottomRight',
            ],
            ['default' => 'cc']
        );
    }
    
    /**
     * Can be used to apply a callback function on a field.
     * This can become quite handy if you want to configure multiple fields with the same configuration.
     * Use a closure to wrap your field configuration and apply it to each field in your TCA
     *
     * @param   callable  $callable
     */
    public function applyCallback(callable $callable): void
    {
        $callable($this->field, $this->context);
    }
}
