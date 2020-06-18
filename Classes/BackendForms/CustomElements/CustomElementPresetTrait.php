<?php
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

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;
use ReflectionClass;

/**
 * Trait CustomFormElementPresetTrait
 *
 * This trait can be used to create your own preset appliers for your custom form elements.
 * It encapsulates all logic that is required to apply a custom form element to an abstract field instance
 *
 * @package LaborDigital\Typo3BetterApi\BackendForms\FormPresets
 */
trait CustomElementPresetTrait
{
    
    /**
     * This helper can be used to create custom definitions for your own custom form elements.
     * It is mend to be used inside your own field preset, that validates and documents the possible options
     * and passes them into this helper afterwards. It will take care of all the heavy lifting and class
     * validation for you.
     *
     * @param   AbstractFormField  $field               The reference of the field you currently configure.
     *                                                  Typically $this->field
     * @param   ExtConfigContext   $context             The ext config context. Typically $this->context
     * @param   string             $customElementClass  The class name of the custom element you want to register.
     *                                                  The class has to implement the CustomElementInterface interface
     * @param   array              $options             Any options you want to specify for your custom element
     *
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function applyCustomElementPreset(
        AbstractFormField $field,
        ExtConfigContext $context,
        string $customElementClass,
        array $options = []
    ): void {
        // Validate if the class exists
        if (! class_exists($customElementClass)) {
            throw new BackendFormException('Could not configure your field: ' . $field->getId()
                                           . " to use the custom element with class: $customElementClass. Because the class does not exist!");
        }
        if (! in_array(CustomElementInterface::class, class_implements($customElementClass))) {
            throw new BackendFormException('Could not configure your field: ' . $field->getId()
                                           . " to use the custom element with class: $customElementClass. Because the class does not implement the required "
                                           . CustomElementInterface::class . ' interface!');
        }
        
        // Apply the configuration of the field
        $defaultConfig = [
            'config' => [
                'type'                 => 'text',
                'renderType'           => 'betterApiCustomElement',
                'customElementClass'   => $customElementClass,
                'customElementOptions' => $options,
            ],
        ];
        $field->setRaw(Arrays::merge($field->getRaw(), $defaultConfig));
        if ($field instanceof TcaField) {
            $field->setSqlDefinition('mediumtext');
        }
        
        
        // Register backend handlers if required
        $ref     = new ReflectionClass($customElementClass);
        $methods = [
            'backendSaveFilter'    => 'registerBackendSaveFilter',
            'backendFormFilter'    => 'registerBackendFormFilter',
            'backendActionHandler' => 'registerBackendActionHandler',
        ];
        foreach ($methods as $methodName => $setterMethodName) {
            $method = $ref->getMethod($methodName);
            // Save the work if we would only call the empty defaults
            if ($method->getDeclaringClass()->getName() === AbstractCustomElement::class) {
                continue;
            }
            // Register the handler
            $field->$setterMethodName($customElementClass, $methodName);
        }
        
        // Allow custom configuration
        call_user_func([$customElementClass, 'configureField'], $field, $options, $context);
    }
}
