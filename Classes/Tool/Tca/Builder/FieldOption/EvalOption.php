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
 * Last modified: 2021.10.25 at 13:53
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Option to add the different eval options to the Options::make definition.
 * The default eval types are: "required", "trim", "datetime", "lower", "int", "email", "password",
 * "unique", "uniqueInSite" and "null"
 */
class EvalOption extends AbstractOption
{
    public const TYPES
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
     * An array of eval types that are allowed to be used, everything else will not be added as an option.
     * If omitted all available eval types are allowed
     *
     * @var array|null
     */
    protected $allowList;
    
    /**
     * Can be used to set the default values for given eval types.
     * setting this to ["trim" => TRUE] will set trim to be true by default,
     * otherwise all eval rules start with a value of FALSE.
     *
     * @var array
     */
    protected $defaults;
    
    public function __construct(?array $allowList = null, ?array $defaults = null)
    {
        $this->allowList = $allowList;
        $this->defaults = $defaults ?? [];
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        foreach (static::TYPES as $type) {
            if ($this->allowList && ! in_array($type, $this->allowList, true)) {
                continue;
            }
            
            $definition[$type] = [
                'type' => 'bool',
                'default' => $this->defaults[$type] ?? false,
            ];
        }
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $eval = [];
        foreach (static::TYPES as $type) {
            if (! $options[$type]) {
                continue;
            }
            
            if (! empty($this->allowList) && is_array($this->allowList) && ! in_array($type, $this->allowList, true)) {
                continue;
            }
            
            $eval[] = $type;
        }
        
        if (! empty($eval)) {
            $config['eval'] = implode(',', $eval);
        } else {
            unset($config['eval']);
        }
    }
    
}