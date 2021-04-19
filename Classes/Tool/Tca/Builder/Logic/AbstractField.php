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

namespace LaborDigital\T3BA\Tool\Tca\Builder\Logic;

use LaborDigital\T3BA\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetApplier;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\Traits\DisplayConditionTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

abstract class AbstractField extends AbstractElement
{
    use DataHookCollectorTrait;
    use DisplayConditionTrait;

    /**
     * Additional configuration for the data hook registration
     *
     * @var array
     */
    protected $dataHookOptions = [];

    /**
     * @inheritDoc
     */
    public function __construct(Node $node, AbstractForm $form)
    {
        parent::__construct($node, $form);
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
    public function setReloadOnChange(bool $state = true)
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
    public function setExclude(bool $state = true)
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
    public function setReadOnly(bool $state = true)
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
    public function setDescription(string $info)
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
     * Completely overrides the configuration of this field with the configuration of another field.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractField  $field
     *
     * @return $this
     */
    public function inheritFrom(AbstractField $field)
    {
        $this->config = $field->config;
        $this->label  = $field->label;
        $this->loadDataHooks([DataHookTypes::TCA_DATA_HOOK_KEY => $field->getRegisteredDataHooks()]);

        return $this;
    }

    /**
     * Use the given object to apply presets to the given field.
     * This makes it a lot easier to configure your table fields, without the hassle of doing the configuration over
     * and over again.
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\FieldPreset\FieldPresetApplier|\LaborDigital\T3BA\Tool\Tca\Builder\FieldPresetAutocompleteHelper
     */
    public function applyPreset()
    {
        $context = $this->form->getContext();
        /** @var FieldPresetApplier $applier */
        $applier = $context->getParent()->getParentContext()->di()->getService(FieldPresetApplier::class);
        $applier->configureField($this, $context);

        return $applier;
    }


    /**
     * @inheritDoc
     */
    public function setRaw(array $raw)
    {
        $this->loadDataHooks($raw);

        return parent::setRaw($raw);
    }

    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = parent::getRaw();

        // Transform some keys into real typo3 translation keys
        // Because typo does not handle those elements using the default translation method...
        $translator   = $this->form->getContext()->cs()->translator;
        $raw['label'] = $translator->getLabelKey($this->getLabel());
        if (is_array($raw['config'])) {
            foreach (['default', 'placeholder'] as $k) {
                if (isset($raw['config'][$k])) {
                    $raw['config'][$k] = $translator->getLabelKey((string)$raw['config'][$k]);
                }
            }
        }

        $this->dumpDataHooks($raw);

        // Done
        return $raw;
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
    public function addConfig($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }
        $this->config['config']
            = Arrays::merge($this->config['config'] ?? [], $key, 'allowRemoval');

        return $this;
    }

    /**
     * Returns the currently set, additional options for the data hook registration.
     *
     * @return array
     */
    public function getDataHookOptions(): array
    {
        return $this->dataHookOptions;
    }

    /**
     * Allows to set additional options for the data hook registration.
     *
     * @param   array  $dataHookOptions
     *
     * @return AbstractField
     */
    public function setDataHookOptions(array $dataHookOptions): AbstractField
    {
        $this->dataHookOptions = $dataHookOptions;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getDataHookTableFieldConstraints(): array
    {
        return [];
    }

    /**
     * Provides the currently set additional data hook options
     *
     * @return array
     */
    protected function additionalDataHookOptions(): array
    {
        return $this->dataHookOptions;
    }
}
