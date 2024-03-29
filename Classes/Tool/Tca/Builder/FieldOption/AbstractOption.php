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


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext;

abstract class AbstractOption implements NoDiInterface
{
    /**
     * The context of the field
     *
     * @var FieldPresetContext
     */
    protected $context;
    
    /**
     * Initializes the option by injecting the field and current context
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext  $context
     */
    public function initialize(FieldPresetContext $context): void
    {
        $this->context = $context;
    }
    
    /**
     * MUST either append additional option definitions to the given array OR do nothing
     *
     * @param   array  $definition  The option definition either provided by the author or by other registered options
     *
     * @return void
     * @see \Neunerlei\Options\Options::make() to learn how options are configured
     */
    abstract public function addDefinition(array &$definition): void;
    
    /**
     * MUST apply the given $options to the field's $config array OR do nothing
     *
     * @param   array  $config   The field's $config array, preset by the author or other registered options
     * @param   array  $options  The parsed options array with the definition provided in addOptionDefinition applied to it
     *
     * @return void
     */
    abstract public function applyConfig(array &$config, array $options): void;
}