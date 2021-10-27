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
 * Last modified: 2021.10.25 at 12:03
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


/**
 * Adds the option to configure the "size" of an "input" field either using a percentage or integer value.
 */
class InputSizeOption extends AbstractOption
{
    /**
     * default: "size", can be set to another name as well, (e.g. cols)
     *
     * @var string
     */
    protected $optionName;
    
    public function __construct(?string $optionName = null)
    {
        $this->optionName = $optionName ?? 'size';
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition[$this->optionName] = [
            'type' => ['int', 'string'],
            'default' => '100%',
            'filter' => static function ($val): int {
                $minWidth = 10;
                $maxWidth = 50;
                if (is_string($val)) {
                    if ($val === '100%') {
                        return $maxWidth;
                    }
                    if (strpos(trim($val), '%') !== false) {
                        $val = $maxWidth * (int)trim(trim($val), '% ');
                    } else {
                        $val = (int)$val;
                    }
                }
                
                return max($minWidth, min($maxWidth, $val));
            },
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        $config[$this->optionName] = $options[$this->optionName];
    }
    
}