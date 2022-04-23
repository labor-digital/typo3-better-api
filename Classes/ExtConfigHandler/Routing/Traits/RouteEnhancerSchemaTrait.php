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
 * Last modified: 2021.07.26 at 13:53
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing\Traits;


use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Throwable;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

trait RouteEnhancerSchemaTrait
{
    protected function getDefaultSchema(): array
    {
        return [
            'pids' => [
                'type' => 'array',
                'default' => [],
                'filter' => function ($v) { return $this->context->resolvePids($v); },
            ],
            'raw' => [
                'type' => 'array',
                'default' => [],
            ],
            'rawOverride' => [
                'type' => 'array',
                'default' => [],
            ],
        ];
    }
    
    protected function getRoutePathSchema(array $override = []): array
    {
        return $this->mergeOptionSchemas(
            $this->getDefaultSchema(),
            [
                'routePath' => [
                    'type' => 'string',
                    'filter' => static function ($v) {
                        return '/' . ltrim($v, '/ ');
                    },
                ],
                'requirements' => [
                    'type' => 'array',
                    'default' => [],
                    'filter' => function (array $list, $k, array $options) {
                        if (! isset($options['routePath'])) {
                            return [];
                        }
                        
                        foreach ($this->extractArgsFromRoutePath($options['routePath']) as $key) {
                            if (! isset($list[$key])) {
                                $list[$key] = "[a-zA-Z0-9\-_.]*";
                            }
                        }
                        
                        return $list;
                    },
                ],
                'defaults' => [
                    'type' => 'array',
                    'default' => [],
                ],
                'dbArgs' => [
                    'type' => 'array',
                    'default' => [],
                ],
                'localeArgs' => [
                    'type' => 'array',
                    'default' => [],
                ],
                'staticArgs' => [
                    'type' => 'array',
                    'default' => [],
                ],
                'urlEncodeArgs' => [
                    'type' => 'array',
                    'default' => [],
                ],
            ],
            $override
        );
    }
    
    protected function getExtBaseSchema(array $override = []): array
    {
        return $this->mergeOptionSchemas(
            $this->getRoutePathSchema(),
            [
                'controller' => [
                    'type' => 'string',
                    'validator' => static function (string $v) {
                        if (! class_exists($v)) {
                            return 'The given controller class: ' . $v . ' does not exist!';
                        }
                        if (! in_array(ActionController::class, class_parents($v), true)) {
                            return 'The given controller class: ' . $v . ' must extend ' . ActionController::class;
                        }
                        
                        return true;
                    },
                ],
                'action' => [
                    'type' => 'string',
                ],
                'extension' => [
                    'type' => 'string',
                    'default' => NamingUtil::extensionNameFromExtKey($this->context->getExtKey()),
                ],
                'additional' => [
                    'type' => 'array',
                    'default' => [],
                    'filter' => function (array $list) {
                        foreach ($list as $k => &$v) {
                            if (is_string($v)) {
                                $v = [$v];
                            }
                            
                            // We ignore the issue here and handle it in the validator
                            if (! isset($v[0]) || ! is_string($v[0])) {
                                continue;
                            }
                            
                            if (! isset($v[1])) {
                                $args = $this->extractArgsFromRoutePath($v[0]);
                                $v[1] = array_combine($args, $args);
                            }
                        }
                        
                        return $list;
                    },
                    'validator' => static function ($list) {
                        foreach ($list as $k => $v) {
                            if (empty($v) || ! is_array($v) || Arrays::isAssociative($v)) {
                                return 'Invalid additional route at position: ' . $k;
                            }
                            
                            if (! is_string($v[0])) {
                                return 'The first element in an additional route at position: ' . $k
                                       . 'must be the route path, like "/{page}"!';
                            }
                            
                            if (isset($v[1]) && ! is_array($v[1])) {
                                return 'The second element in an additional route at position: ' . $k
                                       . 'must be an array containing the argument name map!';
                            }
                        }
                        
                        return true;
                    },
                ],
                'arguments' => [
                    'type' => 'array',
                    'default' => function ($key, array $options): array {
                        $args = $this->extractArgsFromRoutePath($options['routePath']);
                        
                        return array_combine($args, $args);
                    },
                ],
                'plugin' => [
                    'type' => 'string',
                    'default' => static function ($key, array $options): string {
                        try {
                            return NamingUtil::pluginNameFromControllerAction(
                                $options['controller'],
                                $options['action']
                            );
                        } catch (Throwable $e) {
                            if (str_starts_with($e->getMessage(), 'No plugin name ')) {
                                return '-2';
                            }
                            
                            return '-1';
                        }
                    },
                    'validator' => static function (string $v, $key, array $options) {
                        if ($v === '-1') {
                            return 'The plugin name of the given controller: ' . $options['controller']
                                   . ' and action: ' . $options['action']
                                   . ' is ambiguous, please define a plugin name manually using the "plugin" option!';
                        }
                        
                        if ($v === '-2') {
                            return 'The plugin name of the given controller: ' . $options['controller']
                                   . ' and action: ' . $options['action']
                                   . ' seams somehow not to be defined in extbase! Did you configure the plugin correctly?';
                        }
                        
                        return true;
                    },
                ],
            ],
            $override
        );
    }
    
    /**
     * Merges multiple option schemas into a single schema array
     *
     * @param   mixed  ...$definitions
     *
     * @return array
     */
    protected function mergeOptionSchemas(...$definitions): array
    {
        if (count($definitions) > 1) {
            $definitions[] = 'nn';
            $definitions[] = 'r';
            
            return Arrays::merge(
                ...$definitions
            );
        }
        
        return reset($definitions);
    }
    
    /**
     * Helper to extract the argument list from the route path
     *
     * @param   string  $routePath
     *
     * @return array
     */
    protected function extractArgsFromRoutePath(string $routePath): array
    {
        preg_match_all('~({.*?})~s', $routePath, $m);
        
        return array_map(static function ($v) { return trim($v, '{} '); }, $m[1]);
    }
}
