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
 * Last modified: 2020.03.18 at 19:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\Http;


use LaborDigital\Typo3BetterApi\Event\Events\MiddlewareRegistrationEvent;
use LaborDigital\Typo3BetterApi\Event\Events\SiteConfigFilterEvent;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class HttpConfigOption extends AbstractExtConfigOption {
	
	/**
	 * The list of middleware factories by their class name
	 * @var array
	 */
	protected $middlewareFactories = [];
	
	/**
	 * A list of route enhancer filters that were registered
	 * @var array
	 */
	protected $routeEnhancerFilters = [];
	
	/**
	 * @inheritDoc
	 */
	public function subscribeToEvents(EventSubscriptionInterface $subscription) {
		$subscription->subscribe(SiteConfigFilterEvent::class, "__injectRouteEnhancers");
		$subscription->subscribe(MiddlewareRegistrationEvent::class, "__injectMiddlewares", ["priority" => 500]);
	}
	
	/**
	 * Registers a new, raw route enhancer configuration.
	 *
	 * @param string $key    The unique key for this route enhancer
	 * @param array  $config The is the equivalent of the yaml configuration you would put into your site.config file
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Feature-86365-RoutingEnhancersAndAspects.html
	 */
	public function registerRouteEnhancer(string $key, array $config): HttpConfigOption {
		return $this->addToCachedValueConfig("routeEnhancer", [
			"routeType" => "raw",
			"config"    => $config,
		], $key);
	}
	
	/**
	 * Creates a route enhancement for a simple pagination.
	 * The route will look like /{page} so page is the property that will end up in your queryArguments
	 *
	 * @param string $key     A unique key for this route enhancement
	 * @param array  $pids    A list of pids on which this route should be activated.
	 *                        Pid reference definitions are supported here.
	 * @param array  $options Additional options for your route
	 *                        - site string: Defines the siteKey when you are running a multi-site setup
	 *                        - raw array: Can be used to define additional, raw route enhancer configuration options
	 *                        that will be merged with the generated options.
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 */
	public function registerPaginationRoute(string $key, array $pids, array $options = []): HttpConfigOption {
		$options["pids"] = $pids;
		$options["routeType"] = "pagination";
		return $this->addToCachedValueConfig("routeEnhancer", $options, $key);
	}
	
	/**
	 * Creates a route enhancement that works with values, either static (StaticValueMapper) or fetched from the
	 * database (PersistedAliasMapper). It requires you to define a route path that contains the part definition
	 *
	 * @param string $key       A unique key for this route enhancement
	 * @param string $routePath A route path (e.g /my-key/{myValue}/{myOtherValue}) This defines how your route will
	 *                          look like in the url and the segments(parts) you can retrieve in the query arguments.
	 * @param array  $pids      A list of pids on which this route should be activated.
	 *                          Pid reference definitions are supported here.
	 * @param array  $options   Additional configuration parameters for this route
	 *                          - site string: Defines the siteKey when you are running a multi-site setup
	 *                          - raw array: Can be used to define additional, raw route enhancer configuration options
	 *                          that will be merged with the generated options.
	 *                          - defaults array: By default all segments are required for a route to be matched
	 *                          however you can define default values as an key => value array to make segments
	 *                          optional
	 *                          - requirements array: By default all segments in your route path have to match the
	 *                          pattern: [a-zA-Z0-9\-_.]* If you want to modify the pattern for one or multiple
	 *                          segments of your route you can define them as a key => pattern array here.
	 *                          All segments that are in your routePath but not in your requirements array
	 *                          will automatically be set to the pattern above.
	 *                          - dbArgs array: This is a simplified configuration for the "PersistedAliasMapper".
	 *                          You can define segments that should be gathered from the database by defining them
	 *                          as key => [$tableName, $fieldName] array. Your $tableName can use the short table
	 *                          syntax also used in the table config option.
	 *                          - localeArgs array: A simplified configuration of the "LocaleModifier".
	 *                          You can define segments and their language specific variants as an array
	 *                          like key => [$defaultValue, "de_DE.*" => $germanValue, ...]. As you see,
	 *                          the first value HAS to be the default value, all other variants are simply
	 *                          defined by their locale selector.
	 *                          - staticArgs array: A simplified configuration for the "StaticValueMapper".
	 *                          You can define static values that should be mapped to a specific path segment.
	 *                          To define your segments provide an array like key => [$value => $urlValue].
	 *                          This can be used to map month names to numeric values or categories to id's.
	 *                          Note: Arrays as $urlValues will be treated as translation definitions.
	 *                          if you provide an array like: key => [$value => [$defaultValue, "de_DE.*" =>
	 *                          $germanValue, ...]] the script will automatically translate your value in the languages
	 *                          you provided a specific value for. When the route is parsed TYPO3 will re-map the
	 *                          translated value to the real value
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 */
	public function registerValueRoute(string $key, string $routePath, array $pids, array $options = []): HttpConfigOption {
		$options["pids"] = $pids;
		$options["routeType"] = "value";
		$options["routePath"] = $routePath;
		return $this->addToCachedValueConfig("routeEnhancer", $options, $key);
	}
	
	/**
	 * Registers a new route enhancer filter to the stack. The filter will receive both the key, and the configuration
	 * of all registered route enhancers and can filter the configuration to your liking.
	 *
	 * @param callable $filter
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 */
	public function registerRouteEnhancerFilter(callable $filter): HttpConfigOption {
		$this->routeEnhancerFilters[] = $filter;
		return $this;
	}
	
	/**
	 * Registers a new middleware class to the stack.
	 *
	 * @param string $middlewareClass The class to register. This class MUST implement the Middleware interface
	 * @param string $target          Either "frontend" or "backend" to select the middleware stack to add this class
	 *                                to
	 * @param array  $options         Additional options for this middleware
	 *                                - identifier string: By default the middleware identifier is calculated based on
	 *                                the class name. If you set this you can overwrite the default.
	 *                                - factory callable: Can be used to supply a factory callable that is used to
	 *                                create a new instance of the middleware, if the default resolver does not cut
	 *                                it...
	 *                                - before array|string: A list of or a single, middleware identifier to place this
	 *                                middleware in front of
	 *                                - after array|string: A list of or a single, middleware identifier to place this
	 *                                middleware after
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 */
	public function registerMiddleware(string $middlewareClass, string $target = "frontend", array $options = []): HttpConfigOption {
		// Store the factory
		if (isset($options["factory"])) $this->middlewareFactories[$middlewareClass] = $options["factory"];
		unset($options["factory"]);
		
		// Store the config
		return $this->addToCachedValueConfig("middlewares", [
			"class"   => $middlewareClass,
			"target"  => $target,
			"options" => $options,
		], $middlewareClass . $target);
	}
	
	/**
	 * Can be used to disable a previously registered middleware.
	 *
	 * @param string $middlewareClassOrIdentifier Either a middleware class or an identifier to disable
	 * @param string $target                      Either "frontend" or "backend" to select the middleware stack to
	 *                                            remove the class from
	 *
	 * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\Http\HttpConfigOption
	 */
	public function disableMiddleware(string $middlewareClassOrIdentifier, string $target = "frontend"): HttpConfigOption {
		return $this->addToCachedValueConfig("disabledMiddlewares", [
			"classOrIdentifier" => $middlewareClassOrIdentifier,
			"target"            => $target,
		]);
	}
	
	/**
	 * Applies the middleware configuration that is provided for the typo3 stack resolver
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\MiddlewareRegistrationEvent $event
	 */
	public function __injectMiddlewares(MiddlewareRegistrationEvent $event) {
		
		// Get the middleware configuration
		$middlewares = $this->getCachedValueOrRun("middlewareConfig", function () {
			return $this->context->getInstanceOf(MiddlewareConfigGenerator::class)->generate(
				$this->getCachedValueConfig("middlewares"),
				$this->getCachedValueConfig("disabledMiddlewares")
			);
		});
		
		// Create the middleware instances
		foreach ($middlewares as $target => $targetGroup) {
			foreach ($targetGroup as $identifier => $config) {
				if (empty($config["target"])) continue;
				if (!isset($this->middlewareFactories[$config["target"]])) continue;
				$factory = $this->middlewareFactories[$config["target"]];
				if (is_array($factory) && (class_exists($factory[0]) || interface_exists($factory[0])))
					$factory[0] = $this->context->getInstanceOf($factory[0]);
				$instance = call_user_func($factory, $this->context->Container, $config["target"], $config);
				$middlewares[$target][$identifier]["target"] = $instance;
			}
		}
		
		// Done
		$event->setMiddlewares($middlewares);
	}
	
	/**
	 * Internal helper to inject the generated route enhancers into the site configuration array
	 *
	 * @param \LaborDigital\Typo3BetterApi\Event\Events\SiteConfigFilterEvent $event
	 *
	 * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
	 */
	public function __injectRouteEnhancers(SiteConfigFilterEvent $event) {
		
		// Build the route enhancer list
		$routeEnhancers = $this->runCachedValueGenerator("routeEnhancer", RouteEnhancerConfigGenerator::class);
		foreach ($routeEnhancers as $k => $config) {
			foreach ($this->routeEnhancerFilters as $filter) {
				$r = call_user_func($filter, $k, $config);
				if (!is_array($r)) throw new ExtConfigException("A route enhancer filter has to return an array!");
				$routeEnhancers[$k] = $r;
			}
		}
		
		// Ignore if there is nothing to do
		if (empty($routeEnhancers)) return;
		
		// Inject the route enhancers into the site config
		$siteConfig = $event->getConfig();
		$isMultiSite = count($siteConfig) > 1;
		$defaultSite = key($siteConfig);
		foreach ($routeEnhancers as $k => $config) {
			// Select the site key
			$site = $config["@site"];
			unset($config["@site"]);
			if (empty($site)) {
				if ($isMultiSite) throw new ExtConfigException("Your route enhancer: $k did not define a site key it should be registered for! If you are running in a multi-site setup your route enhancers have to specify a \"site\" option.");
				$site = $defaultSite;
			}
			if (!isset($siteConfig[$site]))
				throw new ExtConfigException("Your route enhancer: $k specified a site: $site which does not exist!");
			
			// Inject the config
			$siteConfig = Arrays::setPath($siteConfig, [$site, "routeEnhancers", $k], $config);
		}
		$event->setConfig($siteConfig);
	}
	
}