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


namespace LaborDigital\T3ba\Tool\FormEngine\Custom;


use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;

interface CustomElementInterface
{
    /**
     * This method is called when, and ONLY IF the field is configured using applyPreset in the tca builder.
     * It will receive the array of options as well as the field instance. You can use this method to apply additional
     * TCA configuration to the field, before it is cached for later usage.
     *
     * @param   AbstractField      $field    The instance of the field to apply this form element to
     *                                       The instance will already be preconfigured to be rendered as a custom node
     *                                       in the form framework
     * @param   array              $options  The additional options that were given in the applyPreset method
     * @param   TcaBuilderContext  $context  The context of the extension, that is currently applying this element
     *
     * @return void
     */
    public static function configureField(AbstractField $field, array $options, TcaBuilderContext $context): void;
    
    /**
     * This method receives the prepared form element context and should render the html that will be displayed in the
     * backend.
     *
     * @return string
     */
    public function render(): string;
    
    /**
     * This is a low-level method that you will probably only use in extreme edge-cases.
     * It will receive the prepared and ready result array, formatted for typo3's form engine.
     *
     * You can use this method as a filter for the given array and return the modified array back
     *
     * @param   array  $result
     *
     * @return array
     */
    public function filterResultArray(array $result): array;
}
