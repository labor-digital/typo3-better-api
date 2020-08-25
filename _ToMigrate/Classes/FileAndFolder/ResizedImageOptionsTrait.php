<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.04.11 at 18:28
 */

namespace LaborDigital\Typo3BetterApi\FileAndFolder;

use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;

trait ResizedImageOptionsTrait
{
    
    /**
     * Applies the option definition to create resized/manipulated images.
     * Encapsulated for other extension to use as global image processing format
     *
     * @param   array  $options               The raw options input to validate
     * @param   array  $additionalDefinition  Additional definition to merge into the default
     *
     * @return array The prepared option array
     *
     * @see \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService::getResizedImage()
     */
    protected function applyResizedImageOptions(array $options, array $additionalDefinition = []): array
    {
        // Prepare image processing options
        $def           = [
            'type'    => ['number', 'null', 'string'],
            'default' => null,
            'filter'  => function ($v) {
                if (! is_null($v)) {
                    return (string)$v;
                }
                
                return null;
            },
        ];
        $defNumberOnly = [
            'type'      => ['number', 'null'],
            'default'   => null,
            'preFilter' => function ($v) {
                if (is_numeric($v)) {
                    return floatval($v);
                }
                
                return $v;
            },
            'filter'    => function ($v) {
                if (! is_null($v)) {
                    return (string)$v;
                }
                
                return null;
            },
        ];
        
        // Build the definition
        $defaultDefinition = [
            'width'     => $def,
            'minWidth'  => $defNumberOnly,
            'maxWidth'  => $defNumberOnly,
            'height'    => $def,
            'minHeight' => $defNumberOnly,
            'maxHeight' => $defNumberOnly,
            'crop'      => [
                'type'    => ['bool', 'null', 'string', 'array'],
                'default' => null,
                'filter'  => function ($v) {
                    if (is_array($v)) {
                        $def = ['type' => 'number', 'default' => 0];
                        
                        return Options::make($v, [
                            'x'      => $def,
                            'y'      => $def,
                            'width'  => $def,
                            'height' => $def,
                        ]);
                    }
                    if (! $v) {
                        return null;
                    }
                    
                    return $v;
                },
            ],
            'params'    => [
                'type'    => 'string',
                'default' => '',
            ],
        ];
        
        // Apply the options
        $definition = Arrays::merge($defaultDefinition, $additionalDefinition);
        $options    = Options::make($options, $definition);
        $options    = array_filter($options, function ($v) {
            return ! is_null($v);
        });
        
        // Build additional parameters
        if (! empty($options['params'])) {
            $options['additionalParameters'] = $options['params'];
        }
        unset($options['params']);
        
        // Done
        return $options;
    }
}
