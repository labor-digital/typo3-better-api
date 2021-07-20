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
     */
    protected function addBasePidOptions(array $optionDefinition, bool $withMapping = false): array
    {
        $pid = $this->context->cs()->typoContext->pid();
        
        if ($withMapping) {
            $optionDefinition['basePid'] = [
                'type' => ['int', 'null', 'string', 'array'],
                'default' => null,
                'filter' => function ($v) use ($pid) {
                    if ($v === null || is_int($v)) {
                        return $v;
                    }
                    
                    if (! is_array($v)) {
                        return $pid->get($v);
                    }
                    
                    // Generate the table names for all keys
                    $keys = array_keys($v);
                    foreach ($keys as $i => $table) {
                        $keys[$i] = $this->context->getRealTableName($table);
                    }
                    
                    return array_combine($keys, $pid->getMultiple($v));
                },
            ];
        } else {
            $optionDefinition['basePid'] = [
                'type' => ['int', 'null', 'string'],
                'default' => null,
                'filter' => static function ($v) use ($pid) {
                    return $v === null ? $v : $pid->get($v);
                },
            ];
        }
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to apply the "basePid" config option to the config array of the field
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     */
    protected function addBasePidConfig(array $config, array $options): array
    {
        if ($options['basePid'] !== null) {
            $config['basePid'] = $options['basePid'];
        }
        
        return $config;
    }
}