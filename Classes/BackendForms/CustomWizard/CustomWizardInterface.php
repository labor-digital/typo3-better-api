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

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomWizard;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface CustomWizardInterface
{
    /**
     * This method is called when, and ONLY IF the field is configured using the AbstractFormField's applyPreset method
     * It will receive the array of options as well as the field instance. You can use this method to apply additional
     * TCA configuration to the field, before it is cached for later usage.
     *
     * @param AbstractFormField $field   The instance of the field to apply this form wizard to
     *                                   The instance will already have the wizard configuration set
     * @param array             $options The additional options that were given in the applyPreset method
     * @param ExtConfigContext  $context The context of the extension, that is currently applying this wizard
     *
     * @return mixed
     */
    public static function configureField(AbstractFormField $field, array $options, ExtConfigContext $context);
    
    /**
     * Receives the custom wizard context, containing the wizard configuration, should render the
     * html of the wizard and return it as a string.
     *
     * @param \LaborDigital\Typo3BetterApi\BackendForms\CustomWizard\CustomWizardContext $context
     *
     * @return string
     */
    public function render(CustomWizardContext $context): string;
}
