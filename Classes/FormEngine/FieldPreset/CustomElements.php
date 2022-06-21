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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3ba\FormEngine\FieldPreset;

use LaborDigital\T3ba\FormEngine\Field\InformationField;
use LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldPresetTrait;
use LaborDigital\T3ba\Tool\FormEngine\Custom\Wizard\CustomWizardPresetTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset;

class CustomElements extends AbstractFieldPreset
{
    use CustomFieldPresetTrait;
    use CustomWizardPresetTrait;
    
    /**
     * An information field can be used to provide additional information.
     * It is treated as a "none" field and has no DB representation attached to it.
     * Basically free-text to read for the editor in the backend.
     *
     * @param   string  $labelOrTemplate  Either a string, a translation label, Fluid-HTML code or the reference to a template file like EXT:ext_key...
     *
     * @return void
     */
    public function applyInformation(string $labelOrTemplate): void
    {
        $applyer = $this->field->applyPreset();
        $applyer->none();
        $applyer->customField(InformationField::class, ['lot' => $labelOrTemplate]);
    }
    
    /**
     * Can be used to configure a generic, custom form element.
     *
     * @param   string  $formElementClass  The class name of the custom element you want to register.
     *                                     The class has to implement the CustomElementInterface interface
     * @param   array   $options           Any options you want to specify for your custom element
     */
    public function applyCustomField(string $formElementClass, array $options = []): void
    {
        $this->applyCustomElementPreset($formElementClass, $options);
    }
    
    /**
     * Can be used to configure a generic, custom wizard class.
     *
     * @param   string  $wizardClass  The class name of the custom wizard you want to register.
     *                                The class has to implement the CustomWizardInterface interface
     * @param   array   $options      Any options you want to specify for your custom wizard
     *                                Generic options on all wizards are:
     *                                - before array|string: A list of other wizards that should be
     *                                displayed after this wizard
     *                                - after array|string: A list of other wizards that should be
     *                                displayed before this wizard
     *                                - wizardId string: Can be used to manually set the wizard id.
     *                                If left empty the id will be automatically created.
     */
    public function applyCustomWizard(string $wizardClass, array $options = []): void
    {
        $this->applyCustomWizardPreset($wizardClass, $options);
    }
}
