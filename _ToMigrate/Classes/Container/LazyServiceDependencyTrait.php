<?php
declare(strict_types=1);
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
 * Last modified: 2020.05.12 at 11:53
 */

namespace LaborDigital\Typo3BetterApi\Container;

/**
 * Trait LazyServiceInstanceTrait
 *
 * Successor to the CommonServiceLocatorTrait which is less intrusive (no plastering everything with magic getters)
 * It has a similar logic behind it. It allows you to load service instances lazily either from the container or using
 * locally registered factories. Instances, once resolved are stored locally as as sudo-singleton for the current class
 * this makes sure that every service is only resolved once.
 *
 * @package    LaborDigital\Typo3BetterApi\Container
 * @deprecated Will be removed in v10 -> ContainerAwareTrait should do the trick on it's own now
 */
trait LazyServiceDependencyTrait
{
    use ContainerAwareTrait;
    
    /**
     * Holds the list of optionally registered service factories
     *
     * @var array
     */
    protected $__serviceFactories = [];
    
    /**
     * Holds the list of resolved service instances
     *
     * @var array
     */
    protected $__serviceInstances = [];
    
    /**
     * Allows you to manually inject a service instance either for instance creation or testing purposes
     *
     * @param   string  $classOrInterfaceName  The name of the interface / class this instance should be returned for.
     * @param   object  $instance              The instance to register for the given class / interface name
     *
     * @return $this
     * @deprecated Will be removed in v10 -> ContainerAwareTrait should do the trick on it's own now
     */
    public function setServiceInstance(string $classOrInterfaceName, $instance)
    {
        $this->__serviceInstances[$classOrInterfaceName] = $instance;
        
        return $this;
    }
    
    /**
     * Allows you to register custom factories to create the instance of a service with.
     * The factory MUST return the instance of the service.
     *
     * @param   string    $classOrInterfaceName  The name of the interface / class this factory should create the
     *                                           instances for.
     * @param   callable  $factory               The factory to create the instance with. The callback receives two
     *                                           parameters. First: The container instance Second: the name of the
     *                                           class to instantiate
     *
     * @return $this
     * @deprecated Will be removed in v10 -> ContainerAwareTrait should do the trick on it's own now
     */
    public function setServiceFactory(string $classOrInterfaceName, callable $factory)
    {
        $this->__serviceFactories[$classOrInterfaceName] = $factory;
        
        return $this;
    }
    
    /**
     * Returns the instance of a service class (or any other class, really) either using the already existing instance
     * registered, an registered factory or creates a new instance (and stores it as existing instance) using the
     * TypoContainer lookup
     *
     * It handles similar to ContainerAwareTrait::getInstanceOf() but it only resolves instances once and keeps them
     * for future lookups stored.
     *
     * It is not possible to pass additional arguments to service classes when they are instantiated. Use factories for
     * those cases!
     *
     * @param   string  $classOrInterfaceName  The name of the interface / class this instance should be returned for.
     *
     * @return mixed
     * @deprecated Will be removed in v10 -> ContainerAwareTrait::getSingletonOf() should do the trick on it's own now
     */
    protected function getService(string $classOrInterfaceName)
    {
        // Return existing instances
        if (isset($this->__serviceInstances[$classOrInterfaceName])) {
            return $this->__serviceInstances[$classOrInterfaceName];
        }
        
        // Check if we have a factory -> Create the instance locally
        if (isset($this->__serviceFactories[$classOrInterfaceName])) {
            return $this->__serviceInstances[$classOrInterfaceName]
                = call_user_func($this->__serviceFactories[$classOrInterfaceName], $this->Container(),
                $classOrInterfaceName);
        }
        
        // Create the service using the container
        return $this->__serviceInstances[$classOrInterfaceName] = $this->getInstanceOf($classOrInterfaceName);
    }
}
