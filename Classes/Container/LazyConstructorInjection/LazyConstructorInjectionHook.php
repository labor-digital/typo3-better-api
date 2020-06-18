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
 * Last modified: 2020.03.19 at 13:04
 */

namespace LaborDigital\Typo3BetterApi\Container\LazyConstructorInjection;

use LaborDigital\Typo3BetterApi\Event\Events\ClassSchemaFilterEvent;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\EventBus\Subscription\LazyEventSubscriberInterface;
use ReflectionObject;
use TYPO3\CMS\Core\SingletonInterface;

class LazyConstructorInjectionHook implements LazyEventSubscriberInterface, SingletonInterface
{
    
    /**
     * The list of schemata we already processed
     *
     * @var array
     */
    protected $knownSchemata = [];
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription)
    {
        $subscription->subscribe(ClassSchemaFilterEvent::class, '__filterClassSchema');
    }
    
    /**
     * Event hook that scans all class schemata to make sure to inject lazy loading proxy objects instead of the real
     * object when an interface is given and the argument starts with "lazy"
     *
     * @param   \LaborDigital\Typo3BetterApi\Event\Events\ClassSchemaFilterEvent  $e
     */
    public function __filterClassSchema(ClassSchemaFilterEvent $e)
    {
        $schema = $e->getSchema();
        
        // Check if this schema is already adjusted
        if (isset($this->knownSchemata[spl_object_id($schema)])) {
            return;
        }
        
        // Ignore if we don't have a constructor
        if (! $schema->hasMethod('__construct')) {
            return;
        }
        
        // Check if we have a "lazy" parameter
        $constructor = $schema->getMethod('__construct');
        if (empty($constructor['params'])) {
            return;
        }
        $updateRequired = false;
        foreach ($constructor['params'] as $k => $conf) {
            if (substr($k, 0, 4) !== 'lazy') {
                continue;
            }
            $className = $conf['dependency'];
            if (! empty($conf['lazyInjectionTarget'])) {
                $className = $conf['lazyInjectionTarget'];
            }
            if (! interface_exists($className)) {
                continue;
            }
            
            // Build the proxy class and provide it in the autoloader
            $proxyClassName                                   = LazyObjectProxyGenerator::getInstance()
                                                                                        ->provideProxyForClassSchemaParameter($className);
            $constructor['params'][$k]['dependency']          = $proxyClassName;
            $constructor['params'][$k]['type']                = $proxyClassName;
            $constructor['params'][$k]['class']               = $proxyClassName;
            $constructor['params'][$k]['lazyInjectionTarget'] = $className;
            
            // Don't update already modified objects
            $updateRequired = empty($conf['lazyInjectionTarget']);
        }
        
        // Update the schema if required
        if ($updateRequired) {
            $methods                = $schema->getMethods();
            $methods['__construct'] = $constructor;
            
            // Force the schema to get our overwritten value
            $ref  = new ReflectionObject($schema);
            $prop = $ref->getProperty('methods');
            $prop->setAccessible(true);
            $prop->setValue($schema, $methods);
        }
        
        // Mark as processed
        $this->knownSchemata[spl_object_id($schema)] = true;
    }
}
