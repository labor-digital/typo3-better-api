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
 * Last modified: 2020.08.23 at 23:23
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

namespace LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;
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
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField
     */
    protected $field;

    /**
     * The context of the field
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext
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
                    'type'    => 'bool',
                    'default' => isset($evalDefaults[$type]) ? $evalDefaults[$type] : false,
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
            if ((empty($evalFilter) || $options[$type] === true && in_array($type, $evalFilter, true))) {
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
            'type'    => 'bool',
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
            'type'    => 'bool',
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
            'options'  => [
                'title' => 'betterApi.formPreset.editRecord',
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
            'type'    => 'bool',
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
            'options'  => [
                'title'    => 'betterApi.formPreset.newRecord',
                'setValue' => 'append',
                'pid'      => '###CURRENT_PID###',
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
            'type'    => ['string', 'null'],
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
        // Helper to convert a pid string identifier into a number value
        $pid               = $this->context->cs()->typoContext->pid();
        $pidValueConverter = static function ($value) use ($pid) {
            return $pid->has($value) ? $pid->get($value) : $value;
        };

        if ($withMapping) {
            $optionDefinition['basePid'] = [
                'type'    => ['int', 'null', 'string', 'array'],
                'default' => null,
                'filter'  => function ($v) use ($pidValueConverter) {
                    if ($v === null) {
                        return $v;
                    }
                    if (! is_array($v)) {
                        return $pidValueConverter($v);
                    }

                    // Generate the table names for all keys
                    $keys = array_keys($v);
                    foreach ($keys as $i => $table) {
                        $keys[$i] = $this->context->getRealTableName($table);
                    }

                    // Translate the values to pid numbers
                    $values = array_values($v);
                    $values = array_map($pidValueConverter, $values);

                    return array_combine($keys, $values);
                },
            ];
        } else {
            $optionDefinition['basePid'] = [
                'type'    => ['int', 'null', 'string'],
                'default' => null,
                'filter'  => static function ($v) use ($pidValueConverter) {
                    if ($v === null) {
                        return $v;
                    }

                    return $pidValueConverter($v);
                },
            ];
        }

        // @todo remove deprecated option
        // @todo is this really deprecated?
        $optionDefinition['limitToBasePid'] = [
            'default' => null,
        ];

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
            return $config;
        }
        if ($this->isInFlexFormSection()) {
            return $config;
        }

        // Build the field name and respect the flex form parent field
        $fieldId = Inflector::toUnderscore($this->field->getId());
        if ($this->isFlexForm()) {
            $fieldId = 'flex_' . Inflector::toUnderscore($this->getTcaField()->getId()) . '_' . $fieldId;
        }

        // Check if we are inside a section
        if ($this->isInFlexFormSection()) {
            // MM Tables are not supported in sections
            unset($config['MM']);

            return $config;
        }

        // Add the mm table
        $mmTableName = $this->context->cs()->sqlBuilder->addMmTableDefinition($this->getTcaTable()->getTableName(),
            $fieldId, $options['mmTableName']);
        $this->setSqlDefinitionForTcaField('int(11) DEFAULT \'0\'');

        // Create the mm table configuration
        $config['MM']            = $mmTableName;
        $config['prepend_tname'] = true;

        // Done
        return $config;
    }

    protected function addMinMaxItemOptions(array $optionDefinition, array $options = []): array
    {
        $optionDefinition['minItems'] = [
            'type'    => 'int',
            'default' => is_numeric($options['minItems']) ? (int)$options['minItems'] : 0,
        ];
        $optionDefinition['maxItems'] = [
            'type'    => 'int',
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
    ): array {
        $optionsDefinition['maxLength'] = [
            'type'    => 'int',
            'default' => $defaultMax,
        ];
        $optionsDefinition['minLength'] = [
            'type'    => 'int',
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
            // Make sure we don't create varChars that are too long...
            $sqlType = (int)$options['maxLength'] <= 4096 ? 'varchar(' . $options['maxLength'] . ') DEFAULT \'\''
                : 'text';
            $this->setSqlDefinitionForTcaField($sqlType);
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
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
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
        return false;
        throw new NotImplementedException();

        return $this->field->getForm() instanceof FlexForm;
    }

    /**
     * Returns true if this field is in a repeatable flex form section.
     *
     * @return bool
     */
    protected function isInFlexFormSection(): bool
    {
        return false;
        throw new NotImplementedException();

        return $this->isFlexForm() && $this->field->getParent() instanceof FlexSection;
    }

    /**
     * Returns the instance of the tca table, even if this field is part of a flex form
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    protected function getTcaTable(): TcaTable
    {
        if ($this->isFlexForm()) {
            $form = $this->field->getForm()->getContainingField()->getForm();
        } else {
            $form = $this->field->getForm();
        }

        if ($form instanceof TcaTableType) {
            $form = $form->getForm();
        }

        return $form;
    }

    /**
     * Returns the tca field, even if the currently configured field is part of a flex form
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
     */
    protected function getTcaField(): TcaField
    {
        if ($this->isFlexForm()) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return $this->field->getForm()->getContainingField();
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field;
    }

    /**
     * Internal helper to set the sql definition for a field. But only if said field is a tca field and not part of a
     * flex form
     *
     * @param   string  $definition
     *
     * @return void
     */
    protected function setSqlDefinitionForTcaField(string $definition): void
    {
        // Ignore on flex forms -> We don't require that
        if ($this->isFlexForm()) {
            return;
        }

        // Get the tca field
        $this->getTcaField()->setSqlDefinition($definition);
    }
}
