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
 * Last modified: 2021.10.25 at 13:37
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;

/**
 * The basePid option can be used to limit group or select fields to a certain page
 */
class BasePidOption extends AbstractOption
{
    /**
     * Allow the usage of "basePid" option to be an array of tableName -> basePids
     *
     * @var bool
     */
    protected $withMapping;
    
    public function __construct(bool $withMapping = false)
    {
        $this->withMapping = $withMapping;
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        if ($this->withMapping) {
            $definition['basePid'] = [
                'type' => ['int', 'null', 'string', 'array', 'true'],
                'default' => null,
                'filter' => function ($v) {
                    if ($v === true) {
                        return '###CURRENT_PID###';
                    }
                    
                    if ($v === null || is_int($v)) {
                        return $v;
                    }
                    
                    if (! is_array($v)) {
                        return $v;
                    }
                    
                    return array_combine(
                        $this->context->getRealTableNameList(array_keys($v)),
                        $v
                    );
                },
            ];
        } else {
            $definition['basePid'] = [
                'type' => ['int', 'null', 'string', 'true'],
                'default' => null,
                'filter' => static function ($v) {
                    if ($v === true) {
                        return '###CURRENT_PID###';
                    }
                    
                    return $v;
                },
            ];
        }
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if ($options['basePid'] === null) {
            return;
        }
        
        $config['basePid'] = $options['basePid'];
    }
    
}