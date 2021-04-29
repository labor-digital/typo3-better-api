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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Http;


use LaborDigital\T3BA\Event\Configuration\MiddlewareRegistrationEvent;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigApplier;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;

class Applier extends AbstractExtConfigApplier
{
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(MiddlewareRegistrationEvent::class, 'onMiddlewareRegistration');
    }
    
    /**
     * Inject our middleware configuration into the TYPO3 configuration option
     *
     * @param   \LaborDigital\T3BA\Event\Configuration\MiddlewareRegistrationEvent  $event
     */
    public function onMiddlewareRegistration(MiddlewareRegistrationEvent $event): void
    {
        $config = $this->state->get('typo.middleware', []);
        
        $middlewares = $event->getMiddlewares();
        
        if (! empty($config['list'])) {
            foreach (Arrays::makeFromJson($config['list']) as $stack => $list) {
                foreach ($list as $identifier => $middleware) {
                    $middlewares[$stack][$identifier] = $middleware;
                }
            }
        }
        
        if (! empty($config['disabled'])) {
            foreach (Arrays::makeFromJson($config['disabled']) as $stack => $list) {
                foreach ($list as $identifier => $foo) {
                    $middlewares[$stack][$identifier]['disabled'] = true;
                }
            }
        }
        
        $event->setMiddlewares($middlewares);
    }
    
}
