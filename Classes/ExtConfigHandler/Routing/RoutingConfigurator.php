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
 * Last modified: 2021.05.16 at 16:09
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
     * Builds an automatic middleware identifier out of the given class name and the extension key
     *
     * @param   string  $className  The name of the class to generate the middleware identifier for
     *
     * @return string
     */
    protected function makeMiddlewareIdentifier(string $className): string
    {
        return implode(
            '/',
            [
                Inflector::toDashed($this->context->getExtKeyWithVendor()),
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
        $state->mergeIntoArray('globals', $this->globals);
    }
}
