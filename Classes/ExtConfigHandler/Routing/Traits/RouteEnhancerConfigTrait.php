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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing\Traits;


use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use Neunerlei\Arrays\Arrays;

trait RouteEnhancerConfigTrait
{
    
    /**
     * Internal helper to merge the "raw" config, the $typeConfig and the "rawOverride" config
     * into a single array, while automatically applying some polyfills
     *
     * @param   array  $typeConfig  The configuration of a specific route enhancer type
     * @param   array  $options     The given options to extract "raw" and "rawOverride", as well as the "pids" from
     *
     * @return array
     */
    protected function mergeConfig(array $typeConfig, array $options): array
    {
        $config = Arrays::merge(
            $options['raw'],
            $typeConfig,
            'nn', 'r'
        );
        
        $config = $this->injectDbArgsConfig($config, $options);
        $config = $this->injectLocaleArgsConfig($config, $options);
        $config = $this->injectStaticArgsConfig($config, $options);
        $config = $this->injectUrlEncodeArgsConfig($config, $options);
        
        if (! empty($options['pids'])) {
            $config['limitToPages'] = $this->context->resolvePids($options['pids']);
        }
        
        if (! isset($config['type'])) {
            $config['type'] = 'Simple';
        }
        
        if (! empty($options['defaults'])) {
            $config['defaults'] = is_array($config['defaults'] ?? null)
                ? Arrays::merge($config['defaults'], $options['defaults'])
                : $options['defaults'];
        }
        
        return Arrays::merge(
            $config,
            $options['rawOverride'],
            'nn', 'r'
        );
    }
    
    /**
     * Internal helper to generate the PersistedAliasMapper configuration based on the given dbArgs
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    protected function injectDbArgsConfig(array $config, array $options): array
    {
        if (empty($options['dbArgs'])) {
            return $config;
        }
        
        foreach ($options['dbArgs'] as $field => $fieldConfig) {
            // Validate the input
            if (! is_string($field)) {
                throw new ExtConfigException('Invalid configuration for a dbArgs in the configuration of route: '
                                             . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            
            $baseError = 'Invalid configuration for the dbArgs "' . $field . '" in the configuration of route: '
                         . $options['routePath'] . '!';
            
            if (! is_array($fieldConfig) || ! in_array(count($fieldConfig), [2, 3], true)) {
                throw new ExtConfigException(
                    $baseError . ' The field configuration has to be an array with two or three elements!');
            }
            
            if (! is_string($fieldConfig[0]) || ! is_string($fieldConfig[1])) {
                throw new ExtConfigException(
                    $baseError . ' Both elements have to be strings!');
            }
            
            $tableName = $this->context->resolveTableName($fieldConfig[0]);
            $tableField = $fieldConfig[1];
            
            $storagePids = null;
            if (isset($fieldConfig[2])) {
                $storagePids = $fieldConfig[2];
                
                if (! is_array($storagePids)) {
                    $storagePids = [$storagePids];
                }
                
                $storagePids = array_unique(
                    $this->context->resolvePids($storagePids)
                );
            }
            
            $config['aspects'][$field] = [
                'type' => $storagePids === null ?
                    'PersistedAliasMapper' : 'T3BAStoragePidAwarePersistedAliasMapper',
                'tableName' => $tableName,
                'routeFieldName' => $tableField,
                'storagePids' => $storagePids,
            ];
        }
        
        return $config;
    }
    
    /**
     * Internal helper to generate the LocaleModifier configuration based on the given localeArgs
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    protected function injectLocaleArgsConfig(array $config, array $options): array
    {
        if (empty($options['localeArgs'])) {
            return $config;
        }
        
        foreach ($options['localeArgs'] as $field => $fieldConfig) {
            if (! is_string($field)) {
                throw new ExtConfigException('Invalid configuration for a localeArgs in the configuration of route: '
                                             . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            
            if (! is_array($fieldConfig)) {
                throw new ExtConfigException(
                    'Invalid configuration for the localeArgs "' . $field . '" in the configuration of route: '
                    . $options['routePath'] . '! The field configuration has to be an array!');
            }
            
            $default = reset($fieldConfig);
            if (is_numeric(key($fieldConfig))) {
                array_shift($fieldConfig);
            }
            
            $map = [];
            foreach ($fieldConfig as $locale => $value) {
                $map[] = [
                    'locale' => $locale,
                    'value' => $value,
                ];
            }
            
            $config['aspects'][$field] = [
                'type' => 'LocaleModifier',
                'default' => $default,
                'localeMap' => $map,
            ];
        }
        
        // Done
        return $config;
    }
    
    /**
     * Internal helper to generate the StaticValueMapper configuration based on the given staticArgs
     *
     * @param   array  $config
     * @param   array  $options
     *
     * @return array
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    protected function injectStaticArgsConfig(array $config, array $options): array
    {
        if (empty($options['staticArgs'])) {
            return $config;
        }
        
        foreach ($options['staticArgs'] as $field => $fieldConfig) {
            if (! is_string($field)) {
                throw new ExtConfigException(
                    'Invalid configuration for a staticArgs in the configuration of route: '
                    . $options['routePath'] . '! A numeric key can\'t be an alias name!');
            }
            
            if (! is_array($fieldConfig)) {
                throw new ExtConfigException(
                    'Invalid configuration for the staticArgs "' . $field . '" in the configuration of route: '
                    . $options['routePath'] . '! The field configuration has to be an array!');
            }
            
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
                
                $map[$label] = $value;
            }
            
            foreach ($localeMap as $locale => $_map) {
                $localeMap[$locale] = [
                    'locale' => $locale,
                    'map' => $_map,
                ];
            }
            $localeMap = array_values($localeMap);
            
            $config['aspects'][$field] = [
                'type' => 'StaticValueMapper',
                'map' => $map,
                'localeMap' => empty($localeMap) ? null : $localeMap,
            ];
        }
        
        return $config;
    }
    
    protected function injectUrlEncodeArgsConfig(array $config, array $options): array
    {
        if (empty($options['urlEncodeArgs'])) {
            return $config;
        }
        
        foreach ($options['urlEncodeArgs'] as $field) {
            if (! is_string($field)) {
                throw new ExtConfigException(
                    'Invalid configuration for a urlEncodeArgs in the configuration of route: '
                    . $options['routePath'] . '! An array of strings is expected!');
            }
            
            $config['aspects'][$field] = [
                'type' => 'T3BAUrlEncodeMapper',
            ];
            
            if (($config['requirements'][$field] ?? null) === '[a-zA-Z0-9\-_.]*') {
                $config['requirements'][$field] = '[\\w\\d\-_.%&+]*';
            }
        }
        
        return $config;
    }
}
