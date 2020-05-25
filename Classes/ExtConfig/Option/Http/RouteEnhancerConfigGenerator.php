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
 * Last modified: 2020.03.18 at 19:43
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Http;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedValueGeneratorInterface;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;

class RouteEnhancerConfigGenerator implements CachedValueGeneratorInterface
{
    
    /**
     * @inheritDoc
     */
    public function generate(array $data, ExtConfigContext $context, array $additionalData, $option)
    {
        $routeEnhancers = [];
        $context->runWithCachedValueDataScope($data, function (array $options, string $key) use (&$routeEnhancers, $context, $additionalData) {
            
            // Select the method to build the config with
            if (!isset($options['routeType']) || !is_string($options['routeType'])) {
                throw new ExtConfigException('The given route enhancer did not define a route type to build the config for!');
            }
            $method = 'make' . ucfirst($options['routeType']) . 'Config';
            if (!method_exists($this, $method)) {
                throw new ExtConfigException('The given route enhancer did specify an enhancer with type: ' .
                    $options['routeType'] . " but there is no configuration method with the name $method in the generator class!");
            }
            
            // Build the configuration
            $config = call_user_func([$this, $method], $options, $context, $key, $additionalData);
            $routeEnhancers[$key] = $config;
        });
        return $routeEnhancers;
    }
    
    /**
     * Generates the configuration for a pagination route enhancement
     *
     * @param array                                                   $options
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     *
     * @return array
     */
    protected function makePaginationConfig(array $options, ExtConfigContext $context): array
    {
        $options = $this->prepareOptions($options, $context);
        $config = $this->prepareRawConfig($options);
        return Arrays::merge([
            'routePath'    => '/{page}',
            'requirements' => [
                'page' => '\\d+',
            ],
            'defaults'     => [
                'page' => '1',
            ],
            'aspects'      => [
                'page' => [
                    'type'  => 'StaticRangeMapper',
                    'start' => '1',
                    'end'   => '999',
                ],
            ],
        ], $config);
    }
    
    /**
     * Generates the configuration for a value route enhancement
     *
     * @param array                                                   $options
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     *
     * @return array
     */
    protected function makeValueConfig(array $options, ExtConfigContext $context): array
    {
        $options = $this->prepareOptionsWithRoutePath($options, $context);
        $config = $this->prepareRawConfig($options);
        $config = $this->prepareDbArgs($config, $options, $context);
        $config = $this->prepareLocaleArgs($config, $options);
        $config = $this->prepareStaticArgs($config, $options);
        return Arrays::merge([
            'routePath'    => $options['routePath'],
            'requirements' => $options['requirements'],
        ], $config);
    }
    
    /**
     * Internal helper to build the options array with the
     * addition to add additional definition entries for the different configuration options
     *
     * @param array                                                   $options
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     * @param array                                                   $additionalDefinition
     *
     * @return array
     */
    protected function prepareOptions(array $options, ExtConfigContext $context, array $additionalDefinition = []): array
    {
        $definition = Arrays::merge([
            'pids'      => [
                'type'    => 'array',
                'default' => [],
                'filter'  => function (array $pids) use ($context): array {
                    $pidAspect = $context->TypoContext->getPidAspect();
                    foreach ($pids as $k => $v) {
                        if ($pidAspect->hasPid($v)) {
                            $pids[$k] = $context->TypoContext->getPidAspect()->getPid($v);
                        }
                    }
                    return $pids;
                },
            ],
            'raw'       => [
                'type'    => 'array',
                'default' => [],
            ],
            'site'      => [
                'type'    => 'string',
                'default' => '',
            ],
            'routeType' => '',
        ], $additionalDefinition);
        return Options::make($options, $definition);
    }
    
    /**
     * Similar to the prepareOptions() method, but also adds special definitions for route path and requirement options
     *
     * @param array                                                   $options
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     * @param array                                                   $additionalDefinition
     *
     * @return array
     */
    protected function prepareOptionsWithRoutePath(array $options, ExtConfigContext $context, array $additionalDefinition = []): array
    {
        return $this->prepareOptions($options, $context, Arrays::merge([
            'routePath'    => [
                'type'   => 'string',
                'filter' => function ($v) {
                    return '/' . ltrim($v, '/ ');
                },
            ],
            'requirements' => [
                'type'    => 'array',
                'default' => [],
                'filter'  => function (array $list, $key, array $options) {
                    if (!isset($options['routePath'])) {
                        return [];
                    }
                    preg_match_all('~({.*?})~si', $options['routePath'], $m);
                    foreach ($m[1] as $key) {
                        if (!isset($list[$key])) {
                            $list[trim($key, '}{')] = "[a-zA-Z0-9\-_.]*";
                        }
                    }
                    return $list;
                },
            ],
            'defaults'     => [
                'type'    => 'array',
                'default' => [],
            ],
            'dbArgs'       => [
                'type'    => 'array',
                'default' => [],
            ],
            'localeArgs'   => [
                'type'    => 'array',
                'default' => [],
            ],
            'staticArgs'   => [
                'type'    => 'array',
                'default' => [],
            ],
        ], $additionalDefinition));
    }
    
    /**
     * Internal helper to prepare the raw configuration object based on the given options
     *
     * @param array $options
     *
     * @return array
     */
    protected function prepareRawConfig(array $options): array
    {
        $config = $options['raw'];
        if (!empty($options['pids'])) {
            $config['limitToPages'] = $options['pids'];
        }
        if (!isset($config['type'])) {
            $config['type'] = 'Simple';
        }
        if (!empty($options['defaults'])) {
            $config['defaults'] = Arrays::merge((is_array($config['defaults']) ? $config['defaults'] : []), $options['defaults']);
        }
        $config['@site'] = $options['site'];
        return $config;
    }
    
    /**
     * Internal helper to generate the PersistedAliasMapper configuration based on the given dbArgs
     *
     * @param array                                                   $config
     * @param array                                                   $options
     * @param \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext $context
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function prepareDbArgs(array $config, array $options, ExtConfigContext $context): array
    {
        if (empty($options['dbArgs'])) {
            return $config;
        }
        foreach ($options['dbArgs'] as $field => $fieldConfig) {
            // Validate the input
            if (!is_string($field)) {
                throw new ExtConfigException('Invalid configuration for a dbArgs in the configuration of route: ' . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            $baseError = "Invalid configuration for the dbArgs \"$field\" in the configuration of route: " . $options['routePath'] . '!';
            if (!is_array($fieldConfig) || count($fieldConfig) !== 2) {
                throw new ExtConfigException($baseError . ' The field configuration has to be an array with exactly two elements!');
            }
            if (!is_string($fieldConfig[0]) || !is_string($fieldConfig[1])) {
                throw new ExtConfigException($baseError . ' Both elements have to be strings!');
            }
            
            // Prepare the table name
            $table = $context->OptionList->table()->getRealTableName($fieldConfig[0]);
            $tableField = $fieldConfig[1];
            
            // Build the aspect
            $config['aspects'][$field] = [
                'type'           => 'PersistedAliasMapper',
                'tableName'      => $table,
                'routeFieldName' => $tableField,
            ];
        }
        return $config;
    }
    
    /**
     * Internal helper to generate the LocaleModifier configuration based on the given localeArgs
     *
     * @param array $config
     * @param array $options
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function prepareLocaleArgs(array $config, array $options): array
    {
        if (empty($options['localeArgs'])) {
            return $config;
        }
        foreach ($options['localeArgs'] as $field => $fieldConfig) {
            // Validate the input
            if (!is_string($field)) {
                throw new ExtConfigException('Invalid configuration for a localeArgs in the configuration of route: ' . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            $baseError = "Invalid configuration for the localeArgs \"$field\" in the configuration of route: " . $options['routePath'] . '!';
            if (!is_array($fieldConfig)) {
                throw new ExtConfigException($baseError . ' The field configuration has to be an array!');
            }
            
            // Extract the default value
            $default = reset($fieldConfig);
            if (is_numeric(key($fieldConfig))) {
                array_shift($fieldConfig);
            }
            
            // Build map
            $map = [];
            foreach ($fieldConfig as $locale => $value) {
                $map[] = [
                    'locale' => $locale,
                    'value'  => $value,
                ];
            }
            
            // Build the aspect
            $config['aspects'][$field] = [
                'type'      => 'LocaleModifier',
                'default'   => $default,
                'localeMap' => $map,
            ];
        }
        
        // Done
        return $config;
    }
    
    /**
     * Internal helper to generate the StaticValueMapper configuration based on the given staticArgs
     *
     * @param array $config
     * @param array $options
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function prepareStaticArgs(array $config, array $options): array
    {
        if (empty($options['staticArgs'])) {
            return $config;
        }
        foreach ($options['staticArgs'] as $field => $fieldConfig) {
            // Validate the input
            if (!is_string($field)) {
                throw new ExtConfigException('Invalid configuration for a staticArgs in the configuration of route: ' . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            $baseError = "Invalid configuration for the staticArgs \"$field\" in the configuration of route: " . $options['routePath'] . '!';
            if (!is_array($fieldConfig)) {
                throw new ExtConfigException($baseError . ' The field configuration has to be an array!');
            }
            
            // Build map
            $map = [];
            $localeMap = [];
            foreach ($fieldConfig as $value => $label) {
                // Handle locale array-definition
                if (is_array($label)) {
                    // Extract the default value
                    $defaultLabel = reset($label);
                    if (is_numeric(key($label))) {
                        array_shift($label);
                    }
                    // Build the locale map
                    foreach ($label as $locale => $localeLabel) {
                        $localeMap[$locale][$localeLabel] = $value;
                    }
                    $label = $defaultLabel;
                }
                
                // Add value to map
                $map[$label] = $value;
            }
            
            // Finalize locale map
            foreach ($localeMap as $locale => $map) {
                $localeMap[$locale] = [
                    'locale' => $locale,
                    'map'    => $map,
                ];
            }
            $localeMap = array_values($localeMap);
            
            // Build the aspect
            $config['aspects'][$field] = [
                'type'      => 'StaticValueMapper',
                'map'       => $map,
                'localeMap' => empty($localeMap) ? null : $localeMap,
            ];
        }
        
        // Done
        return $config;
    }
}
