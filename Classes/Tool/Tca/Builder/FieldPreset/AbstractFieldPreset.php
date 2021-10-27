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


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\Container;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetBasePidTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetEvalTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetGenericWizardsTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetMinMaxTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetMmTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits\FieldPresetOptionDeprecationTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

abstract class AbstractFieldPreset implements FieldPresetInterface
{
    use ContainerAwareTrait;
    use FieldPresetMmTrait;
    use FieldPresetEvalTrait;
    use FieldPresetBasePidTrait;
    use FieldPresetMinMaxTrait;
    use FieldPresetGenericWizardsTrait;
    use FieldPresetOptionDeprecationTrait;
    
    /**
     * Holds the instance of the form field to configure
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField
     * @todo make this a read only property in v11 and up and read the value from $this->context->getField()
     */
    protected $field;
    
    /**
     * The context of the field
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext
     */
    protected $context;
    
    /**
     * @inheritDoc
     * @deprecated will be replaced in v11 with "initialize($field, $context)", support will be dropped in v12
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
     * Helper to create an option container for your field preset.
     * The option container is an extension on the Options::make syntax and is specifically designed to apply field definitions to a config array
     *
     * @param   array  $definition  The same syntax as in {@link \Neunerlei\Options\Options::make()}, ADDITIONALLY all objects in the array that extend the
     *                              {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\AbstractOption} class will be used to extend the $definition
     *                              and apply their configuration when the "apply()" method of the container is executed
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\Container
     */
    protected function initializeOptions(array $definition = []): Container
    {
        return $this->makeInstance(Container::class, [$this->context, $definition]);
    }
    
    /**
     * Internal helper which is used to generate a list of valid table names.
     * It will always return an array of table names. If a comma separated string is given, it will be broken up into
     * an array, if a ...table shorthand is given it will be resolved to the current extension's table name.
     * Non-unique items will be dropped
     *
     * @param   string|array  $tableInput
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     * @deprecated will be removed in v11 use $this->context->getRealTableNameList() instead
     */
    protected function generateTableNameList($tableInput): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '(); instead!',
            E_USER_DEPRECATED
        );
        
        return $this->context->getRealTableNameList($tableInput);
    }
    
    /**
     * Returns true if the currently configured field is the child of a flex form
     *
     * @return bool
     * @deprecated will be removed in v11 use $this->context->isFlexForm() instead
     */
    protected function isFlexForm(): bool
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '(); instead!',
            E_USER_DEPRECATED
        );
        
        return $this->context->isFlexForm();
    }
    
    /**
     * Returns true if this field is in a repeatable flex form section.
     *
     * @return bool
     * @deprecated will be removed in v11 use $this->context->isInFlexFormSection() instead
     */
    protected function isInFlexFormSection(): bool
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '(); instead!',
            E_USER_DEPRECATED
        );
        
        return $this->context->isInFlexFormSection();
    }
    
    /**
     * Returns the instance of the tca table, even if this field is part of a flex form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable
     * @deprecated will be removed in v11 use $this->context->getTcaTable() instead
     */
    protected function getTcaTable(): TcaTable
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '(); instead!',
            E_USER_DEPRECATED
        );
        
        return $this->context->getTcaTable();
    }
    
    /**
     * Returns the tca field, even if the currently configured field is part of a flex form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField
     * @deprecated will be removed in v11 use $this->context->getTcaField() instead
     */
    protected function getTcaField(): TcaField
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '(); instead!',
            E_USER_DEPRECATED
        );
        
        return $this->context->getTcaField();
    }
    
    /**
     * Helper method that executes the given callback if the current field has a "getColumn()" method
     * meaning it can have an SQL definition.
     *
     * @param   callable  $callback  The callback receives the column definition to configure
     *
     * @return void
     * @see        \Doctrine\DBAL\Schema\Column
     * @deprecated will be removed in v11 use $this->context->configureSqlColumn() instead
     */
    protected function configureSqlColumn(callable $callback): void
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            '$this->context->' . __FUNCTION__ . '($callback); instead!',
            E_USER_DEPRECATED
        );
        
        $this->context->configureSqlColumn($callback);
    }
}
