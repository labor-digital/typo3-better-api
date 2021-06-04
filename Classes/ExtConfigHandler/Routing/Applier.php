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
 * Last modified: 2021.06.04 at 16:24
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing;


use LaborDigital\T3ba\Event\Configuration\MiddlewareRegistrationEvent;
use LaborDigital\T3ba\Event\Core\SiteConfigFilterEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class Applier extends AbstractExtConfigApplier
{
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(MiddlewareRegistrationEvent::class, 'onMiddlewareRegistration');
        $subscription->subscribe(SiteConfigFilterEvent::class, 'onSiteConfigFilter');
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
    
}
