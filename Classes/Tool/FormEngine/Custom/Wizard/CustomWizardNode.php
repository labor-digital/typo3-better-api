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
 * Last modified: 2021.06.21 at 13:19
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

namespace LaborDigital\T3ba\Tool\FormEngine\Custom\Wizard;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\FormEngine\Custom\CustomFormException;
use LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldInterface;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class CustomWizardNode extends AbstractFormElement
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Tool\FormEngine\Custom\CustomFormException
     */
    public function render()
    {
        // Create the context object
        $context = $this->makeInstance(
            CustomWizardContext::class, [
                [
                    'rawData' => $this->data,
                    'formElement' => $this,
                    'value' => $this->extractValueFromRow($this->data),
                ],
            ]
        );
        
        $className = $context->getOption('className');
        if (empty($className)) {
            throw new CustomFormException(
                'Could not create the custom wizard, because it has no configured class!');
        }
        
        $i = $this->getServiceOrInstance($className);
        
        if (! $i instanceof CustomWizardInterface) {
            throw new CustomFormException(
                'Could not render your field: ' . $context->getFieldName()
                . " to use the custom field with class: $className. Because the class does not implement the required "
                . CustomFieldInterface::class . ' interface!');
        }
        
        $i->setContext($context);
        
        $resultArray = $this->initializeResultArray();
        $resultArray['html'] = $i->render();
        
        return $i->filterResultArray($resultArray);
    }
    
    /**
     * Internal helper to extract the value either from the row, from the given flex form path.
     *
     * @param   array  $config
     *
     * @return array|mixed|null
     */
    protected function extractValueFromRow(array $config)
    {
        $field = $config['fieldName'];
        $row = $config['databaseRow'];
        
        return empty($config['flexFormPath'])
            ? $row[$field]
            : Arrays::getPath($row[$field], $config['flexFormPath'], null, '/');
    }
}
