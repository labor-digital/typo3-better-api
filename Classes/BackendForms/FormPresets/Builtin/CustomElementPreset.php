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

namespace LaborDigital\Typo3BetterApi\BackendForms\FormPresets\Builtin;

use LaborDigital\Typo3BetterApi\BackendForms\CustomElements\CustomElementPresetTrait;
use LaborDigital\Typo3BetterApi\BackendForms\CustomWizard\CustomWizardPresetTrait;
use LaborDigital\Typo3BetterApi\BackendForms\FormPresets\AbstractFormPreset;

class CustomElementPreset extends AbstractFormPreset
{
    use CustomElementPresetTrait;
    use CustomWizardPresetTrait;
    
    /**
     * Can be used to configure a generic, custom form element.
     *
     * @param string $formElementClass The class name of the custom element you want to register.
     *                                 The class has to implement the CustomElementInterface interface
     * @param array  $options          Any options you want to specify for your custom element
     */
    public function customElement(string $formElementClass, array $options = [])
    {
        $this->applyCustomElementPreset($this->field, $this->context, $formElementClass, $options);
    }
    
    /**
     * Can be used to configure a generic, custom wizard class.
     *
     * @param string $wizardClass The class name of the custom wizard you want to register.
     *                            The class has to implement the CustomWizardInterface interface
     * @param array  $options     Any options you want to specify for your custom wizard
     *                            Generic options on all wizards are:
     *                            - position string ("right"): Can be set to "top", "left", "right",
     *                            "bottom" and determines the position where the wizard should be rendered.
     *                            NOTE: This affects all wizards of this field.
     *                            - wizardId string: Can be used to manually set the wizard id.
     *                            If left empty the id will be automatically created.
     */
    public function customWizard(string $wizardClass, array $options = [])
    {
        $this->applyCustomWizardPreset($this->field, $this->context, $wizardClass, $options);
    }
}
