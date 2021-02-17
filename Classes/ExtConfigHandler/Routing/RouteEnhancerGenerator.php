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
 * Last modified: 2021.02.16 at 11:39
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Routing;


use LaborDigital\T3BA\ExtConfig\SiteBased\SiteConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\Routing\Traits\RouteEnhancerConfigTrait;
use LaborDigital\T3BA\ExtConfigHandler\Routing\Traits\RouteEnhancerSchemaTrait;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;

class RouteEnhancerGenerator
{
    use RouteEnhancerSchemaTrait;
    use RouteEnhancerConfigTrait;

    /**
     * @var \LaborDigital\T3BA\ExtConfig\SiteBased\SiteConfigContext
     */
    protected $context;

    /**
     * RouteEnhancerGenerator constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\SiteBased\SiteConfigContext  $context
     */
    public function __construct(SiteConfigContext $context)
    {
        $this->context = $context;
    }

    /**
     * Generates the configuration for a pagination route enhancement
     *
     * @param   array  $options
     *
     * @return array
     */
    public function makePaginationConfig(array $options): array
    {
        return $this->mergeConfig(
            [
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
            ],
            Options::make($options, $this->getDefaultSchema())
        );
    }

    /**
     * Generates the configuration for a value route enhancement
     *
     * @param   array  $options
     *
     * @return array
     */
    public function makeValueConfig(array $options): array
    {
        $options = Options::make($options, $this->getRoutePathSchema());

        return $this->mergeConfig([
            'routePath'    => $options['routePath'],
            'requirements' => $options['requirements'],
        ], $options);
    }

    /**
     * Generates the configuration for a extbase plugin route enhancement
     *
     * @param   array  $options
     *
     * @return array
     */
    public function makeExtbaseConfig(array $options): array
    {
        $options = Options::make($options, $this->getExtBaseSchema());

        $controllerDefinition = NamingUtil::controllerAliasFromClass($options['controller'])
                                . '::' . $options['action'];

        $config = [
            'type'              => 'Extbase',
            'extension'         => $options['extension'],
            'plugin'            => $options['plugin'],
            'requirements'      => $options['requirements'],
            'routes'            => [
                array_filter([
                    'routePath'   => $options['routePath'],
                    '_controller' => $controllerDefinition,
                    '_arguments'  => $options['arguments'],
                ]),
            ],
            'defaultController' => $controllerDefinition,
        ];

        if (! empty($options['additional'])) {
            foreach ($options['additional'] as $route) {
                $isPaginatorRoute = isset($route[1]['page']);

                if ($isPaginatorRoute) {
                    $options['defaults']['page']     = $options['defaults']['page'] ?? 0;
                    $options['requirements']['page'] = $options['requirements']['page'] ?? '\\d+';

                    if ($route[1]['page'] === 'page') {
                        $route[1]['page'] = '@widget_0/currentPage';
                    }

                    if (! isset($config['aspects']['page'])) {
                        $config['aspects']['page'] = [
                            'type'  => 'StaticRangeMapper',
                            'start' => '1',
                            'end'   => '999',
                        ];
                    }
                }

                $realRoutePath      = rtrim($options['routePath'], '/ ') . '/' . trim($route[0], '/ ');
                $config['routes'][] = array_filter([
                    'routePath'   => $realRoutePath,
                    '_controller' => $controllerDefinition,
                    '_arguments'  => array_merge($options['arguments'], $route[1]),
                ]);
            }
        }

        return $this->mergeConfig($config, $options);
    }

    /**
     * Generates the configuration for a extbase plugin pagination route enhancement
     *
     * @param   array  $options
     *
     * @return array
     */
    public function makeExtBasePaginationConfig(array $options): array
    {
        $options = Options::make($options, $this->getExtBaseSchema([
            'routePath'    => [
                'default' => '/page/{page}',
            ],
            'additional'   => '__UNSET',
            'arguments'    => [
                'default' => ['page' => 'page'],
            ],
            'requirements' => [
                'default' => ['page' => '\\d+'],
            ],
        ]));

        $config = $this->makeExtbaseConfig(
            array_merge(
                $options,
                [
                    'rawOverride' => [],
                    'routePath'   => '/',
                    'additional'  => [
                        [$options['routePath'], $options['arguments']],
                    ],
                    'arguments'   => [],
                ]
            )
        );

        return Arrays::merge(
            $config,
            $options['rawOverride'],
            'nn', 'r'
        );
    }

}
