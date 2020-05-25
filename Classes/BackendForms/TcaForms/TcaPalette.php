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
 * Last modified: 2020.03.19 at 03:03
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\TcaForms;

use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer;

class TcaPalette extends AbstractFormContainer
{
    use LayoutMetaTrait;
    
    /**
     * @inheritDoc
     */
    public function setLabel(?string $label)
    {
        // Make sure the label get's printed when the showItem string is build
        $this->layoutMeta[0] = $label;
        return parent::setLabel($label);
    }
}
