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
 * Last modified: 2021.10.25 at 12:34
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * Adds the configuration for a "default" value of the field
 */
class DefaultOption extends AbstractOption
{
    
    /**
     * The value the field should use as "default" for the fields "default" config.
     *
     * @var mixed
     */
    protected $default;
    
    /**
     * The type for the default value validation.
     *
     * @var array|null
     */
    protected $type;
    
    /**
     * @param   mixed       $default  The value the field should use as "default" for the fields "default" config.
     * @param   array|null  $type     The type for the default value validation.
     */
    public function __construct($default = '', ?array $type = null)
    {
        $this->default = $default;
        $this->type = $type ?? [gettype($default), 'null'];
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['default'] = [
            'type' => $this->type,
            'preFilter' => static function ($v) {
                if (is_array($v) && count($v) === 2 && is_string($v[0] ?? null) && is_string($v[1] ?? null)) {
                    return '@callback:' . $v[0] . '->' . $v[1];
                }
                
                return $v;
            },
            'default' => $this->default,
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if ($options['default'] !== null) {
            $config['default'] = $options['default'];
        } else {
            $config['default'] = '__UNSET';
        }
    }
    
}