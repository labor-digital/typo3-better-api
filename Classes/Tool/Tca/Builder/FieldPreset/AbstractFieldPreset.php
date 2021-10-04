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
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetBasePidTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetEvalTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetGenericWizardsTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetMinMaxTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetMmTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use Neunerlei\Arrays\Arrays;

abstract class AbstractFieldPreset implements FieldPresetInterface
{
    use ContainerAwareTrait;
    use FieldPresetMmTrait;
    use FieldPresetEvalTrait;
    use FieldPresetBasePidTrait;
    use FieldPresetMinMaxTrait;
    use FieldPresetGenericWizardsTrait;
    
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
     * Internal helper which is used to add the "readOnly" option to the field configuration
     *
     * @param   array  $optionDefinition
     *
     * @return array
     * @deprecated will be removed in v12 use the setReadOnly() method on a field instead     *
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
     * @deprecated will be removed in v12 use the setReadOnly() method on a field instead
     */
    protected function addReadOnlyConfig(array $config, array $options): array
    {
        if ($options['readOnly'] === true) {
            $table = $this->field->getForm()->getTableName();
            $field = $this->field->getId();
            trigger_error(
                'Deprecated option in: ' . $table . '::' . $field . '. The "readOnly" option will be removed in v12, use the setReadOnly() method on a field instead',
                E_USER_DEPRECATED
            );
            $config['readOnly'] = true;
        }
    
        return $config;
    }
    
    /**
     * Adds the option to configure the "size" of an "input" field either using a percentage or integer value.
     *
     * @param   array        $optionDefinition
     * @param   string|null  $optionName  default: "size", can be set to another name as well, (e.g. cols)
     *
     * @return array
     */
    protected function addInputSizeOption(array $optionDefinition, ?string $optionName = null): array
    {
        $optionDefinition[$optionName ?? 'size'] = [
            'type' => ['int', 'string'],
            'default' => '100%',
            'filter' => static function ($val): int {
                $minWidth = 10;
                $maxWidth = 50;
                if (is_string($val)) {
                    if ($val === '100%') {
                        return $maxWidth;
                    }
                    if (strpos(trim($val), '%') !== false) {
                        $val = $maxWidth * (int)trim(trim($val), '% ');
                    } else {
                        $val = (int)$val;
                    }
                }
                
                return max($minWidth, min($maxWidth, $val));
            },
        ];
        
        return $optionDefinition;
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
