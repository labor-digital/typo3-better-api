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

use LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;

class TcaFieldFlexForm
{

    /**
     * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField
     */
    protected $field;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext
     */
    protected $context;

    /**
     * The list of flex forms by their structure key
     *
     * @var FlexForm[]
     */
    protected $structures = [];

    /**
     * TcaFieldFlexForm constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaField  $field
     * @param   array                                                        $config
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext      $context
     */
    public function __construct(TcaField $field, array $config, ExtConfigContext $context)
    {
        $this->field   = $field;
        $this->config  = $config;
        $this->context = $context;
    }

    /**
     * Returns the id of the field, or fields (comma separated) that are responsible for
     * determining the the type of flex form structure to use on this field.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Flex.html#pointing-to-a-data-structure
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Flex.html#ds-pointerfield
     *
     * @return string
     */
    public function getFormSelectorField(): string
    {
        return Arrays::getPath($this->config, ['config', 'ds_pointerField'], '');
    }

    /**
     * Can be used to set the id of the field, or fields (comma separated) that are responsible for
     * determining the the type of flex form structure to use on this field.
     *
     * If set to NULL the configuration will be removed
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Flex.html#pointing-to-a-data-structure
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/ColumnsConfig/Type/Flex.html#ds-pointerfield
     *
     *
     * @param   string|null  $field
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaFieldFlexForm
     */
    public function setFormSelectorField(?string $field): TcaFieldFlexForm
    {
        if (is_null($field)) {
            unset($this->config['ds_pointerField']);
        }
        $this->config['config']['ds_pointerField'] = $field;

        return $this;
    }

    /**
     * Use this method to get the flex form layout for this field.
     *
     * By default the structure is "default" (who would have thought...) and should be everything you need on a daily basis.
     * If there are multiple structures of different flex forms on this field, you may want to set it's identifier as
     * parameter.
     *
     * If the requested structure does not exist, it will automatically created for you.
     *
     * @param   string  $structure  Optional identifier for a flex form structure to get the form representation for
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     */
    public function getForm(string $structure = 'default'): FlexForm
    {
        // Return existing flex form objects
        if (isset($this->structures[$structure])) {
            return $this->structures[$structure];
        }

        // Check if we have the structure for this form
        $this->config['config']['ds'][$structure] = $definition
            = Arrays::hasPath($this->config, ['config', 'ds', $structure]) ?
            $this->config['config']['ds'][$structure]
            : '<T3DataStructure><sheets type=\'array\'></sheets></T3DataStructure>';

        // Generate a new flex form instance
        return $this->structures[$structure]
            = FlexForm::makeInstance($definition, $this->context, $this->field);
    }

    /**
     * Returns true if this field has a flex form configuration for the given structure
     *
     * @param   string  $structure
     *
     * @return bool
     */
    public function hasForm(string $structure = 'default'): bool
    {
        return Arrays::hasPath($this->config, ['config', 'ds', $structure]);
    }

    /**
     * Returns the keys of all flex form structures that are registered on this field.
     *
     * @return array
     */
    public function getFormStructures(): array
    {
        return array_keys(Arrays::getPath($this->config, ['config', 'ds'], []));
    }

    /**
     * Removes a given structure definition from the current flex form configuration.
     *
     * @param   string  $structure
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaFieldFlexForm
     */
    public function removeForm(string $structure): TcaFieldFlexForm
    {
        unset($this->structures[$structure]);
        Arrays::removePath($this->config, ['config', 'ds', $structure]);

        return $this;
    }

    /**
     * Internal helper to dump the current flex form configuration into a file and to
     * register it in the tca configuration of the field
     *
     * @return array
     */
    public function __build(): array
    {
        // Update the ds list
        foreach ($this->structures as $structure => $config) {
            // Make sure we supply a relative path...
            $filename                                 = $config->__build()->getFileName();
            $this->config['config']['ds'][$structure] = 'FILE:' . $filename;
        }

        // Return the config
        return $this->config;
    }
}
