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

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Backend\Form\NodeInterface;

class CustomWizardContext
{
    use CommonServiceLocatorTrait;
    
    /**
     * The raw data, given to the user func handler
     * @var array
     */
    protected $rawData;
    
    /**
     * The form element this wizard is attached to.
     * @var \TYPO3\CMS\Backend\Form\Element\AbstractFormElement
     */
    protected $formElement;
    
    /**
     * The value of the form field
     * @var mixed
     */
    protected $value;
    
    /**
     * CustomWizardContext constructor.
     *
     * @param array $injection
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
     * Returns the raw data received by the form element node
     * @return array
     */
    public function getRawData(): array
    {
        return $this->rawData;
    }
    
    /**
     * Returns the instance of the form element that has this wizard attached
     * @return \TYPO3\CMS\Backend\Form\NodeInterface
     */
    public function getFormElement(): NodeInterface
    {
        return $this->formElement;
    }
    
    /**
     * Returns the UID of the record this field is part of
     * @return int
     */
    public function getRecordUid(): int
    {
        return (int)Arrays::getPath($this->rawData, ['databaseRow', 'uid']);
    }
    
    /**
     * Returns the page id of the record this field is part of
     * @return int
     */
    public function getRecordPid(): int
    {
        return (int)Arrays::getPath($this->rawData, ['databaseRow', 'pid']);
    }
    
    /**
     * Returns the currently set value for this field
     * @return array|mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Returns the complete database record this field is part of
     * @return array
     */
    public function getRow(): array
    {
        return Arrays::getPath($this->rawData, 'databaseRow', []);
    }
    
    /**
     * Returns the name of the database table this field is part of
     * @return string
     */
    public function getTableName(): string
    {
        return Arrays::getPath($this->rawData, ['tableName'], '');
    }
    
    /**
     * Returns the name of the database field in the record
     * @return string
     */
    public function getFieldName(): string
    {
        return Arrays::getPath($this->rawData, ['fieldName'], '');
    }
    
    /**
     * Returns the list of additional options that were passed when the field
     * was applied using the fieldPreset applier.
     *
     * @return array
     */
    public function getOptions(): array
    {
        $options = Arrays::getPath($this->rawData, ['renderData', 'fieldWizardOptions'], []);
        if (!is_array($options)) {
            return [];
        }
        return $options;
    }
    
    /**
     * Can be used to return a single option, or returns the default value
     *
     * @param array|string $path    The key, or the path to look up
     * @param null         $default An optional default value to return if the key/path was not found in the options
     *                              array
     *
     * @return array|mixed|null
     */
    public function getOption($path, $default = null)
    {
        return Arrays::getPath($this->getOptions(), $path, $default);
    }
    
    /**
     * Returns the field name that should be put as "name" attribute of the HTML tag,
     * representing this field
     * @return string
     */
    public function getRenderName(): string
    {
        return Arrays::getPath($this->rawData, ['parameterArray', 'itemFormElName'], '');
    }
    
    /**
     * The HTML ID that should be set for this field
     * @return string
     */
    public function getRenderId(): string
    {
        return Arrays::getPath($this->rawData, ['parameterArray', 'itemFormElID'], '');
    }
    
    /**
     * Returns the prepared TCA configuration for this field
     * @return array
     */
    public function getConfig(): array
    {
        return Arrays::getPath($this->rawData, ['parameterArray', 'fieldConf'], []);
    }
}
