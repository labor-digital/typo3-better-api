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
 * Last modified: 2021.04.29 at 22:17
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
 * Last modified: 2020.03.19 at 02:59
 */

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Field;

use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Sql\SqlFieldLength;
use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use Neunerlei\Arrays\Arrays;

/**
 * Trait CustomFieldPresetTrait
 *
 * This trait can be used to create your own preset appliers for your custom form elements.
 * It encapsulates all logic that is required to apply a custom form element to an abstract field instance
 *
 * @package LaborDigital\T3BA\Tool\FormEngine\Custom\Field
 */
trait CustomFieldPresetTrait
{
    /**
     * This helper can be used to create custom definitions for your own custom form elements.
     * It is mend to be used inside your own field preset, that validates and documents the possible options
     * and passes them into this helper afterwards. It will take care of all the heavy lifting and class
     * validation for you.
     *
     * @param   string                 $customElementClass  The class name of the custom element you want to register.
     *                                                      The class has to implement the CustomElementInterface
     *                                                      interface
     * @param   array |null            $options             Any options you want to specify for your custom element
     * @param   AbstractField|null     $field               The reference of the field you currently configure.
     *                                                      Typically $this->field
     * @param   ExtConfigContext|null  $context             The ext config context. Typically $this->context
     *
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
     */
    protected function applyCustomElementPreset(
        string $customElementClass,
        ?array $options = null,
        ?AbstractField $field = null,
        ?ExtConfigContext $context = null
    ): void
    {
        $options = $options ?? [];
        if ($this instanceof AbstractFieldPreset) {
            $field = $field ?? $this->field;
            $context = $context ?? $this->context;
        }
        
        if (! class_exists($customElementClass)) {
            throw new TcaBuilderException(
                'Could not configure your field: ' . $field->getId()
                . ' to use the custom element with class: ' . $customElementClass
                . '. Because the class does not exist!');
        }
        
        if (! in_array(CustomFieldInterface::class, class_implements($customElementClass), true)) {
            throw new TcaBuilderException(
                'Could not configure your field: ' . $field->getId()
                . ' to use the custom element with class: ' . $customElementClass
                . '. Because the class does not implement the required '
                . CustomFieldInterface::class . ' interface!');
        }
        
        $field->setRaw(
            Arrays::merge(
                $field->getRaw(),
                [
                    'config' => [
                        'type' => 'text',
                        'renderType' => 't3baField',
                        't3baClass' => $customElementClass,
                        't3ba' => $options,
                    ],
                ]
            )
        );
        
        if ($field instanceof TcaField) {
            $field->getColumn()->setType(new TextType())->setLength(SqlFieldLength::MEDIUM_TEXT);
        }
        
        $dataHookOptions = $field->getDataHookOptions();
        $field->setDataHookOptions(array_merge($dataHookOptions,
            ['contextClass' => CustomFieldDataHookContext::class]));
        
        call_user_func([$customElementClass, 'configureField'], $field, $options, $context);
        
        $field->setDataHookOptions($dataHookOptions);
    }
}
