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
 * Last modified: 2021.07.20 at 15:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;


use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxItemOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MinMaxLengthOption;

/**
 * @deprecated will be removed in v12 use the option container instead!
 */
trait FieldPresetMinMaxTrait
{
    
    /**
     * Provides the option definition for the minItems and maxItems options
     *
     * @param   array  $optionDefinition
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMinMaxItemOptions(array $optionDefinition, array $options = []): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . MinMaxItemOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new MinMaxItemOption())->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMinMaxItemConfig(array $config, array $options): array
    {
        (new MinMaxItemOption())->applyConfig($config, $options);
        
        return $config;
    }
    
    /**
     * Internal helper to add the "maxLength" config option to the Options::make definition.
     * This makes only sense for "input" type fields
     *
     * @param   array  $optionsDefinition
     * @param   int    $defaultMax  The default value of the maximal input length
     * @param   int    $defaultMin  The default value for the minimal input length
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMinMaxLengthOptions(
        array $optionsDefinition,
        int $defaultMax = 512,
        int $defaultMin = 0
    ): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . MinMaxLengthOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new MinMaxLengthOption($defaultMax, $defaultMin))->addDefinition($optionsDefinition);
        
        return $optionsDefinition;
    }
    
    /**
     * Internal helper to apply the "maxLength" configuration to the config array of a input field
     *
     * @param   array  $config
     * @param   array  $options
     * @param   bool   $addSqlStatement  If set to true the sql statement of this column will automatically be
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMaxLengthConfig(array $config, array $options, bool $addSqlStatement = false): array
    {
        $i = new MinMaxLengthOption(512, 0, $addSqlStatement);
        $i->initialize($this->context);
        $i->applyConfig($config, $options);
        
        return $config;
    }
}