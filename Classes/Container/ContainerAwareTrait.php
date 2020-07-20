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
 * Last modified: 2020.05.12 at 11:46
 */

namespace LaborDigital\Typo3BetterApi\Container;

/**
 * Trait ContainerAwareTrait
 *
 * Makes any class container aware even if your class was loaded without dependency injection
 * the getContainer() method will return the container instance!
 *
 * @package LaborDigital\Typo3BetterApi\Container
 */
trait ContainerAwareTrait
{
    /**
     * Holds the list of resolved singleton instances
     *
     * @var array
     */
    protected $__containerAwareTraitSingletons = [];
    
    /**
     * Injects the container instance if possible
     *
     * @param   \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface  $container
     */
    public function injectContainer(TypoContainerInterface $container): void
    {
        $this->__containerAwareTraitSingletons['@container'] = $container;
    }
    
    /**
     * Allows you to manually inject a singleton instance either for manual instance creation or testing purposes
     *
     * @param   string  $classOrInterfaceName  The name of the interface / class this instance should be returned for.
     * @param   object  $instance              The instance to register for the given class / interface name
     *
     * @return $this
     */
    public function setLocalSingleton(string $classOrInterfaceName, $instance): self
    {
        $this->__containerAwareTraitSingletons[$classOrInterfaceName] = $instance;
        
        return $this;
    }
    
    /**
     * Returns the instance of the container
     *
     * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    protected function Container(): TypoContainerInterface
    {
        return $this->__containerAwareTraitSingletons['@container'] ??
               $this->__containerAwareTraitSingletons['@container'] = TypoContainer::getInstance();
    }
    
    /**
     * You can use this method if you want to lazy load an object using the container instance.
     *
     * Note: You should try to avoid this method as hard as possible!
     * This is the opposite of IoC and how you should use dependency injection.
     * However: There are some good examples of where you might want to use it:
     * Inside Models, or callbacks that don't support dependency injection for example.
     *
     * @param   string  $class  The class or interface you want to retrieve the object for
     * @param   array   $args   [deprecated, will be removed in v10] Optional, additional constructor arguments
     *
     * @return mixed
     */
    protected function getInstanceOf(string $class, $args = [])
    {
        // Create the instance as singleton
        if (isset($this->__containerAwareTraitSingletons[$class])) {
            return $this->__containerAwareTraitSingletons[$class]
                = $this->Container()->get($class, ['args' => $args]);
        }
        
        // Just create the instance
        return $this->Container()->get($class, ['args' => $args]);
    }
    
    /**
     * Similar to getInstanceOf() but stores all class instances as a local reference/singleton.
     * Meaning that you will always receive the same instance of the required class. It also means,
     * that you can save overhead because the instance is locally stored and not resolved by the container
     * every time you request it.
     *
     * This behaviour is independent of TYPO3's internal singleton handling and works for every class /
     * interface in your codebase. While the TYPO3 SingletonInterface provides you a singleton instance
     * on a container-level, this implementation only provides you a singleton instance on an instance-level.
     *
     * @param   string  $class  The class or interface you want to retrieve the object for
     *
     * @return mixed
     */
    protected function getSingletonOf(string $class)
    {
        // Check if we have a singleton
        if (isset($this->__containerAwareTraitSingletons[$class])) {
            return $this->__containerAwareTraitSingletons[$class];
        }
        
        // Request the instance from the container
        return $this->__containerAwareTraitSingletons[$class]
            = $this->Container()->get($class);
    }
    
}
