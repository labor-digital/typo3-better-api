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

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Wizard;

use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;

trait CustomWizardPresetTrait
{
    
    /**
     * This helper is quite similar to the CustomElementPresetTrait class but is used
     * to create custom wizard definitions for your own wizards.
     * It is mend to be used inside your own field preset, that validates and documents the possible options
     * and passes them into this helper afterwards. It will take care of all the heavy lifting and class
     * validation for you.
     *
     * @param   string                  $wizardClass  The class name of the custom wizard you want to register.
     *                                                The class has to implement the CustomWizardInterface interface
     * @param   array|null              $options      Any options you want to specify for your custom wizard
     *                                                Generic options on all wizards are:
     *                                                - before array|string: A list of other wizards that should be
     *                                                displayed after this wizard
     *                                                - after array|string: A list of other wizards that should be
     *                                                displayed before this wizard
     *                                                - wizardId string: Can be used to manually set the wizard id.
     *                                                If left empty the id will be automatically created.
     *
     * @param   AbstractField|null      $field        The reference of the field you currently configure.
     *                                                Typically $this->field
     * @param   TcaBuilderContext|null  $context      The ext config context. Typically $this->context
     *
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
     */
    protected function applyCustomWizardPreset(
        string $wizardClass,
        ?array $options = null,
        ?AbstractField $field = null,
        ?TcaBuilderContext $context = null
    ): void
    {
        $options = $options ?? [];
        if ($this instanceof AbstractFieldPreset) {
            $field = $field ?? $this->field;
            $context = $context ?? $this->context;
        }
        
        // Validate if the class exists
        if (! class_exists($wizardClass)) {
            throw new TcaBuilderException(
                'Could not configure your field: ' . $field->getId()
                . ' to use the custom wizard with class: ' . $wizardClass . '. Because the class does not exist!');
        }
        
        if (! in_array(CustomWizardInterface::class,
            class_implements($wizardClass), true)) {
            throw new TcaBuilderException(
                'Could not configure your field: ' . $field->getId()
                . ' to use the custom wizard with class: ' . $wizardClass
                . '. Because the class does not implement the required '
                . CustomWizardInterface::class . ' interface!'
            );
        }
        
        $beforeAfterDefinition = [
            'type' => ['string', 'array'],
            'default' => [],
            'filter' => static function ($v) { return is_array($v) ? $v : [$v]; },
        ];
        $options = Options::make($options, [
            'wizardId' => [
                'type' => 'string',
                'default' => Inflector::toDashed(str_replace('\\', '-', $wizardClass)),
            ],
            'before' => $beforeAfterDefinition,
            'after' => $beforeAfterDefinition,
        ]);
        
        // Build the wizard configuration
        $config = [
            'fieldWizard' => [
                $options['wizardId'] => [
                    'type' => 't3baWizard',
                    'renderType' => 't3baWizard',
                    'options' => [
                        'className' => $wizardClass,
                        'fieldName' => $field->getId(),
                    ],
                    'before' => $options['before'],
                    'after' => $options['after'],
                ],
            ],
        ];
        $field->addConfig($config);
        
        // Run the field configuration
        call_user_func([$wizardClass, 'configureField'], $field, $options, $context);
    }
}
