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
 * Last modified: 2021.07.20 at 15:08
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset;


trait FieldPresetEvalTrait
{
    protected $EVAL_TYPES
        = [
            'required',
            'trim',
            'date',
            'datetime',
            'lower',
            'int',
            'email',
            'password',
            'unique',
            'uniqueInSite',
            'null',
        ];
    
    /**
     * Internal helper to add the different eval options to the Options::make definition.
     * The default eval types are: "required", "trim", "datetime", "lower", "int", "email", "password"
     *
     * @param   array  $optionDefinition  The option definition to add the eval rules to
     * @param   array  $evalFilter        If given an array of eval types that are whitelisted everything else will not
     *                                    be added as option
     * @param   array  $evalDefaults      Can be used to set the default values for given eval types.
     *                                    setting this to ["trim" => TRUE] will set trim to be true by default,
     *                                    otherwise all eval rules start with a value of FALSE.
     *
     * @return array
     */
    protected function addEvalOptions(array $optionDefinition, array $evalFilter = [], array $evalDefaults = []): array
    {
        foreach ($this->EVAL_TYPES as $type) {
            if (empty($evalFilter) || in_array($type, $evalFilter, true)) {
                $optionDefinition[$type] = [
                    'type' => 'bool',
                    'default' => $evalDefaults[$type] ?? false,
                ];
            }
        }
        
        return $optionDefinition;
    }
    
    /**
     * Internal helper to add the different eval config options as a string to "config"->"eval"
     *
     * @param   array  $config        The configuration array to add the eval string to
     * @param   array  $options       The current fields options to check for eval config
     * @param   array  $evalFilter    If given an array of eval types that are whitelisted everything else will not be
     *                                added as option
     *
     * @return array
     */
    protected function addEvalConfig(array $config, array $options, array $evalFilter = []): array
    {
        // Build the eval string
        $eval = [];
        foreach ($this->EVAL_TYPES as $type) {
            if ($options[$type] === true && (empty($evalFilter) || in_array($type, $evalFilter, true))) {
                $eval[] = $type;
            }
        }
        $evalString = implode(',', $eval);
        
        // Add eval only if we got it configured
        if (! empty($evalString)) {
            $config['eval'] = $evalString;
        } else {
            unset($config['eval']);
        }
        
        return $config;
    }
}