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
 * Last modified: 2021.07.27 at 11:25
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

namespace LaborDigital\T3ba\Tool\FormEngine\Custom\Field;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\FormEngine\Custom\CustomAssetTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

class CustomFieldContext implements NoDiInterface
{
    use CustomAssetTrait;
    
    /**
     * @var \TYPO3\CMS\Core\Imaging\IconFactory
     */
    protected $iconFactory;
    
    /**
     * @var \TYPO3\CMS\Backend\Form\NodeFactory
     */
    protected $nodeFactory;
    
    /**
     * The raw data, stored on the root node element
     *
     * @var array
     */
    protected $rawData;
    
    /**
     * The real node in the form engine, that serves as a wrapper for our easier interface to form elements
     *
     * @var \LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldNode
     */
    protected $rootNode;
    
    /**
     * Default width value for a couple of elements like text
     *
     * @var int
     */
    protected $defaultInputWidth = 30;
    
    /**
     * Minimum width value for a couple of elements like text
     *
     * @var int
     */
    protected $minInputWidth = 10;
    
    /**
     * Maximum width value for a couple of elements like text
     *
     * @var int
     */
    protected $maxInputWidth = 50;
    
    /**
     * If this is set to false, the outer html elements around the form element,
     * including the field wizards will not be added to the result string!
     *
     * @var bool
     */
    protected $applyOuterWrap = true;
    
    /**
     * The result of the renderFieldInformation() method on the node itself
     *
     * @var array
     */
    protected $fieldInformation;
    
    /**
     * The result of the renderFieldWizard() method on the node itself
     *
     * @var array
     */
    protected $fieldWizard;
    
    /**
     * The result of the renderFieldControl() method on the node itself
     *
     * @var array
     */
    protected $fieldControl;
    
    /**
     * CustomFormElementContext constructor.
     *
     * @param   array  $injection  The list of properties that are injected into the context
     */
    public function __construct(array $injection)
    {
        foreach ($injection as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }
    
    /**
     * Returns the raw data received by the root node
     *
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }
    
    /**
     * Can be used to update the raw data of the rendering root node
     *
     * @param   array  $rawData
     *
     * @return CustomFieldContext
     */
    public function setRawData(array $rawData): self
    {
        $this->rawData = $rawData;
        
        return $this;
    }
    
    /**
     * Returns the form factory node, that serves as a wrapper for your field node
     *
     * @return \LaborDigital\T3ba\Tool\FormEngine\Custom\Field\CustomFieldNode
     */
    public function getRootNode(): CustomFieldNode
    {
        return $this->rootNode;
    }
    
    /**
     * Returns the default width value for a couple of elements like text
     *
     * @return int
     */
    public function getDefaultInputWidth(): int
    {
        return $this->defaultInputWidth;
    }
    
    /**
     * Returns the minimum width value for a couple of elements like text
     *
     * @return int
     */
    public function getMinInputWidth(): int
    {
        return $this->minInputWidth;
    }
    
    /**
     * Returns the maximum width value for a couple of elements like text
     *
     * @return int
     */
    public function getMaxInputWidth(): int
    {
        return $this->maxInputWidth;
    }
    
    /**
     * Returns the UID of the record this field is part of
     * Returns 0 if the record is not yet saved
     *
     * @return int
     */
    public function getRecordUid(): int
    {
        return (int)($this->rawData['databaseRow']['uid'] ?? 0);
    }
    
    /**
     * Returns the page id of the record this field is part of
     *
     * @return int
     */
    public function getRecordPid(): int
    {
        return (int)($this->rawData['effectivePid'] ?? 0);
    }
    
    /**
     * Returns true if the field was disabled, false if not
     *
     * @return bool
     * @todo this should be called "isReadOnly" instead.
     */
    public function isDisabled(): bool
    {
        return (bool)$this->rawData['parameterArray']['fieldConf']['config']['readOnly'];
    }
    
    /**
     * Returns the currently set value for this field
     *
     * @return array|mixed|null
     */
    public function getValue()
    {
        return $this->rawData['parameterArray']['itemFormElValue'] ?? null;
    }
    
    /**
     * Returns the complete database record this field is part of
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->rawData['databaseRow'] ?? [];
    }
    
    /**
     * Returns the name of the database table this field is part of
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->rawData['tableName'] ?? '';
    }
    
    /**
     * Returns the name of the database field in the record
     *
     * @return string
     */
    public function getFieldName(): string
    {
        return $this->rawData['fieldName'] ?? '';
    }
    
    /**
     * Returns the field name that should be put as "name" attribute of the HTML tag,
     * representing this field
     *
     * @return string
     */
    public function getRenderName(): string
    {
        return $this->rawData['parameterArray']['itemFormElName'] ?? '';
    }
    
    /**
     * The HTML ID that should be set for this field
     *
     * @return string
     */
    public function getRenderId(): string
    {
        return $this->rawData['parameterArray']['itemFormElID'] ?? '';
    }
    
    /**
     * Returns the prepared TCA configuration for this field
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->rawData['parameterArray']['fieldConf'] ?? [];
    }
    
    /**
     * Returns the list of additional options that were passed when the field
     * was applied using the fieldPreset applier.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->rawData['parameterArray']['fieldConf']['config']['t3ba'] ?? [];
    }
    
    /**
     * Can be used to return a single option, or returns the default value
     *
     * @param   array|string  $path     The key, or the path to look up
     * @param   null          $default  An optional default value to return if the key/path was not found in the options
     *                                  array
     *
     * @return array|mixed|null
     */
    public function getOption($path, $default = null)
    {
        return Arrays::getPath($this->getOptions(), $path, $default);
    }
    
    /**
     * Returns the registered class to handle the rendering for this field.
     * If this returns an empty string, the space-time-continuum will explode in around 30 seconds...
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->rawData['parameterArray']['fieldConf']['config']['t3baClass'] ?? '';
    }
    
    /**
     * Returns true if the outer html wrap around your form field will be rendered, false if not
     *
     * @return bool
     */
    public function isApplyOuterWrap(): bool
    {
        return $this->applyOuterWrap;
    }
    
    /**
     * Sets if the outer html wrap around your form field will be rendered or not
     *
     * @param   bool  $applyOuterWrap
     *
     * @return CustomFieldContext
     */
    public function setApplyOuterWrap(bool $applyOuterWrap): self
    {
        $this->applyOuterWrap = $applyOuterWrap;
        
        return $this;
    }
    
    /**
     * Calling this method disables the outer html wrap around your form field
     *
     * @return $this
     */
    public function disableOuterWrap(): self
    {
        $this->applyOuterWrap = false;
        
        return $this;
    }
    
    /**
     * Returns the current field information array generated by renderFieldInformation()
     *
     * @return array
     */
    public function getFieldInformation(): array
    {
        return $this->fieldInformation;
    }
    
    /**
     * Returns the html of the field information.
     * As long as isApplyOuterWrap() returns true, the html will be injected via the outer wrap automatically
     *
     * @return string
     */
    public function getFieldInformationHtml(): string
    {
        return $this->getFieldInformation()['html'] ?? '';
    }
    
    /**
     * Allows you to modify the field information array
     *
     * @param   array  $fieldInformation
     *
     * @return CustomFieldContext
     */
    public function setFieldInformation(array $fieldInformation): CustomFieldContext
    {
        $this->fieldInformation = $fieldInformation;
        
        return $this;
    }
    
    /**
     * Returns the currently set field wizards array, generated by renderFieldWizard()
     *
     * @return array
     */
    public function getFieldWizard(): array
    {
        return $this->fieldWizard;
    }
    
    /**
     * Returns the html of the field wizards.
     * As long as isApplyOuterWrap() returns true, the html will be injected via the outer wrap automatically
     *
     * @return string
     */
    public function getFieldWizardsHtml(): string
    {
        return $this->getFieldWizard()['html'] ?? '';
    }
    
    /**
     * Allows you to modify the result of the field wizard array
     *
     * @param   array  $fieldWizard
     *
     * @return CustomFieldContext
     */
    public function setFieldWizard(array $fieldWizard): CustomFieldContext
    {
        $this->fieldWizard = $fieldWizard;
        
        return $this;
    }
    
    /**
     * Returns the currently set field control array, generated by renderFieldControl()
     *
     * @return array
     */
    public function getFieldControl(): array
    {
        return $this->fieldControl;
    }
    
    /**
     * Returns the html of the field control elements.
     * As long as isApplyOuterWrap() returns true, the html will be injected via the outer wrap automatically
     *
     * @return string
     */
    public function getFieldControlHtml(): string
    {
        return $this->getFieldControl()['html'] ?? '';
    }
    
    /**
     * Allows you to modify the result of the field control array
     *
     * @param   array  $fieldControl
     *
     * @return CustomFieldContext
     */
    public function setFieldControl(array $fieldControl): CustomFieldContext
    {
        $this->fieldControl = $fieldControl;
        
        return $this;
    }
    
    /**
     * Returns the icon factory instance for the field
     *
     * @return \TYPO3\CMS\Core\Imaging\IconFactory
     */
    public function getIconFactory(): IconFactory
    {
        return $this->iconFactory;
    }
    
    /**
     * Returns the node factory instance for the field
     *
     * @return \TYPO3\CMS\Backend\Form\NodeFactory
     */
    public function getNodeFactory(): NodeFactory
    {
        return $this->nodeFactory;
    }
}
