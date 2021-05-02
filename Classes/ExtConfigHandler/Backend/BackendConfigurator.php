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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Backend;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\Common\Assets\AssetCollectorTrait;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;

class BackendConfigurator extends AbstractExtConfigConfigurator
{
    use RteConfigTrait;
    use AssetCollectorTrait;
    
    /**
     * The list of registered rte configuration arrays
     *
     * @var array
     */
    protected $rteConfig = [];
    
    /**
     * A list of rte configuration files by their preset name to load
     *
     * @var array
     */
    protected $rteConfigFiles = [];
    
    /**
     * TYPO3 core options in the $GLOBALS array
     *
     * @var array
     */
    protected $globals = [];
    
    /**
     * Use this method to register your custom RTE configuration for the Typo3 backend.
     *
     * @param   array  $config   The part you would normally write under default.editor.config
     *                           Alternatively: If you provide a "config" key in your array,
     *                           it will automatically be moved to editor.config, all other
     *                           options will be moved to the root preset. This is useful for
     *                           defining "processing" information or similar cases.
     *                           If you don't want a "magic" restructuring of your configuration
     *                           and keep it as you defined it start with an 'editor' => ['config' => []]
     *                           array, which will disable all of our internal restructuring.
     * @param   array  $options  Additional options for the configuration
     *                           - preset string (default): A speaking name/key for the preset you are configuring.
     *                           By default all configuration will be done to the "default" preset
     *                           - useDefaultImports bool (TRUE): By default the Processing.yaml, Base.yaml and
     *                           Plugins.yaml will be auto-imported in your configuration. Set this to false to disable
     *                           this feature
     *                           - imports array: Additional imports that will be added to the generated preset file
     *
     * @return $this
     * @see https://docs.typo3.org/c/typo3/cms-rte-ckeditor/master/en-us/Configuration/Examples.html
     */
    public function registerRteConfig(array $config, array $options = []): self
    {
        $options = Options::make($options, [
            'preset' => [
                'type' => 'string',
                'default' => 'default',
            ],
            'useDefaultImports' => [
                'type' => 'bool',
                'default' => true,
            ],
            'imports' => [
                'type' => 'array',
                'default' => [],
                'children' => [
                    '*' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ]);
        $options = $this->context->replaceMarkers($options);
        
        unset($this->rteConfigFiles[$options['preset']]);
        $this->rteConfig[$options['preset']] = $this->makeRteConfig(
            $this->context->replaceMarkers($config),
            $options
        );
        
        return $this;
    }
    
    /**
     * Returns a registered rte config ur null if it was not defined
     *
     * @param   string  $presetName  The name/key of the preset to retrieve
     *
     * @return array|null
     */
    public function getRteConfig(string $presetName): ?array
    {
        return $this->rteConfig[$this->context->replaceMarkers($presetName)] ?? null;
    }
    
    /**
     * Used to set the raw configuration array for an rte configuration.
     * Unlike registerRteConfig() this method does not apply any additional logic to your configuration
     *
     * @param   string  $presetName  The name/key of the preset to set the configuration for
     * @param   array   $config      The raw RTE configuration array to applied for the preset name
     *
     * @return $this
     */
    public function setRteConfig(string $presetName, array $config): self
    {
        $this->rteConfig[$this->context->replaceMarkers($presetName)] = $config;
        
        return $this;
    }
    
    /**
     * Allows you to register a new, static editor configuration yml file
     *
     * @param   string  $presetName  The name/key of the preset to set the configuration file for
     * @param   string  $fileName    The path of the file relative to EXT:yourExt...
     *
     * @return $this
     */
    public function registerRteConfigFile(string $presetName, string $fileName): self
    {
        $presetName = $this->context->replaceMarkers($presetName);
        unset($this->rteConfig[$presetName]);
        $this->rteConfigFiles[$presetName] = $this->context->replaceMarkers($fileName);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $this->storeAssetCollectorConfiguration($state);
        
        $state->useNamespace(null, function (ConfigState $state) {
            if (! empty($this->rteConfig)) {
                $mount = $this->context->getExtConfigService()->getFsMount();
                foreach ($this->rteConfig as $key => $config) {
                    $this->rteConfigFiles[$key] = $this->dumpRteConfigFile($mount, $key, $config);
                }
            }
            $state->mergeIntoArray('typo.globals.TYPO3_CONF_VARS.RTE.Presets', $this->rteConfigFiles);
        });
    }
    
    
}
