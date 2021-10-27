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
 * Last modified: 2021.07.20 at 15:12
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;

use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\WizardAllowEditOption;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\WizardAllowNewOption;

/**
 * @deprecated will be removed in v12 use the option container instead!
 */
trait FieldPresetGenericWizardsTrait
{
    /**
     * Internal helper which is used to add the "edit record" wizard option to the Options::make definition.
     *
     * @param   array  $optionDefinition
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addAllowEditOptions(array $optionDefinition): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . WizardAllowEditOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new WizardAllowEditOption())->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "edit record" wizard option to the config array
     *
     * @param   array  $config   The configuration array to add the wizard to
     * @param   array  $options  The current fields options to check if the wizard was enabled
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addAllowEditConfig(array $config, array $options): array
    {
        (new WizardAllowEditOption())->applyConfig($config, $options);
        
        return $config;
    }
    
    /**
     * Internal helper which is used to add the "new record" wizard option to the Options::make definition.
     *
     * @param   array  $optionDefinition
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addAllowNewOptions(array $optionDefinition): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . WizardAllowNewOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new WizardAllowNewOption())->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "new record" wizard option to the config array
     *
     * @param   array  $config   The configuration array to add the wizard to
     * @param   array  $options  The current fields options to check if the wizard was enabled
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addAllowNewConfig(array $config, array $options): array
    {
        (new WizardAllowNewOption())->applyConfig($config, $options);
        
        return $config;
    }
}