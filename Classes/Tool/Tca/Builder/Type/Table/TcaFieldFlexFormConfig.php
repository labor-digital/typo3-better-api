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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table;


use Doctrine\DBAL\Types\TextType;
use LaborDigital\T3ba\Tool\Sql\SqlFieldLength;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\Factory;

class TcaFieldFlexFormConfig
{
    
    /**
     * @var array
     */
    protected $config;
    
    /**
     * @var TcaField
     */
    protected $field;
    
    /**
     * @var Factory
     */
    protected $factory;
    
    /**
     * The list of flex forms by their structure key
     *
     * @var Flex[]
     */
    protected $structures = [];
    
    public function __construct(TcaField $field, array &$fieldConfig, Factory $factory)
    {
        $this->field = $field;
        $this->factory = $factory;
        $this->config = &$fieldConfig;
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
        return $this->config['config']['ds_pointerField'] ?? '';
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
     * @param   string|int|AbstractField|null  $field
     *
     * @return $this
     */
    public function setFormSelectorField($field): self
    {
        if ($field === null) {
            unset($this->config['ds_pointerField']);
            
            return $this;
        }
        
        if ($field instanceof AbstractField) {
            $field = $field->getId();
        }
        
        $this->config['config']['ds_pointerField'] = $field;
        
        return $this;
    }
    
    /**
     * Use this method to get the flex form layout for this field.
     *
     * By default the structure is "default" (who would have thought?) and should be everything you need on a daily
     * basis. If there are multiple structures of different flex forms on this field, you may want to set it's
     * identifier as parameter.
     *
     * If the requested structure does not exist, it will automatically created for you.
     *
     * @param   string|null  $structure  Optional identifier for a flex form structure to get
     *                                   the form representation for. Otherwise "default" is used
     *
     * @return Flex
     */
    public function getStructure(?string $structure = null): Flex
    {
        $structure = $structure ?? 'default';
        
        // Return existing flex form objects
        if (isset($this->structures[$structure])) {
            return $this->structures[$structure];
        }
        
        // Check if we have the structure for this form
        $definition = $this->config['config']['ds'][$structure] ?? '';
        if (empty($definition)) {
            $definition = '<T3DataStructure><sheets type=\'array\'></sheets></T3DataStructure>';
        }
        
        // Generate a new flex form instance
        $i = $this->factory->create($this->field);
        $this->factory->initialize($i, $definition);
        
        return $this->structures[$structure] = $i;
    }
    
    /**
     * Returns true if this field has a flex form configuration for the given structure
     *
     * @param   string|null  $structure  Optional identifier for a flex form structure to
     *                                   check for. Otherwise "default" is used
     *
     * @return bool
     */
    public function hasStructure(?string $structure = null): bool
    {
        return isset($this->config['config']['ds'][$structure ?? 'default']);
    }
    
    /**
     * Returns the keys of all flex form structures that are registered on this field.
     *
     * @return array
     */
    public function getStructureNames(): array
    {
        return array_unique(
            array_merge(
                array_keys($this->structures),
                array_keys($this->config['config']['ds'] ?? [])
            )
        );
    }
    
    /**
     * Internal helper to dump the configuration into the given tca field configuration array
     *
     * @param   array  $config
     *
     * @internal
     * @private
     */
    public function dump(array &$config): void
    {
        // Make sure we are rendering as flex form
        $config['config']['type'] = 'flex';
        unset($config['config']['renderType']);
        $this->field->getColumn()->setType(new TextType())->setLength(SqlFieldLength::MEDIUM_TEXT);
        
        $dumper = $this->field->getRoot()->getContext()->cs()->flexFormDumper;
        foreach ($this->structures as $k => $flexForm) {
            $config['config']['ds'][$k] = 'FILE:' . $dumper->dumpToFile($flexForm);
        }
        
    }
}
