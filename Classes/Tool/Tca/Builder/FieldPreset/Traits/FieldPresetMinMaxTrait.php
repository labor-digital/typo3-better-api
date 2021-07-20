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
 * Last modified: 2021.07.20 at 15:10
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldPreset\Traits;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;

trait FieldPresetMinMaxTrait
{
    
    /**
     * Provides the option definition for the minItems and maxItems options
     *
     * @param   array  $optionDefinition
     * @param   array  $options
     *
     * @return array
     */
    protected function addMinMaxItemOptions(array $optionDefinition, array $options = []): array
    {
        $optionDefinition['minItems'] = [
            'type' => 'int',
            'default' => is_numeric($options['minItems']) ? (int)$options['minItems'] : 0,
        ];
        $optionDefinition['maxItems'] = [
            'type' => 'int',
            'default' => is_numeric($options['maxItems']) ? (int)$options['maxItems'] : 999,
        ];
        
        return $optionDefinition;
    }
    
    protected function addMinMaxItemConfig(array $config, array $options): array
    {
        // If the field is required -> minItems is 1
        if ($options['required'] === true) {
            $options['minItems'] = max($options['minItems'], 1);
        }
        $config['minitems'] = $options['minItems'];
        $config['maxitems'] = $options['maxItems'];
        
        return $config;
    }
    
    /**
     * Internal helper to add the "maxLength" config option to the Options::make definition.
     * This makes only sense for "input" type fields
     *
     * @param   array  $optionsDefinition
     * @param   int    $defaultMax  The default value of the maximal input length
     * @param   int    $defaultMin  The default value for the minimal input length
     *
     * @return array
     */
    protected function addMinMaxLengthOptions(
        array $optionsDefinition,
        int $defaultMax = 512,
        int $defaultMin = 0
    ): array
    {
        $optionsDefinition['maxLength'] = [
            'type' => 'int',
            'default' => $defaultMax,
        ];
        $optionsDefinition['minLength'] = [
            'type' => 'int',
            'default' => $defaultMin,
        ];
        
        return $optionsDefinition;
    }
    
    /**
     * Internal helper to apply the "maxLength" configuration to the config array of a input field
     *
     * @param   array  $config
     * @param   array  $options
     * @param   bool   $addSqlStatement  If set to true the sql statement of this column will automatically be
     *
     * @return array
     */
    protected function addMaxLengthConfig(array $config, array $options, bool $addSqlStatement = false): array
    {
        if (! empty($options['maxLength'])) {
            $config['max'] = $options['maxLength'];
        }
        if (! empty($options['minLength'])) {
            $config['min'] = $options['minLength'];
        }
        
        if ($addSqlStatement) {
            $this->configureSqlColumn(static function (Column $column) use ($options) {
                if ((int)$options['maxLength'] <= 4096) {
                    $column->setType(new StringType())
                           ->setDefault('')
                           ->setLength((int)$options['maxLength']);
                } else {
                    $column->setType(new TextType())
                           ->setDefault(null)
                           ->setLength(null);
                }
            });
        }
        
        return $config;
    }
}