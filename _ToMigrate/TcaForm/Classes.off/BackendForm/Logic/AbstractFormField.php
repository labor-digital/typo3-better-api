<?php
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
 * Last modified: 2020.05.24 at 11:34
 */

namespace LaborDigital\T3BA\ExtConfig\BackendForm\Logic;

use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionCollectorTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\Table\Preset\FieldPresetApplier;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

abstract class AbstractFormField extends AbstractFormElement
{
    use DataHandlerActionCollectorTrait;
    use FormDisplayConditionTrait;
    
    /**
     * @inheritDoc
     */
    public function __construct(FormNode $node, AbstractForm $form, array $tca)
    {
        parent::__construct($node, $form);
        $this->config = $tca;
    }
    
    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        $label = parent::getLabel();
        if (empty($label)) {
            return Inflector::toHuman($this->getId());
        }
        
        return $label;
    }
    
    /**
     * Set this to true, the form will reload itself after the value of this column was updated
     *
     * @param   bool  $state
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#onchange
     */
    public function setReloadOnChange(bool $state = true): self
    {
        $this->config['onChange'] = $state ? 'reload' : '';
        
        return $this;
    }
    
    /**
     * Returns true if the field should reload itself after an update, false if not
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#onchange
     *
     * @return bool
     */
    public function doesReloadOnChange(): bool
    {
        return $this->config['onChange'] === 'reload';
    }
    
    /**
     * If set, all backend users are prevented from editing the field unless they are members of a backend user group
     * with this field added as an “Allowed Excludefield” (or “admin” user).
     *
     * @param   bool  $state
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#exclude
     */
    public function setExclude(bool $state = true): self
    {
        $this->config['exclude'] = $state;
        
        return $this;
    }
    
    /**
     * If true all backend users are prevented from editing the field unless they are members of a backend user group
     * with this field added as an “Allowed Excludefield” (or “admin” user).
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#exclude
     *
     * @return bool
     */
    public function isExclude(): bool
    {
        return (bool)$this->config['exclude'];
    }
    
    /**
     * Sets if a field is read only or not
     * This property affects only the display. It is still possible to write to those fields when using the DataHandler
     *
     * @param   bool  $state
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Check.html#readonly
     */
    public function setReadOnly(bool $state = true): self
    {
        if ($state) {
            $this->config['config']['readOnly'] = true;
        } else {
            unset($this->config['config']['readOnly']);
        }
        
        return $this;
    }
    
    /**
     * Returns true if the field is configured to be read only, false if not
     *
     * @return bool
     */
    public function isReadOnly(): bool
    {
        return (bool)$this->config['config']['readOnly'];
    }
    
    /**
     * Can be used to set a field description text between the label and the input field.
     * Can contain html
     *
     * @param   string  $info  The text to set as description for this field
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/FormEngine/Rendering/Index.html#formengine-rendering-nodeexpansion
     */
    public function setDescription(string $info): self
    {
        $this->config['description'] = $info;
        
        return $this;
    }
    
    /**
     * Returns the currently set field information, or an empty string if there is none
     *
     * @return string
     */
    public function getDescription(): string
    {
        return (string)($this->config['description'] ?? '');
    }
    
    /**
     * Use the given object to apply presets to the given field.
     * This makes it a lot easier to configure your table fields, without the hassle of doing the configuration over
     * and over again.
     */
    public function applyPreset(): FieldPresetApplier
    {
        $context = $this->form->getContext();
        $applier = $context->getInstanceOf(FieldPresetApplier::class);
        $applier->__setField($this, $context);
        
        return $applier;
    }
    
    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = parent::getRaw();
        
        // Transform some keys into real typo3 translation keys
        // Because typo does not handle those elements using the default translation method...
        unset($raw['label']);
        $translator   = $this->form->getContext()->Translation();
        $raw['label'] = $translator->getTranslationKeyMaybe($this->getLabel());
        if (is_array($raw['config'])) {
            foreach (['default', 'placeholder'] as $k) {
                if (isset($raw['config'][$k])) {
                    $raw['config'][$k] = $translator->getTranslationKeyMaybe($raw['config'][$k]);
                }
            }
        }
        
        // Add the data handler actions
        $handlers = $this->__getDataHandlerActionHandlers();
        if (! empty($handlers)) {
            $raw['dataHandlerActions'] = $handlers['@table'];
        }
        
        // Done
        return $raw;
    }
    
    /**
     * Similar to setRaw() but will merge the given array of key/value pairs instead of
     * overwriting the original configuration.
     *
     * This method supports TYPO3's syntax of removing values from the current config if __UNSET is set as key
     *
     * @param   array  $rawInput
     *
     * @return $this
     */
    public function mergeRaw(array $rawInput): self
    {
        $this->setRaw(Arrays::merge($this->config, $rawInput, 'allowRemoval'));
        
        return $this;
    }
    
    /**
     * Lets you add additional entries to the field's "config" array.
     * This will merge your input with the existing value!
     *
     * This method supports TYPO3's syntax of removing values from the current config if __UNSET is set as key
     *
     * @param   string|array  $key    Either a key for the value to set, or a list of key => value pairs
     * @param   mixed         $value  If $key is an array, this is ignored. Otherwise this will be set as value for $key
     *
     * @return $this
     */
    public function addConfig($key, $value = null): self
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }
        $this->mergeRaw(['config' => $key]);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHandlerTableName(): string
    {
        return '@table';
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHandlerFieldConstraints(): array
    {
        return [];
    }
}
