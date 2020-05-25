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
 * Last modified: 2020.03.19 at 03:01
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\CustomWizard;

use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class CustomWizardNode extends AbstractFormElement
{
    use CommonServiceLocatorTrait;
    
    /**
     * @inheritDoc
     */
    public function render()
    {
        // Create the context object
        $context = $this->getInstanceOf(CustomWizardContext::class, [
            [
                'rawData'     => $this->data,
                'formElement' => $this,
                'value'       => $this->extractValueFromRow($this->data),
            ],
        ]);
        
        // Get the controller instance
        $class = $context->getOption('customWizardClass');
        if (empty($class)) {
            throw new BackendFormException('Could not create the custom wizard class, because it is not configured for this wizard!');
        }
        /** @var \LaborDigital\Typo3BetterApi\BackendForms\CustomWizard\CustomWizardInterface $i */
        $i = $this->getInstanceOf($class);
        
        // Call the controller
        $result = $i->render($context);
        
        // Build result
        $resultArray = $this->initializeResultArray();
        $resultArray['html'] = $result;
        return $resultArray;
    }
    
    /**
     * Internal helper to extract the value either from the row, from the given flex form path.
     *
     * @param array $config
     *
     * @return array|mixed|null
     */
    protected function extractValueFromRow(array $config)
    {
        $field = $config['fieldName'];
        $row = $config['databaseRow'];
        return empty($config['flexFormPath']) ? $row[$field] :
            Arrays::getPath($row[$field], $config['flexFormPath'], null, '/');
    }
}
