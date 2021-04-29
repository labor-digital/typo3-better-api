<?php
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

namespace LaborDigital\T3BA\Tool\FormEngine\Custom\Field;

use LaborDigital\T3BA\Tool\FormEngine\Custom\CustomAssetTrait;
use Neunerlei\Arrays\Arrays;

class CustomFieldContext
{
    use CustomAssetTrait;
    
    /**
     * The raw data, stored on the root node element
     *
     * @var array
     */
    protected $rawData;
    
    /**
     * The real node in the form engine, that serves as a wrapper for our easier interface to form elements
     *
     * @var \LaborDigital\T3BA\Tool\FormEngine\Custom\Field\CustomFieldNode
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
     * @return \LaborDigital\T3BA\Tool\FormEngine\Custom\Field\CustomFieldNode
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
     *
     * @return int
     */
    public function getRecordUid(): int
    {
        return (int)($this->rawData['vanillaUid'] ?? 0);
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
    
}
