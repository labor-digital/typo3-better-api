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
 * Last modified: 2021.10.26 at 11:55
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\FieldOption;


use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;

/**
 * Adds multiple options to modify the "crop" definition on FAL file fields
 */
class FileImageCropOption extends AbstractOption
{
    
    /**
     * @inheritDoc
     */
    public function addDefinition(array &$definition): void
    {
        $definition['allowCrop'] = [
            'type' => 'bool',
            'default' => true,
        ];
        $definition['useDefaultCropVariant'] = [
            'type' => 'bool',
            'default' => true,
        ];
        $definition['cropVariants'] = [
            'type' => 'array',
            'default' => [],
            'children' => [
                '*' => [
                    'title' => [
                        'type' => 'string',
                    ],
                    'aspectRatios' => [
                        'type' => 'array',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function applyConfig(array &$config, array $options): void
    {
        if (! $options['allowCrop'] || $this->context->isInFlexFormSection()) {
            // Disable crop field
            $config['overrideChildTca']['columns']['crop']['config']['type'] = 'passthrough';
            $config['overrideChildTca']['columns']['crop']['config']['renderType'] = 'passthrough';
            
            return;
        }
        
        $cropVariants = $this->buildCropVariants($options);
        
        if ($options['useDefaultCropVariant'] === false) {
            $cropVariants['default']['disabled'] = 1;
        }
        
        $cropConfig = [
            'type' => 'imageManipulation',
        ];
        
        if (! empty($cropVariants)) {
            $cropConfig['cropVariants'] = $cropVariants;
        }
        
        $config['overrideChildTca']['columns']['crop']['config'] = $cropConfig;
    }
    
    protected function buildCropVariants(array $options): array
    {
        $cropVariants = [];
        foreach ($options['cropVariants'] as $k => $c) {
            // Build aspect ratio list by converting the simple format to the TYPO3 format
            if (! is_array($c['allowedAspectRatios'])) {
                $c['allowedAspectRatios'] = [];
            }
            
            if (is_array($c['aspectRatios'])) {
                foreach ($c['aspectRatios'] as $ratio => $label) {
                    if ($ratio === 'free') {
                        $ratio = 'NaN';
                    }
                    $value = 0;
                    if ($ratio !== 'NaN') {
                        $ratioParts = array_map('trim', explode(':', $ratio));
                        if (count($ratioParts) !== 2 || ! is_numeric($ratioParts[0]) || ! is_numeric($ratioParts[1])
                            || (int)$ratioParts[1] === 0) {
                            throw new TcaBuilderException("Invalid image ratio definition: \"$ratio\" given!");
                        }
                        $value = $ratioParts[0] / $ratioParts[1];
                    }
                    $c['allowedAspectRatios'][$ratio] = [
                        'title' => $label,
                        'value' => $value,
                    ];
                }
            }
            unset($c['aspectRatios']);
            
            // Add the selected aspect ratio if it is not defined
            if (! isset($c['selectedRatio']) && ! empty($c['allowedAspectRatios'])) {
                reset($c['allowedAspectRatios']);
                $c['selectedRatio'] = key($c['allowedAspectRatios']);
            }
            
            // Add the crop area if it is not defined
            if (! isset($c['cropArea'])) {
                $c['cropArea'] = [
                    'height' => 1.0,
                    'width' => 1.0,
                    'x' => 0.0,
                    'y' => 0.0,
                ];
            }
            
            // Make sure we have a default crop variant
            if (is_numeric($k)) {
                if (! isset($cropVariants['default']) && ! isset($options['cropVariants']['default'])) {
                    $k = 'default';
                } else {
                    throw new TcaBuilderException(
                        'Invalid crop variant list given. elements must have unique, non-numeric keys! Key: ' . $k . ' is therefore invalid!');
                }
            }
            
            $cropVariants[$k] = $c;
        }
        
        return $cropVariants;
        
    }
    
}