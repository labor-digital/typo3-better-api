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
 * Last modified: 2021.10.25 at 18:14
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;

/**
 * Applies the option to register a "userFunc" for itemProcFunc definitions
 */
class UserFuncOption extends AbstractOption
{
    /**
     * default: "userFunc", can be set to another name as well, (e.g. itemsProcFunc)
     *
     * @var string
     */
    protected $configName;
    
    public function __construct(?string $configName = null)
    {
        $this->configName = $configName ?? 'itemsProcFunc';
    }
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['userFunc'] = [
            'type' => 'string',
            'default' => '',
            'preFilter' => static function ($v) {
                if (is_array($v) && count($v) === 2) {
                    return reset($v) . '->' . end($v);
                }
                
                return $v;
            },
            'validator' => static function (string $func) {
                if (empty($func)) {
                    return true;
                }
                
                try {
                    return NamingUtil::isCallable($func);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            },
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (empty($options['userFunc'])) {
            return;
        }
        
        $config[$this->configName] = $options['userFunc'];
    }
    
}