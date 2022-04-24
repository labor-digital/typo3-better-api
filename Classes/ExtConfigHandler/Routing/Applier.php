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
 * Last modified: 2021.07.16 at 16:16
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\Configuration\BackendRouteRegistrationEvent;
use LaborDigital\T3ba\Event\Configuration\MiddlewareRegistrationEvent;
use LaborDigital\T3ba\Event\Core\SiteActivatedEvent;
use LaborDigital\T3ba\Event\Core\SiteConfigFilterEvent;
use LaborDigital\T3ba\Event\Frontend\HashBaseArgFilterEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\ExtConfigHandler\Routing\Site\Util\NoCacheArgsProvider;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class Applier extends AbstractExtConfigApplier
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(MiddlewareRegistrationEvent::class, 'onMiddlewareRegistration');
        $subscription->subscribe(BackendRouteRegistrationEvent::class, 'onBeRouteRegistration');
        $subscription->subscribe(SiteConfigFilterEvent::class, 'onSiteConfigFilter');
        $subscription->subscribe(SiteActivatedEvent::class, 'onSiteActivated');
        $subscription->subscribe(HashBaseArgFilterEvent::class, 'onHashBaseFilter');
    }
    
    /**
     * Inject our middleware configuration into the TYPO3 configuration option
     *
     * @param   \LaborDigital\T3ba\Event\Configuration\MiddlewareRegistrationEvent  $event
     */
    public function onMiddlewareRegistration(MiddlewareRegistrationEvent $event): void
    {
        $config = $this->state->get('typo.middleware', []);
        
        $middlewares = $event->getMiddlewares();
        
        if (! empty($config['list'])) {
            foreach (SerializerUtil::unserializeJson($config['list']) ?? [] as $stack => $list) {
                foreach ($list as $identifier => $middleware) {
                    $middlewares[$stack][$identifier] = $middleware;
                }
            }
        }
        
        if (! empty($config['disabled'])) {
            foreach (SerializerUtil::unserializeJson($config['disabled']) ?? [] as $stack => $list) {
                foreach ($list as $identifier => $foo) {
                    $middlewares[$stack][$identifier]['disabled'] = true;
                }
            }
        }
        
        $event->setMiddlewares($middlewares);
    }
    
    /**
     * Injects the collected backend routes into the TYPO3 configuration
     *
     * @param   \LaborDigital\T3ba\Event\Configuration\BackendRouteRegistrationEvent  $e
     */
    public function onBeRouteRegistration(BackendRouteRegistrationEvent $e): void
    {
        $backendRoutes = $this->state->get('typo.backendRoutes.' . ($e->isAjax() ? 'ajax' : 'default'));
        if ($backendRoutes) {
            $e->setRoutes(
                array_merge(
                    $e->getRoutes(),
                    SerializerUtil::unserializeJson($backendRoutes)
                )
            );
        }
    }
    
    /**
     * Injects the route enhancers into the site config array
     *
     * @param   \LaborDigital\T3ba\Event\Core\SiteConfigFilterEvent  $e
     */
    public function onSiteConfigFilter(SiteConfigFilterEvent $e): void
    {
        $routeEnhancers = $this->state->get('typo.site.*.routeEnhancers');
        $config = $e->getConfig();
        
        foreach (array_keys($config) as $key) {
            if (! is_string($routeEnhancers[$key])) {
                continue;
            }
            
            $config[$key]['routeEnhancers'] = SerializerUtil::unserializeJson($routeEnhancers[$key]) ?? [];
        }
        
        $e->setConfig($config);
    }
    
    /**
     * Updates the cHash configuration for the registered no cache args when the site changes
     *
     * @return void
     */
    public function onSiteActivated(): void
    {
        $this->makeInstance(NoCacheArgsProvider::class)->updateCHashCalculator();
    }
    
    /**
     * Removes all registered no cache args from the hash base in the TSFE
     *
     * @param   \LaborDigital\T3ba\Event\Frontend\HashBaseArgFilterEvent  $event
     *
     * @return void
     */
    public function onHashBaseFilter(HashBaseArgFilterEvent $event): void
    {
        $noCacheArgs = $this->makeInstance(NoCacheArgsProvider::class)->getNoCacheArgs();
        if (empty($noCacheArgs)) {
            return;
        }
        
        $hashArgs = $event->getArgs();
        foreach ($noCacheArgs as $noCacheArg) {
            $hashArgs['staticRouteArguments'] = $this->removeFromArgumentsArray(
                $noCacheArg, $hashArgs['staticRouteArguments'] ?? []);
            $hashArgs['dynamicArguments'] = $this->removeFromArgumentsArray(
                $noCacheArg, $hashArgs['dynamicArguments'] ?? []);
        }
        
        $event->setArgs($hashArgs);
    }
    
    /**
     * Internal helper to recursively remove a query argument from a list of existing arguments
     *
     * @param   string  $arg
     * @param   array   $args
     *
     * @return array
     */
    protected function removeFromArgumentsArray(string $arg, array $args): array
    {
        if (empty($args)) {
            return $args;
        }
        
        parse_str($arg, $parsed);
        
        $walker = static function (array $list, array $removeList, \Closure $walker): ?array {
            foreach ($list as $lk => $lv) {
                if (! isset($removeList[$lk])) {
                    continue;
                }
                
                if (! is_array($removeList[$lk])) {
                    unset($list[$lk]);
                    continue;
                }
                
                if (is_array($lv)) {
                    $lv = $walker($lv, $removeList[$lk], $walker);
                    if (! $lv) {
                        unset($list[$lk]);
                        continue;
                    }
                    $list[$lk] = $lv;
                }
            }
            
            return empty($list) ? null : $list;
        };
        
        return $walker($args, $parsed, $walker);
    }
}
