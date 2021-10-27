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
 * Last modified: 2021.07.20 at 15:09
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;

use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\BasePidOption;

/**
 * @deprecated will be removed in v12 use the option container instead!
 */
trait FieldPresetBasePidTrait
{
    
    /**
     * Internal helper to apply the "basePid" config option to the Options::make definition.
     * BasePid can be used to limit group or select fields to a certain page
     *
     * @param   array  $optionDefinition
     * @param   bool   $withMapping  Allow the usage of "basePid" option to be an array of tableName -> basePids
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addBasePidOptions(array $optionDefinition, bool $withMapping = false): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . BasePidOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        $i = new BasePidOption($withMapping);
        $i->initialize($this->context);
        $i->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "basePid" config option to the config array of the field
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addBasePidConfig(array $config, array $options): array
    {
        (new BasePidOption())->applyConfig($config, $options);
        
        return $config;
    }
}