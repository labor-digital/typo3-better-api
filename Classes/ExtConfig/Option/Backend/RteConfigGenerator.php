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
 * Last modified: 2020.03.19 at 01:49
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Backend;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedValueGeneratorInterface;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use Symfony\Component\Yaml\Yaml;

class RteConfigGenerator implements CachedValueGeneratorInterface
{
    
    /**
     * @inheritDoc
     */
    public function generate(array $data, ExtConfigContext $context, array $additionalData, $option)
    {
        $presetList = [];
        $context->runWithCachedValueDataScope($data, function ($row) use ($context, &$presetList) {
            $config = $row['config'];
            $options = Options::make($row['options'], [
                'preset'            => [
                    'type'    => 'string',
                    'default' => 'default',
                ],
                'useDefaultImports' => [
                    'type'    => 'bool',
                    'default' => true,
                ],
                'imports'           => [
                    'type'    => 'array',
                    'default' => [],
                ],
            ]);
            
            // Merge the config into a single preset
            $options = $context->replaceMarkers($options);
            $presetKey = $options['preset'];
            if (!isset($presetList[$presetKey])) {
                $presetList[$presetKey] = ['config' => [], 'imports' => [], 'defaultImports' => true];
            }
            $presetList[$presetKey]['config'] = Arrays::merge($presetList[$presetKey]['config'], $config);
            $presetList[$presetKey]['imports'] = Arrays::merge($presetList[$presetKey]['imports'], $options['imports']);
            if ($presetList[$presetKey]['defaultImports']) {
                $presetList[$presetKey]['defaultImports'] =
                $options['useDefaultImports'] === true;
            }
        });
        
        // Build the preset configuration
        foreach ($presetList as $key => $config) {
            $presetList[$key] = $this->generatePresetConfig($context, $key, $config);
        }
        
        // Done
        return $presetList;
    }
    
    /**
     * Internal helper to generate a single rte config preset file.
     * The method will dump the content into the tempFs and return the relative path to the file
     *
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     * @param string                                                  $key
     * @param array                                                   $config
     *
     * @return string
     */
    protected function generatePresetConfig(ExtConfigContext $context, string $key, array $config): string
    {
        // Unify imports
        foreach ($config['imports'] as $k => &$v) {
            if (is_array($v) && is_string($v['resource'])) {
                $v = $v['resource'];
            }
        }
        
        // Add auto imports
        if ($config['defaultImports']) {
            $config['imports'] = Arrays::attach([
                'EXT:rte_ckeditor/Configuration/RTE/Processing.yaml',
                'EXT:rte_ckeditor/Configuration/RTE/Editor/Base.yaml',
                'EXT:rte_ckeditor/Configuration/RTE/Editor/Plugins.yaml',
            ], $config['imports']);
        }
        
        // De-duplicate imports
        $config['imports'] = array_unique($config['imports']);
        foreach ($config['imports'] as &$v) {
            if (is_string($v)) {
                $v = ['resource' => $v];
            }
        }
        
        // Build real config
        $output = [
            'imports' => $config['imports'],
            'editor'  => [
                'config' => Arrays::merge(['stylesSet' => []], $config['config']),
            ],
        ];
        
        // Dump the preset
        $fileName = 'rteConfig/' . $key . '.yaml';
        $context->Fs->setFileContent($fileName, Yaml::dump($output));
        return $context->Fs->getFile($fileName)->getPathname();
    }
}
