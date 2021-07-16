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
 * Last modified: 2021.07.16 at 16:15
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing;


use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Inflection\Inflector;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use Psr\Http\Server\MiddlewareInterface;

class RoutingConfigurator extends AbstractExtConfigConfigurator implements NoDiInterface
{
    protected const MIDDLEWARE_STACK_SCHEMA
        = [
            'type' => 'string',
            'default' => 'frontend',
            'values' => ['frontend', 'backend'],
        ];
    
    /**
     * The list of registered middleware configurations, sorted by "frontend" and "backend" stack
     *
     * @var array[]
     */
    protected $middlewares = [];
    
    /**
     * The list of disabled middleware identifiers, sorted by "frontend" and "backend" stack
     *
     * @var array
     */
    protected $disabledMiddlewares = [];
    
    /**
     * Additional global configuration options
     *
     * @var array
     */
    protected $globals = [];
    
    /**
     * Registered backend routing configuration options
     *
     * @var array
     */
    protected $backendRoutes = [];
    
    /**
     * Registered backend ajax routing configuration options
     *
     * @var array
     */
    protected $backendAjaxRoutes = [];
    
    /**
     * Registers a new middleware class to the stack.
     *
     * @param   string  $middlewareClass       The class to register. This class MUST implement the Middleware
     *                                         interface
     * @param   array   $options               Additional options for this middleware
     *                                         - stack string (frontend): Either "frontend" or "backend" to select
     *                                         the middleware stack to add this class to
     *                                         - identifier string: By default the middleware identifier is calculated
     *                                         based on the class name. If you set this you can overwrite the default.
     *                                         - before array|string: A list of or a single, middleware identifier to
     *                                         place this middleware in front of
     *                                         - after array|string: A list of or a single, middleware identifier to
     *                                         place this middleware after
     *
     * @return $this
     */
    public function registerMiddleware(
        string $middlewareClass,
        array $options = []
    ): self
    {
        if (! class_exists($middlewareClass)) {
            throw new InvalidArgumentException('The given middleware class: ' . $middlewareClass . ' does not exist!');
        }
        
        if (! in_array(MiddlewareInterface::class, class_implements($middlewareClass), true)) {
            throw new InvalidArgumentException(
                'The given middleware class: ' . $middlewareClass
                . ' does not implement the required interface: ' . MiddlewareInterface::class . '!');
        }
        
        $beforeAfterDefinition = [
            'type' => 'array',
            'default' => [],
            'preFilter' => static function ($v) { return is_string($v) ? [$v] : $v; },
            'filter' => function (array $v) {
                foreach ($v as $k => $identifier) {
                    if (class_exists($identifier)) {
                        $v[$k] = $this->makeMiddlewareIdentifier($identifier);
                    }
                }
                
                return $v;
            },
        ];
        
        $options = Options::make($options, [
            'identifier' => [
                'type' => 'string',
                'default' => function () use ($middlewareClass) {
                    return $this->makeMiddlewareIdentifier($middlewareClass);
                },
            ],
            'stack' => static::MIDDLEWARE_STACK_SCHEMA,
            'before' => $beforeAfterDefinition,
            'after' => $beforeAfterDefinition,
        ]);
        
        $stack = $options['stack'];
        $identifier = $options['identifier'];
        
        if (isset($this->disabledMiddlewares[$stack][$identifier])) {
            return $this;
        }
        
        $this->middlewares[$stack][$identifier] = [
            'target' => $middlewareClass,
            'before' => $options['before'],
            'after' => $options['after'],
        ];
        
        return $this;
    }
    
    /**
     * Can be used to disable a previously registered middleware.
     *
     * @param   string       $middlewareClassOrIdentifier  Either a middleware class or an identifier to disable
     * @param   string|null  $stack                        Either "frontend" or "backend" to select the middleware
     *                                                     stack to remove the class from
     *
     * @return $this
     */
    public function disableMiddleware(
        string $middlewareClassOrIdentifier,
        ?string $stack = null
    ): self
    {
        $stack = Options::makeSingle('stack', $stack, static::MIDDLEWARE_STACK_SCHEMA);
        
        if (class_exists($middlewareClassOrIdentifier)) {
            $middlewareClassOrIdentifier = $this->makeMiddlewareIdentifier($middlewareClassOrIdentifier);
        }
        
        $this->disabledMiddlewares[$stack][$middlewareClassOrIdentifier] = true;
        
        return $this;
    }
    
    /**
     * Registers a new route aspect handler
     *
     * @param   string  $key        The short name / type for this aspect to be registered with
     * @param   string  $className  The name of the handler class to use as handler
     *
     * @return $this
     */
    public function registerRouteAspectHandler(string $key, string $className): self
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException(
                'The given route aspect handler ' . $className . ' class does not exist!');
        }
        
        $this->globals['TYPO3_CONF_VARS']['SYS']['routing']['aspects'][$key] = $className;
        
        return $this;
    }
    
    /**
     * Registers a new route for the TYPO3 Backend.
     *
     * Each request to the backend is eventually executed by a controller. A list of routes is defined
     * which maps a given request to a controller and an action.
     *
     * WARNING: Backend controllers don't support dependency injection!
     *
     * @param   string  $path             The path that should be mapped to the registered controller (e.g. /some/path)
     * @param   string  $controllerClass  The name of the controller class that should be mapped
     * @param   string  $actionMethod     The FULL name of the action method inside the controller class to use.
     * @param   array   $options          Additional options to configure the route
     *                                    - name string: Allows you to define a speaking name for your route.
     *                                    If omitted, the name is auto-generated based on the given $path
     *                                    - public bool (FALSE): Public backend routes do not require any session token,
     *                                    but can be used to redirect to a route that requires a session token internally
     *                                    - referrerRequired bool (FALSE): enforces existence of HTTP Referer header that
     *                                    has to match the currently used backend URL (e.g. https://example.org/typo3/),
     *                                    the request will be denied otherwise.
     *                                    - refreshEmpty bool (FALSE): triggers a HTML based refresh in case HTTP Referer
     *                                    header is not given or empty - this attempt uses an HTML refresh, since regular
     *                                    HTTP Location redirect still would not set a referrer. It implies this technique
     *                                    should only be used on plain HTML responses and won’t have any impact e.g. on
     *                                    JSON or XML response types.
     *                                    - ajax bool (FALSE): If set to true, the route will be registered as "ajax" route.
     *                                    - parameters array: Optional, additional GET parameters to always be appended to the
     *                                    generated route path
     *                                    - raw array: Raw configuration array to merge into the generated config,
     *                                    to implement features not supported by this facade
     *
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/BackendRouting/Index.html
     */
    public function registerBackendRoute(string $path, string $controllerClass, string $actionMethod, array $options = []): self
    {
        if (! class_exists($controllerClass)) {
            throw new InvalidArgumentException(
                'The given route controller ' . $controllerClass . ' class does not exist!');
        }
        
        $ref = new \ReflectionClass($controllerClass);
        if (! $ref->hasMethod($actionMethod) ||
            ! $ref->getMethod($actionMethod)->isPublic() ||
            $ref->getMethod($actionMethod)->isStatic()) {
            throw new InvalidArgumentException(
                'The given route action method ' . $actionMethod . ' does not exist as public method in class: ' .
                $controllerClass . '!');
        }
        
        $path = '/' . ltrim(trim($path), '/');
        
        $options = Options::make($options, [
            'name' => [
                'type' => 'string',
                'default' => str_replace('-', '_', Inflector::toFile($path)),
            ],
            'ajax' => [
                'type' => 'bool',
                'default' => false,
            ],
            'public' => [
                'type' => 'bool',
                'default' => false,
            ],
            'parameters' => [
                'type' => 'array',
                'default' => [],
            ],
            'referrerRequired' => [
                'type' => 'bool',
                'default' => false,
            ],
            'refreshEmpty' => [
                'type' => 'bool',
                'default' => false,
            ],
            'raw' => [
                'type' => 'array',
                'default' => [],
            ],
        ]);
        
        $definition = [
            'path' => $path,
            'target' => $controllerClass . '::' . $actionMethod,
        ];
        
        if ($options['public']) {
            $definition['access'] = 'public';
        }
        if (! empty($options['parameters'])) {
            $definition['parameters'] = $options['parameters'];
        }
        $referrer = $options['referrerRequired'] ? 'required' : '';
        if ($options['refreshEmpty']) {
            $referrer .= ',refresh-empty';
        }
        if (! empty($referrer)) {
            $definition['referrer'] = ltrim($referrer, ',');
        }
        $definition = array_merge($definition, $options['raw']);
        
        $field = $options['ajax'] ? 'backendAjaxRoutes' : 'backendRoutes';
        $this->$field[$options['name']] = $definition;
        
        return $this;
    }
    
    /**
     * Builds an automatic middleware identifier out of the given class name and the extension key
     *
     * @param   string  $className  The name of the class to generate the middleware identifier for
     *
     * @return string
     */
    protected function makeMiddlewareIdentifier(string $className): string
    {
        $nsPos = strpos($className, '\\');
        
        if ($nsPos !== false) {
            $nsPos = strpos($className, '\\', $nsPos + 1);
        }
        
        if ($nsPos === false) {
            $classVendor = Inflector::toDashed($this->context->getExtKeyWithVendor());
        } else {
            $classParts = explode('\\', substr($className, 0, $nsPos));
            $classVendor = Inflector::toDashed($classParts[0] ?? '') . '/' . Inflector::toDashed($classParts[1] ?? '');
        }
        
        return implode(
            '/',
            [
                $classVendor,
                Inflector::toDashed(Path::classBasename($className)),
                md5($className),
            ]
        );
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->setAsJson('middleware.list', $this->middlewares);
        $state->setAsJson('middleware.disabled', $this->disabledMiddlewares);
        $state->setAsJson('backendRoutes.default', $this->backendRoutes);
        $state->setAsJson('backendRoutes.ajax', $this->backendAjaxRoutes);
        $state->mergeIntoArray('globals', $this->globals);
    }
}
