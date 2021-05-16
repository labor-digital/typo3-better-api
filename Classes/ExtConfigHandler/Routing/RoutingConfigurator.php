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
 * Last modified: 2021.05.10 at 17:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\Routing\Exceptions\NotFoundException;
use LaborDigital\T3ba\ExtConfigHandler\Routing\Traits\RouteEnhancerConfigTrait;
use LaborDigital\T3ba\ExtConfigHandler\Routing\Traits\RouteEnhancerSchemaTrait;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Site\Entity\Site;

class RoutingConfigurator extends AbstractExtConfigConfigurator implements NoDiInterface
{
    use RouteEnhancerSchemaTrait;
    use RouteEnhancerConfigTrait;
    
    /**
     * The list of registered route enhancers
     *
     * @var array
     */
    protected $routeEnhancers = [];
    
    /**
     * HttpConfigurator constructor.
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\Site  $site
     */
    public function __construct(Site $site)
    {
        $this->routeEnhancers = $site->getConfiguration()['routeEnhancers'] ?? [];
    }
    
    /**
     * Returns true if a route enhancer with the given key exists either in this configuration or the site.yml
     *
     * @param   string  $key  The unique key of the route enhancer to check
     *
     * @return bool
     */
    public function hasRouteEnhancer(string $key): bool
    {
        try {
            $this->getRouteEnhancer($key);
            
            return true;
        } catch (NotFoundException $e) {
            return true;
        }
    }
    
    /**
     * Returns the raw route enhancer configuration for a specified key
     *
     * @param   string  $key  The unique key of the route enhancer to retrieve
     *
     * @return array
     * @throws \LaborDigital\T3ba\ExtConfigHandler\Routing\Exceptions\NotFoundException
     */
    public function getRouteEnhancer(string $key): array
    {
        $key = $this->context->replaceMarkers($key);
        
        if (! isset($this->routeEnhancers[$key])) {
            throw new NotFoundException('There is no registered route enhancer with key: ' . $key);
        }
        
        return $this->routeEnhancers[$key];
    }
    
    /**
     * Registers a raw route enhancer configuration, like you would in your site.yml.
     *
     * Existing route enhancers (including those in your site.yaml) will be overwritten with the new configuration
     *
     * @param   string  $key     The unique key for this route enhancer
     * @param   array   $config  The is the equivalent of the yaml configuration you would put into your site.config
     *                           file
     *
     * @return $this
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Feature-86365-RoutingEnhancersAndAspects.html
     */
    public function setRouteEnhancer(string $key, array $config): self
    {
        $this->routeEnhancers[$this->context->replaceMarkers($key)] = $this->context->replaceMarkers($config);
        
        return $this;
    }
    
    /**
     * Removes a previously registered route enhancer.
     * This will also remove route enhancers configured in your site.yml!
     *
     * @param   string  $key  The unique key for this route enhancer to remove
     *
     * @return $this
     */
    public function removeRouteEnhancer(string $key): self
    {
        unset($this->routeEnhancers[$this->context->replaceMarkers($key)]);
        
        return $this;
    }
    
    /**
     * Creates a route enhancement for a simple pagination.
     * The route will look like /{page} so page is the property that will end up in your queryArguments
     *
     * @param   string  $key        A unique key for this route enhancement
     * @param   array   $pids       A list of pids on which this route should be activated.
     *                              Pid reference definitions are supported here.
     * @param   array   $options    Additional options for your route
     *                              - raw array: Can be used to define additional, raw route enhancer configuration
     *                              options that will be merged with the generated options.
     *                              - rawOverride array: Similar to "raw" but will be merged into the
     *                              config and OVERRIDE the generated values
     *
     * @return $this
     */
    public function registerPagination(string $key, array $pids, array $options = []): self
    {
        $this->routeEnhancers[$this->context->replaceMarkers($key)]
            = $this->mergeConfig(
            [
                'routePath' => '/{page}',
                'requirements' => [
                    'page' => '\\d+',
                ],
                'defaults' => [
                    'page' => '1',
                ],
                'aspects' => [
                    'page' => [
                        'type' => 'StaticRangeMapper',
                        'start' => '1',
                        'end' => '999',
                    ],
                ],
            ],
            Options::make(
                $this->context->replaceMarkers(
                    array_merge($options, ['pids' => $pids])
                ),
                $this->getDefaultSchema()
            )
        );
        
        return $this;
    }
    
    /**
     * Similar to registerPagination, but creates a pagination route enhancer for an extbase plugin instead.
     * If you already have a route enhancer for the same plugin, you should use the "additional" option instead.
     *
     * @param   string  $key              A unique key for this route enhancement
     * @param   string  $controllerClass  The absolute name of the ext base plugin controller you want to use
     * @param   string  $actionName       The name of the ext base action (without the Action suffix) you want to use.
     * @param   array   $pids             A list of pids on which this route should be activated.
     *                                    Pid reference definitions are supported here.
     * @param   array   $options          Additional configuration parameters for this route.
     *                                    All options defined in {@link registerValueRoute()} will work here as well.
     *                                    In addition to that, the following options are supported:
     *                                    - extension (string): allows you to override the name of the extension
     *                                    your plugin lives in, by default the name of the extension you call this
     *                                    method from.
     *                                    - arguments (array): Allows you to define the mapping of path segment
     *                                    names into plugin argument names. This will be auto-filled by default.
     *                                    - plugin (string): By default the plugin name will be inflected based on the
     *                                    given controller class and action name, however, if multiple plugins match
     *                                    the same controller and action this allows you to define a specific plugin
     *                                    yourself.
     *
     * @return $this*
     */
    public function registerExtBasePagination(
        string $key,
        string $controllerClass,
        string $actionName,
        array $pids,
        array $options = []
    ): self
    {
        $options = Options::make(
            $this->context->replaceMarkers(
                array_merge($options, [
                    'pids' => $pids,
                    'controller' => $controllerClass,
                    'action' => $actionName,
                ])
            ),
            $this->getExtBaseSchema([
                'routePath' => [
                    'default' => '/page/{page}',
                ],
                'additional' => '__UNSET',
                'arguments' => [
                    'default' => ['page' => 'page'],
                ],
                'requirements' => [
                    'default' => ['page' => '\\d+'],
                ],
            ])
        );
        
        $key = $this->context->replaceMarkers($key);
        
        $this->registerExtbasePlugin(
            $key,
            $options['routePath'],
            $options['controller'], $options['action'],
            $options['pids'],
            array_merge(
                $options,
                [
                    'rawOverride' => [],
                    'additional' => [
                        // We abuse the "pagination" feature of the additional route option here
                        // We will remove the second route again below
                        [$options['routePath'], $options['arguments']],
                    ],
                    'arguments' => [],
                ]
            )
        );
        
        $c = &$this->routeEnhancers[$key]['routes'];
        
        $c[0]['_arguments'] = $c[1]['_arguments'];
        array_pop($c);
        
        $this->routeEnhancers[$key] = Arrays::merge(
            $this->routeEnhancers[$key],
            $options['rawOverride'],
            'nn', 'r'
        );
        
        return $this;
        
    }
    
    /**
     * Creates a route enhancement that works with values, either static (StaticValueMapper) or fetched from the
     * database (PersistedAliasMapper). It requires you to define a route path that contains the part definition
     *
     * @param   string  $key        A unique key for this route enhancement
     * @param   string  $routePath  A route path (e.g /my-key/{myValue}/{myOtherValue}) This defines how your route
     *                              will look like in the url and the segments(parts) you can retrieve in the query
     *                              arguments.
     * @param   array   $pids       A list of pids on which this route should be activated.
     *                              Pid reference definitions are supported here.
     * @param   array   $options    Additional configuration parameters for this route
     *                              - defaults array: By default all segments are required for a route to be matched
     *                              however you can define default values as an key => value array to make segments
     *                              optional
     *                              - requirements array: By default all segments in your route path have to match the
     *                              pattern: [a-zA-Z0-9\-_.]* If you want to modify the pattern for one or multiple
     *                              segments of your route you can define them as a key => pattern array here.
     *                              All segments that are in your routePath but not in your requirements array
     *                              will automatically be set to the pattern above.
     *                              - dbArgs array: This is a simplified configuration for the "PersistedAliasMapper".
     *                              You can define segments that should be gathered from the database by defining them
     *                              as key => [$tableName, $fieldName, ($pidConstraint)] array. Your $tableName can use
     *                              the short table syntax also used in the table config option.
     *                              -- If the third parameter is either a PID, or a list of PIDs,
     *                              the used records are constrained to the storage PIDs given.
     *                              - localeArgs array: A simplified configuration of the "LocaleModifier".
     *                              You can define segments and their language specific variants as an array
     *                              like key => [$defaultValue, "de_DE.*" => $germanValue, ...]. As you see,
     *                              the first value HAS to be the default value, all other variants are simply
     *                              defined by their locale selector.
     *                              - staticArgs array: A simplified configuration for the "StaticValueMapper".
     *                              You can define static values that should be mapped to a specific path segment.
     *                              To define your segments provide an array like key => [$value => $urlValue].
     *                              This can be used to map month names to numeric values or categories to id's.
     *                              Note: Arrays as $urlValues will be treated as translation definitions.
     *                              if you provide an array like: key => [$value => [$defaultValue, "de_DE.*" =>
     *                              $germanValue, ...]] the script will automatically translate your value in the
     *                              languages you provided a specific value for. When the route is parsed TYPO3 will
     *                              re-map the translated value to the real value
     *                              - raw array: Can be used to define additional, raw route enhancer configuration
     *                              options that will be merged with the generated options.
     *                              - rawOverride array: Similar to "raw" but will be merged into the
     *                              config and OVERRIDE the generated values
     *
     * @return $this
     */
    public function registerValueRoute(
        string $key,
        string $routePath,
        array $pids,
        array $options = []
    ): self
    {
        $options = Options::make(
            $this->context->replaceMarkers(
                array_merge($options, ['pids' => $pids, 'routePath' => $routePath])
            ), $this->getRoutePathSchema()
        );
        
        $this->routeEnhancers[$this->context->replaceMarkers($key)] = $this->mergeConfig([
            'routePath' => $options['routePath'],
            'requirements' => $options['requirements'],
        ], $options);
        
        return $this;
    }
    
    /**
     * Works exactly the same way registerValueRoute does() but is designed specially for ext base plugins.
     *
     * Contrary to the default TYPO3 Extbase route enhancer this method expects you to register a route
     * for the routes that can be handled by a single action of a plugin. This makes the definition less
     * cluttered and resolves the need of too much configuration
     *
     * @param   string  $key              A unique key for this route enhancement
     * @param   string  $routePath        A route path (e.g /my-key/{myValue}/{myOtherValue}) This defines how your
     *                                    route will look like in the url and the segments(parts) you can retrieve in
     *                                    your controller action.
     * @param   string  $controllerClass  The absolute name of the ext base plugin controller you want to use
     * @param   string  $actionName       The name of the ext base action (without the Action suffix) you want to use.
     * @param   array   $pids             A list of pids on which this route should be activated.
     *                                    Pid reference definitions are supported here.
     * @param   array   $options          Additional configuration parameters for this route.
     *                                    All options defined in {@link registerValueRoute()} will work here as well.
     *                                    In addition to that, the following options are supported:
     *                                    - extension (string): allows you to override the name of the extension
     *                                    your plugin lives in, by default the name of the extension you call this
     *                                    method from.
     *                                    - arguments (array): Allows you to define the mapping of path segment
     *                                    names into plugin argument names. This will be auto-filled by default.
     *                                    - plugin (string): By default the plugin name will be inflected based on the
     *                                    given controller class and action name, however, if multiple plugins match
     *                                    the same controller and action this allows you to define a specific plugin
     *                                    yourself.
     *                                    - additional (array): Can be used to register additional child-routes
     *                                    inside the registered route. This can be used for pagination or
     *                                    filtering routes, inside the parent. See the example below, on how to
     *                                    configure additional routes.
     *
     *
     * "Additional" routes
     * -----------------------------------
     * The option "additional" allows you to register additional child routes that are relative
     * to the $routePath. Meaning an additional route like "categories/{category}" will be merged
     * with the route path like: "/$routePath/categories/{category}".
     *
     * Let's assume we have a $routePath of "/authors/{author}",
     * and use "dbArgs" to map the author segment based on a "slug" field on the author table.
     *
     * Pagination routes:
     * We want to register a pagination of articles of the author,
     * all additional routes that contain the "{page}" segment will automatically be mapped as pagination route for
     * you, so no additional configuration is required. You should simply use the f:widget.pagination widget to create
     * the pagination in your template. NOTE: In your widget, you MUST set configuration="{addQueryStringMethod:
     * 'GET'}" in order for the enhancer to work!
     *
     * "additional" => [
     *      "/article-page/{page}"
     * ]
     *
     * The result is a final route path of /authors/{author}/article-page/{page}
     *
     * Argument mapping:
     * Additional routes can not only be defined as string, but also as array. The first parameter
     * is the path segment we have used already, but the second is the "argument" mapping array
     * you already know from the extbase route enhancer. The "key" is the name of the segment, the "value" is the
     * mapped argument name.
     *
     * "additional" => [
     *      // By default a pagination route would map to @widget_0, here we forcefully use @widget_1 instead.
     *      ["/article-page/{page}", ["page" => "@widget_1/currentPage"]]
     * ]
     *
     * @return $this
     */
    public function registerExtbasePlugin(
        string $key,
        string $routePath,
        string $controllerClass,
        string $actionName,
        array $pids,
        array $options = []
    ): self
    {
        $options = Options::make(
            $this->context->replaceMarkers(
                array_merge($options, [
                    'pids' => $pids,
                    'routePath' => $routePath,
                    'controller' => $controllerClass,
                    'action' => $actionName,
                ])
            ), $this->getExtBaseSchema()
        );
        
        $controllerDefinition = NamingUtil::controllerAliasFromClass($options['controller'])
                                . '::' . $options['action'];
        
        $config = [
            'type' => 'Extbase',
            'extension' => $options['extension'],
            'plugin' => $options['plugin'],
            'requirements' => $options['requirements'],
            'routes' => [
                array_filter([
                    'routePath' => $options['routePath'],
                    '_controller' => $controllerDefinition,
                    '_arguments' => $options['arguments'],
                ]),
            ],
            'defaultController' => $controllerDefinition,
        ];
        
        if (! empty($options['additional'])) {
            foreach ($options['additional'] as $route) {
                $isPaginatorRoute = isset($route[1]['page']);
                
                if ($isPaginatorRoute) {
                    $options['defaults']['page'] = $options['defaults']['page'] ?? 0;
                    $options['requirements']['page'] = $options['requirements']['page'] ?? '\\d+';
                    
                    if ($route[1]['page'] === 'page') {
                        $route[1]['page'] = '@widget_0/currentPage';
                    }
                    
                    if (! isset($config['aspects']['page'])) {
                        $config['aspects']['page'] = [
                            'type' => 'StaticRangeMapper',
                            'start' => '1',
                            'end' => '999',
                        ];
                    }
                }
                
                $realRoutePath = rtrim($options['routePath'], '/ ') . '/' . trim($route[0], '/ ');
                $config['routes'][] = array_filter([
                    'routePath' => $realRoutePath,
                    '_controller' => $controllerDefinition,
                    '_arguments' => array_merge($options['arguments'], $route[1]),
                ]);
            }
        }
        
        $this->routeEnhancers[$this->context->replaceMarkers($key)] = $this->mergeConfig($config, $options);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->setAsJson('routeEnhancers', $this->routeEnhancers, true);
    }
}
