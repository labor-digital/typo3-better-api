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

use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class MiddlewareConfigGenerator
{
    
    /**
     * Generates the combined middleware list of all middlewares, registered using the ext config option
     *
     * @param array $middlewares
     * @param array $disabledMiddlewares
     *
     * @return array
     */
    public function generate(array $middlewares, array $disabledMiddlewares): array
    {
        $middlewareConfig = [];
        
        // Pass 1: Register middlewares
        foreach ($middlewares as $configRaw) {
            $config = $configRaw['value'];
            
            // Get identifier
            $identifier = Arrays::getPath($config, ['options', 'identifier']);
            if (empty($identifier) || !is_string($identifier)) {
                $identifier = $this->makeMiddlewareIdentifier($config['class']);
            }
            
            // Prepare ordering
            $before = $after = [];
            if (!empty($config['options']['before'])) {
                $before = $config['options']['before'];
            }
            if (is_string($before)) {
                $before = [$before];
            }
            if (!empty($config['options']['after'])) {
                $after = $config['options']['after'];
            }
            if (is_string($after)) {
                $after = [$after];
            }
            
            
            // Build the config
            $target = $config['target'] === 'frontend' ? 'frontend' : 'backend';
            $c = ['target' => $config['class']];
            if (!empty($before)) {
                $c['before'] = $before;
            }
            if (!empty($after)) {
                $c['after'] = $after;
            }
            $middlewareConfig[$target][$identifier] = $c;
        }
        
        // Pass 2: Disable middlewares
        foreach ($disabledMiddlewares as $configRaw) {
            $config = $configRaw['value'];
            
            // Prepare the identifier
            $identifier = $config['classOrIdentifier'];
            if (class_exists($config['classOrIdentifier']) || stripos($config['classOrIdentifier'], '\\') !== false) {
                $identifier = $this->makeMiddlewareIdentifier($config['classOrIdentifier']);
            }
            
            // Build the config
            $target = $config['target'] === 'frontend' ? 'frontend' : 'backend';
            $middlewareConfig[$target][$identifier]['disabled'] = true;
        }
        
        // Done
        return $middlewareConfig;
    }
    
    /**
     * Builds an automatic middleware identifier out of the given class name and the extension key
     *
     * @param string $className The name of the class to generate the middleware identifier for
     *
     * @return string
     */
    protected function makeMiddlewareIdentifier(string $className): string
    {
        return Inflector::toDashed('middleware-' . implode('-', explode('\\', $className)));
    }
}
