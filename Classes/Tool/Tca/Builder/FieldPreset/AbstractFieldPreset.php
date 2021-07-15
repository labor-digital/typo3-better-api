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
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.03.20 at 14:28
 */

namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Sql\SqlFieldLength;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

abstract class AbstractFieldPreset implements FieldPresetInterface
{
    use ContainerAwareTrait;
    
    protected const EVAL_TYPES
        = [
            'required',
            'trim',
            'date',
            'datetime',
            'lower',
            'int',
            'email',
            'password',
            'unique',
            'uniqueInSite',
            'null',
        ];
    
    /**
     * Holds the instance of the form field to configure
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField
     */
    protected $field;
    
    /**
     * The context of the field
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext
     */
    protected $context;
    
    /**
     * @inheritDoc
     */
    public function setField(AbstractField $field): void
    {
        $this->field = $field;
    }
    
    /**
     * @inheritDoc
     */
    public function setContext(TcaBuilderContext $context): void
    {
        $this->context = $context;
    }
    
    /**
     * Internal helper to add the different eval options to the Options::make definition.
     * The default eval types are: "required", "trim", "datetime", "lower", "int", "email", "password"
     *
     * @param   array  $optionDefinition  The option definition to add the eval rules to
     * @param   array  $evalFilter        If given an array of eval types that are whitelisted everything else will not
     *                                    be added as option
     * @param   array  $evalDefaults      Can be used to set the default values for given eval types.
     *                                    setting this to ["trim" => TRUE] will set trim to be true by default,
     *                                    otherwise all eval rules start with a value of FALSE.
     *
     * @return array
     */
    protected function addEvalOptions(array $optionDefinition, array $evalFilter = [], array $evalDefaults = []): array
    {
        foreach (static::EVAL_TYPES as $type) {
            if (empty($evalFilter) || in_array($type, $evalFilter, true)) {
                $optionDefinition[$type] = [
                    'type' => 'bool',
                    'default' => $evalDefaults[$type] ?? false,
                ];
            }
        }
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to add the different eval config options as a string to "config"->"eval"
     *
     * @param   array  $config        The configuration array to add the eval string to
     * @param   array  $options       The current fields options to check for eval config
     * @param   array  $evalFilter    If given an array of eval types that are whitelisted everything else will not be
     *                                added as option
     *
     * @return array
     */
    protected function addEvalConfig(array $config, array $options, array $evalFilter = []): array
    {
        // Build the eval string
        $eval = [];
        foreach (static::EVAL_TYPES as $type) {
            if ($options[$type] === true && (empty($evalFilter) || in_array($type, $evalFilter, true))) {
                $eval[] = $type;
            }
        }
        $evalString = implode(',', $eval);
        
        // Add eval only if we got it configured
        if (! empty($evalString)) {
            $config['eval'] = $evalString;
        } else {
            unset($config['eval']);
        }
        
        return $config;
    }
    
    /**
     * Internal helper which is used to add the "readOnly" option to the field configuration
     *
     * @param   array  $optionDefinition
     *
     * @return array
     */
    protected function addReadOnlyOptions(array $optionDefinition): array
    {
        $optionDefinition['readOnly'] = [
            'type' => 'bool',
            'default' => false,
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to add the "read only" configuration to the config array if the matching option was set
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     */
    protected function addReadOnlyConfig(array $config, array $options): array
    {
        if ($options['readOnly'] === true) {
            $config['readOnly'] = true;
        }
        
        return $config;
    }
    
    /**
     * Internal helper which is used to add the "edit record" wizard option to the Options::make definition.
     *
     * @param   array  $optionDefinition
     *
     * @return array
     */
    protected function addAllowEditOptions(array $optionDefinition): array
    {
        $optionDefinition['allowEdit'] = [
            'type' => 'bool',
            'default' => true,
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "edit record" wizard option to the config array
     *
     * @param   array  $config   The configuration array to add the wizard to
     * @param   array  $options  The current fields options to check if the wizard was enabled
     *
     * @return array
     */
    protected function addAllowEditConfig(array $config, array $options): array
    {
        if (! $options['allowEdit']) {
            return $config;
        }
        
        $config['fieldControl']['editPopup'] = [
            'disabled' => false,
            'options' => [
                'title' => 't3ba.formPreset.editRecord',
            ],
        ];
        
        return $config;
    }
    
    /**
     * Internal helper which is used to add the "new record" wizard option to the Options::make definition.
     *
     * @param   array  $optionDefinition
     *
     * @return array
     */
    protected function addAllowNewOptions(array $optionDefinition): array
    {
        $optionDefinition['allowNew'] = [
            'type' => 'bool',
            'default' => false,
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "new record" wizard option to the config array
     *
     * @param   array  $config   The configuration array to add the wizard to
     * @param   array  $options  The current fields options to check if the wizard was enabled
     *
     * @return array
     */
    protected function addAllowNewConfig(array $config, array $options): array
    {
        if (! $options['allowNew']) {
            return $config;
        }
        
        $config['fieldControl']['addRecord'] = [
            'disabled' => false,
            'options' => [
                'title' => 't3ba.formPreset.newRecord',
                'setValue' => 'append',
                'pid' => '###CURRENT_PID###',
            ],
        ];
        
        return $config;
    }
    
    /**
     * Internal helper to add a placeholder definition to the option array
     *
     * @param   array        $optionDefinition
     * @param   string|null  $defaultPlaceholder  Optional default placeholder value
     *
     * @return array
     */
    protected function addPlaceholderOption(array $optionDefinition, ?string $defaultPlaceholder = null): array
    {
        $optionDefinition['placeholder'] = [
            'type' => ['string', 'null'],
            'default' => $defaultPlaceholder,
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Adds the placeholder config option to the config array of the field
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     */
    protected function addPlaceholderConfig(array $config, array $options): array
    {
        if (empty($options['placeholder'])) {
            return $config;
        }
        $config['placeholder'] = $options['placeholder'];
        
        return $config;
    }
    
    /**
     * Internal helper to apply the "basePid" config option to the Options::make definition.
     * BasePid can be used to limit group or select fields to a certain page
     *
     * @param   array  $optionDefinition
     * @param   bool   $withMapping  Allow the usage of "basePid" option to be an array of tableName -> basePids
     *
     * @return array
     */
    protected function addBasePidOptions(array $optionDefinition, bool $withMapping = false): array
    {
        $pid = $this->context->cs()->typoContext->pid();
        
        if ($withMapping) {
            $optionDefinition['basePid'] = [
                'type' => ['int', 'null', 'string', 'array'],
                'default' => null,
                'filter' => function ($v) use ($pid) {
                    if ($v === null || is_int($v)) {
                        return $v;
                    }
                    
                    if (! is_array($v)) {
                        return $pid->get($v);
                    }
                    
                    // Generate the table names for all keys
                    $keys = array_keys($v);
                    foreach ($keys as $i => $table) {
                        $keys[$i] = $this->context->getRealTableName($table);
                    }
                    
                    return array_combine($keys, $pid->getMultiple($v));
                },
            ];
        } else {
            $optionDefinition['basePid'] = [
                'type' => ['int', 'null', 'string'],
                'default' => null,
                'filter' => static function ($v) use ($pid) {
                    return $v === null ? $v : $pid->get($v);
                },
            ];
        }
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "basePid" config option to the config array of the field
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     */
    protected function addBasePidConfig(array $config, array $options): array
    {
        if ($options['basePid'] !== null) {
            $config['basePid'] = $options['basePid'];
        }
        
        return $config;
    }
    
    /**
     * Internal helper to configure an mm table for the current field.
     *
     * @param   array  $config  The current "config" array of the field, to add the mm table to
     * @param   array  $options
     *
     * @return array
     */
    protected function addMmTableConfig(array $config, array $options): array
    {
        // Skip if we should not use an mm table
        if ($options['mmTable'] === false) {
            $this->configureSqlColumn(static function (Column $column) {
                $column->setType(new TextType())->setLength(SqlFieldLength::TEXT)->setDefault('');
            });
            
            return $config;
        }
        
        // MM Tables are not supported in sections
        if ($this->isInFlexFormSection()) {
            unset($config['MM']);
            
            return $config;
        }
        
        // Build the field name and respect the flex form parent field
        $fieldId = Inflector::toUnderscore($this->field->getId());
        if ($this->isFlexForm()) {
            $fieldId = 'flex_' . Inflector::toUnderscore($this->getTcaField()->getId()) . '_' . $fieldId;
        }
        
        $mmTableName = $this->context->cs()->sqlRegistry->registerMmTable(
            $this->getTcaTable()->getTableName(),
            $fieldId,
            $options['mmTableName'] ?? null
        );
        
        $this->configureSqlColumn(static function (Column $column) {
            $column->setType(new IntegerType())->setLength(11)->setDefault(0);
        });
        
        $config['MM'] = $mmTableName;
        $config['prepend_tname'] = true;
        
        // Done
        return $config;
    }
    
    /**
     * Provides the option definition for the "default" configuration
     *
     * @param   array       $optionDefinition
     * @param   array|null  $type
     * @param   mixed       $default
     *
     * @return array
     */
    protected function addDefaultOptions(array $optionDefinition, ?array $type = null, $default = ''): array
    {
        $optionDefinition['default'] = [
            'type' => $type ?? ['string'],
            'preFilter' => static function ($v) {
                if (is_array($v) && count($v) === 2 && is_string($v[0] ?? null) && is_string($v[1] ?? null)) {
                    return '@callback:' . $v[0] . '->' . $v[1];
                }
                
                return $v;
            },
            'default' => $default,
        ];
        
        return $optionDefinition;
    }
    
    /**
     * Adds the default configuration based on the given options
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     */
    protected function addDefaultConfig(array $config, array $options): array
    {
        if ($options['default'] !== null && $options['default'] !== '') {
            $config['default'] = $options['default'];
        }
        
        return $config;
    }
    
    /**
     * Provides the option definition for the minItems and maxItems options
     *
     * @param   array  $optionDefinition
     * @param   array  $options
     *
     * @return array
     */
    protected function addMinMaxItemOptions(array $optionDefinition, array $options = []): array
    {
        $optionDefinition['minItems'] = [
            'type' => 'int',
            'default' => is_numeric($options['minItems']) ? (int)$options['minItems'] : 0,
        ];
        $optionDefinition['maxItems'] = [
            'type' => 'int',
            'default' => is_numeric($options['maxItems']) ? (int)$options['maxItems'] : 999,
        ];
        
        return $optionDefinition;
    }
    
    protected function addMinMaxItemConfig(array $config, array $options): array
    {
        // If the field is required -> minItems is 1
        if ($options['required'] === true) {
            $options['minItems'] = max($options['minItems'], 1);
        }
        $config['minitems'] = $options['minItems'];
        $config['maxitems'] = $options['maxItems'];
        
        return $config;
    }
    
    /**
     * Internal helper to add the "maxLength" config option to the Options::make definition.
     * This makes only sense for "input" type fields
     *
     * @param   array  $optionsDefinition
     * @param   int    $defaultMax  The default value of the maximal input length
     * @param   int    $defaultMin  The default value for the minimal input length
     *
     * @return array
     */
    protected function addMinMaxLengthOptions(
        array $optionsDefinition,
        int $defaultMax = 512,
        int $defaultMin = 0
    ): array
    {
        $optionsDefinition['maxLength'] = [
            'type' => 'int',
            'default' => $defaultMax,
        ];
        $optionsDefinition['minLength'] = [
            'type' => 'int',
            'default' => $defaultMin,
        ];
        
        return $optionsDefinition;
    }
    
    /**
     * Internal helper to apply the "maxLength" configuration to the config array of a input field
     *
     * @param   array  $config
     * @param   array  $options
     * @param   bool   $addSqlStatement  If set to true the sql statement of this column will automatically be
     *
     * @return array
     */
    protected function addMaxLengthConfig(array $config, array $options, bool $addSqlStatement = false): array
    {
        if (! empty($options['maxLength'])) {
            $config['max'] = $options['maxLength'];
        }
        if (! empty($options['minLength'])) {
            $config['min'] = $options['minLength'];
        }
        
        if ($addSqlStatement) {
            $this->configureSqlColumn(static function (Column $column) use ($options) {
                if ((int)$options['maxLength'] <= 4096) {
                    $column->setType(new StringType())
                           ->setDefault('')
                           ->setLength((int)$options['maxLength']);
                } else {
                    $column->setType(new TextType())
                           ->setDefault(null)
                           ->setLength(null);
                }
            });
        }
        
        return $config;
    }
    
    /**
     * Internal helper which is used to generate a list of valid table names.
     * It will always return an array of table names. If a comma separated string is given, it will be broken up into
     * an array, if a ...table short hand is given it will be resolved to the current extension's table name.
     * Non unique items will be dropped
     *
     * @param   string|array  $tableInput
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     */
    protected function generateTableNameList($tableInput): array
    {
        if (! is_array($tableInput)) {
            if (is_string($tableInput)) {
                $tableInput = Arrays::makeFromStringList($tableInput);
            } else {
                throw new TcaBuilderException('The given value for $table is invalid! Please use either an array of table names, or a single table as string!');
            }
        }
        
        return array_unique(
            array_map([$this->context, 'getRealTableName'], $tableInput)
        );
        
    }
    
    /**
     * Returns true if the currently configured field is the child of a flex form
     *
     * @return bool
     */
    protected function isFlexForm(): bool
    {
        return $this->field->getForm() instanceof Flex;
    }
    
    /**
     * Returns true if this field is in a repeatable flex form section.
     *
     * @return bool
     */
    protected function isInFlexFormSection(): bool
    {
        return $this->isFlexForm() && $this->field->getParent() instanceof FlexSection;
    }
    
    /**
     * Returns the instance of the tca table, even if this field is part of a flex form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable
     */
    protected function getTcaTable(): TcaTable
    {
        if ($this->isFlexForm()) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $form = $this->field->getForm()->getContainingField()->getForm();
        } else {
            $form = $this->field->getForm();
        }
        
        if ($form instanceof TcaTableType) {
            $form = $form->getParent();
        }
        
        return $form;
    }
    
    /**
     * Returns the tca field, even if the currently configured field is part of a flex form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField
     */
    protected function getTcaField(): TcaField
    {
        if ($this->isFlexForm()) {
            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            return $this->field->getForm()->getContainingField();
        }
        
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field;
    }
    
    /**
     * Helper method that executes the given callback if the current field has a "getColumn()" method
     * meaning it can have a SQL definition.
     *
     * @param   callable  $callback  The callback receives the column definition to configure
     *
     * @return void
     * @see \Doctrine\DBAL\Schema\Column
     */
    protected function configureSqlColumn(callable $callback): void
    {
        if (method_exists($this->field, 'getColumn')) {
            /** @var Column $col */
            $col = $this->field->getColumn();
            $callback($col);
        }
    }
}
