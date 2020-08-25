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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomElements;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface CustomElementInterface
{
    
    /**
     * This method is called when, and ONLY IF the field is configured using the AbstractFormField's applyPreset method
     * It will receive the array of options as well as the field instance. You can use this method to apply additional
     * TCA configuration to the field, before it is cached for later usage.
     *
     * @param   AbstractFormField  $field    The instance of the field to apply this form element to
     *                                       The instance will already be preconfigured to be rendered as a custom node
     *                                       in the form framework
     * @param   array              $options  The additional options that were given in the applyPreset method
     * @param   ExtConfigContext   $context  The context of the extension, that is currently applying this element
     *
     * @return mixed
     */
    public static function configureField(AbstractFormField $field, array $options, ExtConfigContext $context);
    
    /**
     * This method receives the prepared form element context and should render the html that will be displayed in the
     * backend.
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementContext  $context
     *
     * @return string
     */
    public function render(CustomElementContext $context): string;
    
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
    
    /**
     * This method can be used to filter the incoming data for this field when the backend form saves the data.
     * You can also validate the value and throw an exception with an error message from this method, that will then
     * be rendered to the client as a flash message.
     *
     * The filter is executed once per field, so if there are e.g. 3 instances of the same field in your form, it will
     * be called 3 times, not just once!
     *
     * To filter the value, just return it by the method.
     *
     * @param   CustomElementFormActionContext  $context     The context object, containing all relevant
     *                                                       information about the current save process
     *
     * @return mixed
     * @deprecated removed in v10 use dataHandlerSaveFilter instead
     */
    public function backendSaveFilter(CustomElementFormActionContext $context);
    
    /**
     * This method can be used to filter the incoming data for this field when the data handler saves the data to the
     * database. You can also validate the value and throw an exception with an error message from this method, that
     * will then be rendered to the client as a flash message.
     *
     * The filter is executed once per field, so if there are e.g. 3 instances of the same field in your form, it will
     * be called 3 times, not just once!
     *
     * To filter the value, just return it by the method.
     *
     * @param   CustomElementFormActionContext  $context     The context object, containing all relevant
     *                                                       information about the current save process
     *
     * @return mixed
     */
    public function dataHandlerSaveFilter(CustomElementFormActionContext $context);
    
    /**
     * This method can be used to prepare the form field before the form engine begins the rendering of the form it is
     * placed it. This method also has access to the prepared TCA array and may change configuration and values of the
     * database row.
     *
     * The filter is executed once per field, so if there are e.g. 3 instances of the same field in your form, it will
     * be called 3 times, not just once!
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementFormActionContext  $context
     *
     * @return mixed
     */
    public function backendFormFilter(CustomElementFormActionContext $context);
    
    /**
     * This method is executed when any kind of backend action occurs that is not a save event. Actions are move, copy,
     * translation, deletion...
     *
     * The filter is executed once per field, so if there are e.g. 3 instances of the same field in your form, it will
     * be called 3 times, not just once!
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementFormActionContext  $context
     *
     * @return mixed
     * @deprecated removed in v10 use dataHandlerActionHandler instead
     */
    public function backendActionHandler(CustomElementFormActionContext $context);
    
    /**
     * This method is executed when any kind of data handler action occurs that is NOT a save event.
     * Actions are move, copy, translation, deletion...
     *
     * The filter is executed once per field, so if there are e.g. 3 instances of the same field in your form, it will
     * be called 3 times, not just once!
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementFormActionContext  $context
     *
     * @return mixed
     */
    public function dataHandlerActionHandler(CustomElementFormActionContext $context);
}
