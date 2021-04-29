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
 * Last modified: 2020.03.19 at 02:52
 */

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Field;

use LaborDigital\T3BA\Tool\FormEngine\Custom\CustomFormElementTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractCustomField implements CustomFieldInterface
{
    use CustomFormElementTrait {
        enhanceTemplateData as enhanceTemplateDataRoot;
    }
    
    /**
     * The context object to work with
     *
     * @var CustomFieldContext
     */
    protected $context;
    
    /**
     * @inheritDoc
     */
    public function setContext(CustomFieldContext $context): void
    {
        $this->context = $context;
    }
    
    /**
     * Your input field, like your custom <input type="text"...> has to have some quite
     * specific and extensive attributes in order to work correctly with the form engine of typo3's backend.
     *
     * This method will return the complete string of those attributes so you can apply them into your template
     * without thinking about most of the internals.
     *
     * ATTENTION: This helper sets the ID and the CLASS html attributes. If you want to change or add, lets say
     * a class use the $mergeAttributes like ["class" => ["myClass"]] to supply your additional class to the built
     * output. You can't specify the class attribute twice, that will not be parsed correctly by the browser!
     *
     * @param   array  $mergeAttributes
     *
     * @return string
     * @throws \JsonException
     */
    protected function getInputAttributes(array $mergeAttributes = []): string
    {
        $config = $this->context->getConfig()['config'] ?? [];
        
        // Build required attribute values
        $jsonValidation = $this->context->getRootNode()->callMethod('getValidationDataAsJsonString', [$config]);
        $evalList = implode(',', array_unique(Arrays::makeFromStringList(Arrays::getPath($config, 'eval', ''))));
        $isIn = trim(Arrays::getPath($config, 'is_in', ''));
        
        // Build default attributes
        $attributes = [
            'id' => $this->context->getRenderId(),
            'class' => implode(' ', [
                'form-control',
                't3js-clearable',
                'hasDefaultValue',
            ]),
            'data-formengine-validation-rules' => $jsonValidation,
            'data-formengine-input-params' => json_encode([
                'field' => $this->context->getRenderName(),
                'evalList' => $evalList,
                'is_in' => $isIn,
            ], JSON_THROW_ON_ERROR),
            'data-formengine-input-name' => $this->context->getRenderName(),
        ];
        
        // Merge and implode attributes
        $attributes = Arrays::merge($attributes, $mergeAttributes);
        
        return GeneralUtility::implodeAttributes($attributes, true);
    }
    
    /**
     * Similar to your input field, the hidden field, which stores the real data
     * also has to have quite specific attributes. This method returns those attributes in the
     * same manner as getInputAttributes().
     *
     * It sets the NAME and the VALUE attribute by default.
     *
     * @param   array  $mergeAttributes
     *
     * @return string
     */
    protected function getHiddenAttributes(array $mergeAttributes = []): string
    {
        // Make sure we are not breaking the backend
        $value = $this->context->getValue();
        if (is_array($value)) {
            if (count($value) === 1) {
                $value = reset($value);
            } else {
                $value = implode(', ', $value);
            }
        }
        
        // Build default attributes
        $attributes = [
            'name' => $this->context->getRenderName(),
            'value' => $value,
        ];
        
        // Merge and implode attributes
        $attributes = Arrays::merge($attributes, $mergeAttributes);
        
        return GeneralUtility::implodeAttributes($attributes, true);
    }
    
    /**
     * It's not enough to add a new value field like <input type="text"...> to your template.
     * You will also have to add an additional <input type="hidden"...> field which is used as a data-holder
     * by typo3's form engine.
     *
     * To save you most of the hazel you may use this method to get the already prepared html for said hidden field.
     *
     * Also see getHiddenAttributes() for how to adjust the attributes of the generated input
     *
     * @param   array  $mergeAttributes
     *
     * @return string
     */
    protected function getHiddenHtml(array $mergeAttributes = []): string
    {
        $attr = $this->getHiddenAttributes($mergeAttributes);
        
        return "<input type=\"hidden\" $attr />";
    }
    
    /**
     * @inheritDoc
     */
    protected function enhanceTemplateData(array $data): array
    {
        $data = $this->enhanceTemplateDataRoot($data);
        
        if (! isset($data['inputAttributes'])) {
            $data['inputAttributes'] = $this->getInputAttributes();
        }
        
        if (! isset($data['hiddenField'])) {
            $data['hiddenField'] = $this->getHiddenHtml();
        }
        
        if (! isset($data['hiddenAttributes'])) {
            $data['hiddenAttributes'] = $this->getHiddenAttributes();
        }
        
        return $data;
    }
}
