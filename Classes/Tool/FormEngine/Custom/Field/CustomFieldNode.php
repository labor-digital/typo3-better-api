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
 * Last modified: 2021.07.27 at 11:28
 */

declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 02:59
 */

namespace LaborDigital\T3ba\Tool\FormEngine\Custom\Field;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\FormEngine\CustomFieldPostProcessorEvent;
use LaborDigital\T3ba\Tool\FormEngine\Custom\CustomFormException;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\MathUtility;

class CustomFieldNode extends AbstractFormElement
{
    use ContainerAwareTrait;
    
    /**
     * Holds the current context to be able to update our local data if required
     *
     * @var \LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldContext
     */
    protected $context;
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3ba\Tool\FormEngine\Custom\CustomFormException
     * @todo this method should be refactored into smaller parts to make it easier to read and understand
     */
    public function render()
    {
        $this->context = $this->makeInstance(
            CustomFieldContext::class,
            [
                [
                    'rawData' => $this->data,
                    'iconFactory' => $this->iconFactory,
                    'rootNode' => $this,
                    'defaultInputWidth' => $this->defaultInputWidth,
                    'maxInputWidth' => $this->maxInputWidth,
                    'minInputWidth' => $this->minimumInputWidth,
                ],
            ]
        );
        
        // Validate if the class exists
        $className = $this->context->getClassName();
        if (! class_exists($className)) {
            throw new CustomFormException(
                'Could not render your field: ' . $this->context->getFieldName()
                . ' to use the custom field class: ' . $className
                . '. Because the class does not exist!');
        }
        
        $i = $this->makeInstance($className);
        
        if (is_callable([$i, 'provideDefaults'])) {
            (function (array $defaults) {
                $this->defaultFieldInformation = $defaults[0];
                $this->defaultFieldControl = $defaults[1];
                $this->defaultFieldWizard = $defaults[2];
            })($i->provideDefaults());
        }
        
        // Late binding of the data which requires the defaults to be set
        $this->context->setFieldInformation($this->renderFieldInformation());
        $this->context->setFieldWizard($this->renderFieldWizard());
        $this->context->setFieldControl($this->renderFieldControl());
        
        if (! $i instanceof CustomFieldInterface) {
            throw new CustomFormException(
                'Could not render your field: ' . $this->context->getFieldName()
                . " to use the custom field with class: $className. Because the class does not implement the required "
                . CustomFieldInterface::class . ' interface!');
        }
        
        $i->setContext($this->context);
        
        $result = $this->initializeResultArray();
        $result['html'] = $html = $i->render();
        
        $this->refreshData();
        
        $result = $this->mergeChildReturnIntoExistingResult($result, $this->context->getFieldInformation(), false);
        if (! $this->context->isDisabled()) {
            $result = $this->mergeChildReturnIntoExistingResult($result, $this->context->getFieldWizard(), false);
            $result = $this->mergeChildReturnIntoExistingResult($result, $this->context->getFieldControl(), false);
        }
        
        if ($this->context->isApplyOuterWrap()) {
            $config = $this->context->getConfig()['config'] ?? [];
            
            $size = $config['size'] ?? $this->context->getDefaultInputWidth();
            $size = MathUtility::forceIntegerInRange($size, $this->context->getMinInputWidth(), $this->context->getMaxInputWidth());
            $width = $this->formMaxWidth($size);
            
            $fieldInformationHtml = $this->context->getFieldInformationHtml();
            $wizardHtml = $this->context->getFieldWizardsHtml();
            $wizardHtml = empty($wizardHtml) || $this->context->isDisabled()
                ? '' : '<div class="form-wizards-items-bottom">' . $wizardHtml . '</div>';
            $controlHtml = $this->context->getFieldControlHtml();
            $controlHtml = empty($controlHtml) || $this->context->isDisabled()
                ? '' : '<div class="form-wizards-items-aside"><div class="btn-group">' . $controlHtml . '</div></div>';
            
            $result['html'] = <<<HTML
<div class="formengine-field-item">
    $fieldInformationHtml
    <div class="form-control-wrap" style="max-width: $width px;">
        <div class="form-wizards-wrap">
            <div class="form-wizards-element">
                $html
            </div>
            $controlHtml
            $wizardHtml
        </div>
    </div>
</div>
HTML;
        }
        
        $result['requireJsModules']
            = array_merge($result['requireJsModules'] ?? [], $this->context->getRequireJsModules());
        
        return $this->cs()->eventBus->dispatch(new CustomFieldPostProcessorEvent(
            $this->context,
            $i->filterResultArray($result)
        ))->getResult();
    }
    
    /**
     * Internal helper to allow the external world access to all protected methods of this object
     *
     * @param   string  $method
     * @param   array   $args
     *
     * @return mixed
     * @throws \LaborDigital\T3ba\Tool\FormEngine\Custom\CustomFormException
     */
    public function callMethod(string $method, array $args = [])
    {
        // Check if the method exists
        if (! method_exists($this, $method)) {
            throw new CustomFormException(
                'It is not allowed to call method: ' . $method
                . ' on the root node, because the method does not exist!');
        }
        
        // Make sure our data is up to data
        $this->refreshData();
        
        // Call the method
        return call_user_func_array([$this, $method], $args);
    }
    
    /**
     * The companion to __callMethod. Checks if a certain, protected method exists or not
     *
     * @param   string  $method
     *
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return method_exists($this, $method);
    }
    
    /**
     * Internal helper to synchronize the context's data back to this nodes data storage
     */
    public function refreshData(): void
    {
        $this->data = $this->context->getRawData();
    }
}
