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
 * Last modified: 2021.10.25 at 12:50
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;

use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\DefaultOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\InputSizeOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\LegacyReadOnlyOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\PlaceholderOption;

/**
 * @deprecated Temporary trait until v12 DON'T use it!
 */
trait FieldPresetOptionDeprecationTrait
{
    
    /**
     * Internal helper which is used to add the "readOnly" option to the field configuration
     *
     * @param   array  $optionDefinition
     *
     * @return array
     * @deprecated will be removed in v12 use the setReadOnly() method on a field instead
     */
    protected function addReadOnlyOptions(array $optionDefinition): array
    {
        (new LegacyReadOnlyOption())->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to add the "read only" configuration to the config array if the matching option was set
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the setReadOnly() method on a field instead
     */
    protected function addReadOnlyConfig(array $config, array $options): array
    {
        (new LegacyReadOnlyOption())->applyConfig($config, $options);
        
        return $config;
    }
    
    /**
     * Adds the option to configure the "size" of an "input" field either using a percentage or integer value.
     *
     * @param   array        $optionDefinition
     * @param   string|null  $optionName  default: "size", can be set to another name as well, (e.g. cols)
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addInputSizeOption(array $optionDefinition, ?string $optionName = null): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . InputSizeOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new InputSizeOption($optionName))->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to add a placeholder definition to the option array
     *
     * @param   array        $optionDefinition
     * @param   string|null  $defaultPlaceholder  Optional default placeholder value
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addPlaceholderOption(array $optionDefinition, ?string $defaultPlaceholder = null): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . PlaceholderOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new PlaceholderOption($defaultPlaceholder))->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Adds the placeholder config option to the config array of the field
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addPlaceholderConfig(array $config, array $options): array
    {
        (new PlaceholderOption(null))->applyConfig($config, $options);
        
        return $config;
    }
    
    /**
     * Provides the option definition for the "default" configuration
     *
     * @param   array       $optionDefinition
     * @param   array|null  $type
     * @param   mixed       $default
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addDefaultOptions(array $optionDefinition, ?array $type = null, $default = ''): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . DefaultOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new DefaultOption($default, $type))->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Adds the default configuration based on the given options
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addDefaultConfig(array $config, array $options): array
    {
        (new DefaultOption())->applyConfig($config, $options);
        
        return $config;
    }
}