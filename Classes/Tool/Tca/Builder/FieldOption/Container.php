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
 * Last modified: 2021.10.25 at 12:00
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\FieldPresetContext;
use Neunerlei\Options\Options;

class Container implements NoDiInterface
{
    public const INHERIT_DEFINITION_KEY = '_OPT_CONTAINER_INHERIT';
    
    /**
     * @var array
     */
    protected $definition;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\FieldOption\AbstractOption[]
     */
    protected $options = [];
    
    /**
     * After validate() has been executed, the validated options will be stored in here.
     *
     * @var array|null
     */
    protected $validatedOptions;
    
    public function __construct(FieldPresetContext $context, array $definition)
    {
        foreach ($definition as $k => $item) {
            if ($item instanceof AbstractOption) {
                $item->initialize($context);
                $this->options[] = $item;
                unset($definition[$k]);
            }
        }
        
        $this->definition = $definition;
    }
    
    /**
     * Validates the given $options using the given definition and returns the validated option array.
     *
     * @param   array  $options         The given config array provided for a field preset
     * @param   array  $applierOptions  Additional options for the Options::make applier {@link Options::make()}
     *
     * @return array
     */
    public function validate(array $options = [], array $applierOptions = []): array
    {
        if (isset($this->validatedOptions)) {
            return $this->validatedOptions;
        }
        
        $definition = $this->definition;
        unset($this->definition);
        
        foreach ($this->options as $option) {
            $option->addDefinition($definition);
        }
        
        // If the inherit key exists in the option, we automatically inherit
        // the provided parent definition into this definition
        if (is_object($options[static::INHERIT_DEFINITION_KEY] ?? null) &&
            is_callable([$options[static::INHERIT_DEFINITION_KEY], 'getDefinition'])) {
            foreach ($options[static::INHERIT_DEFINITION_KEY]->getDefinition() as $k => $v) {
                if ($k === static::INHERIT_DEFINITION_KEY) {
                    continue;
                }
                $definition[$k] = $v;
            }
            unset($options[static::INHERIT_DEFINITION_KEY]);
        }
        
        return $this->validatedOptions = Options::make($options, $definition, $applierOptions);
        
    }
    
    /**
     * Applies the validated options to the given configuration array of the field.
     * WARNING: MUST be executed AFTER validate()!
     *
     * @param   array  $config  The field config that the options should be applied to
     *
     * @return array Returns the enhanced $config array with the $given options applied
     */
    public function apply(array $config = []): array
    {
        if (! isset($this->validatedOptions)) {
            throw new \RuntimeException('You can\'t apply the options to the given configuration, because you need to run "validate()" first!');
        }
        
        foreach ($this->options as $option) {
            $option->applyConfig($config, $this->validatedOptions);
        }
        
        return array_filter($config, static function ($v) { return $v !== null; });
    }
}