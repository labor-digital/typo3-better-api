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

namespace LaborDigital\Typo3BetterApi\BackendForms\FormPresets;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface FormPresetInterface
{
    
    /**
     * This is used to inject the form field the next preset method should refer to
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormField  $field
     *
     * @return void
     */
    public function setField(AbstractFormField $field);
    
    /**
     * This is used to inject the extConfig context object for the field that will be configured next
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext  $context
     *
     * @return mixed
     */
    public function setContext(ExtConfigContext $context);
}
