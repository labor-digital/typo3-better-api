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
 * Last modified: 2021.07.20 at 14:49
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;


use LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\MmTableOption;

/**
 * @deprecated will be removed in v12 use the option container instead!
 */
trait FieldPresetMmTrait
{
    /**
     * @param   array  $optionDefinition
     * @param   bool   $withOpposite
     *
     * @return array
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMmTableOptions(array $optionDefinition, bool $withOpposite = true): array
    {
        trigger_error(
            'Deprecated usage of: ' . get_called_class() . '::' . __FUNCTION__ . '() you should use: ' .
            get_called_class() . '::prepareOptions([new ' . MmTableOption::class . '()])->apply($config, $options); instead!',
            E_USER_DEPRECATED
        );
        
        (new MmTableOption($withOpposite ? 'pages' : null))->addDefinition($optionDefinition);
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to configure an mm table for the current field.
     *
     * @param   array  $config  The current "config" array of the field, to add the mm table to
     * @param   array  $options
     *
     * @return array
     *
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMmTableConfig(array $config, array $options): array
    {
        $i = new MmTableOption();
        $i->initialize($this->context);
        $i->applyConfig($config, $options);
        
        return $config;
    }
    
    /**
     * Registers the mmOpposite configuration in the current field config, and registers a post processor
     * on the target table to generate the required configuration there as well
     *
     * @param   array  $config
     * @param   array  $options
     * @param   array  $tableNames
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException
     *
     * @deprecated will be removed in v12 use the option container instead
     * {@link \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\AbstractFieldPreset::initializeOptions}
     */
    protected function addMmOppositeConfig(array $config, array $options, array $tableNames): array
    {
        $i = new MmTableOption($tableNames);
        $i->initialize($this->context);
        $i->applyConfig($config, $options);
        
        return $config;
    }
}