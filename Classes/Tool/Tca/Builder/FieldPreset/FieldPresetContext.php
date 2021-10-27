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
 * Last modified: 2021.10.25 at 12:53
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use LaborDigital\T3ba\TypoContext\ConfigFacet;

class FieldPresetContext extends TcaBuilderContext
{
    /**
     * Holds the instance of the form field to configure
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField
     */
    protected $field;
    
    /**
     * @inheritDoc
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(TcaBuilderContext $parent)
    {
        $this->commonServices = $parent->commonServices;
    }
    
    /**
     * Returns the global config facet of the typo context
     *
     * @return \LaborDigital\T3ba\TypoContext\ConfigFacet
     */
    public function getConfigFacet(): ConfigFacet
    {
        return $this->commonServices->typoContext->config();
    }
    
    /**
     * Returns true if the currently configured field is the child of a flex form
     *
     * @return bool
     */
    public function isFlexForm(): bool
    {
        return $this->field->getForm() instanceof Flex;
    }
    
    /**
     * Returns true if this field is in a repeatable flex form section.
     *
     * @return bool
     */
    public function isInFlexFormSection(): bool
    {
        return $this->isFlexForm() && $this->field->getParent() instanceof FlexSection;
    }
    
    /**
     * Returns the instance of the form field to configure
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField
     */
    public function getField(): AbstractField
    {
        return $this->field;
    }
    
    /**
     * Returns the instance of the TCA table, even if this field is part of a flex form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable
     */
    public function getTcaTable(): TcaTable
    {
        if ($this->isFlexForm()) {
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
    public function getTcaField(): TcaField
    {
        if ($this->isFlexForm()) {
            return $this->field->getForm()->getContainingField();
        }
        
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->field;
    }
    
    /**
     * Helper method that executes the given callback if the current field has a "getColumn()" method
     * meaning it can have an SQL definition.
     *
     * @param   callable  $callback  The callback receives the column definition to configure
     *
     * @return void
     * @see \Doctrine\DBAL\Schema\Column
     */
    public function configureSqlColumn(callable $callback): void
    {
        if (method_exists($this->field, 'getColumn')) {
            $callback($this->field->getColumn());
        }
    }
    
    /**
     * Internal helper to inject the field to apply the context to
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext  $i
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField             $field
     *
     * @internal
     * @private
     */
    public static function setField(self $i, AbstractField $field): void
    {
        $i->field = $field;
    }
}