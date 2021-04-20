<?php
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Core\Di;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Trait ContainerAwareTrait
 *
 * Makes any class container aware even if your class was loaded without dependency injection
 * the Container() method will return the container instance!
 *
 * @package LaborDigital\T3BA\Core\Di
 */
trait ContainerAwareTrait
{
    /**
     * Holds the list of stored service instances
     *
     * @var array
     */
    protected $caServices = [];

    /**
     * Allows you to manually inject a service instance. Every time you use "getService" with $classOrInterfaceName
     * the matching instance will be returned
     *
     * @param   string  $classOrInterfaceName  The name of the interface / class this instance should be returned for.
     * @param   object  $instance              The instance to register for the given class / interface name
     *
     * @return void
     */
    public function setService(string $classOrInterfaceName, object $instance): void
    {
        $this->caServices[$classOrInterfaceName] = $instance;
    }

    /**
     * Returns true if the object already has an instance of the given class or interface in store,
     * false if not.
     *
     * @param   string  $classOrInterfaceName  The name of the interface / class this instance that should be checked
     *
     * @return bool
     */
    public function hasService(string $classOrInterfaceName): bool
    {
        return isset($this->caServices[$classOrInterfaceName]);
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
     *
     * @return mixed
     */
    protected function getService(string $class)
    {
        return $this->caServices[$class] ?? $this->getContainer()->get($class);
    }

    /**
     * Returns the instance of the container
     *
     * @return \LaborDigital\T3BA\Core\Di\DelegateContainer
     */
    protected function getContainer(): DelegateContainer
    {
        return $this->caServices['delegate'] ??
               $this->caServices['delegate'] = DelegateContainer::getInstance();
    }

    /**
     * Allows you to create a new object instance without dependency injection.
     * This is currently only a wrapper around GeneralUtility::makeInstance()
     *
     * @param   string  $class                 The class to instantiate
     * @param   array   $constructorArguments  The constructor arguments to pass
     *
     * @return mixed
     *
     * @see \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance()
     */
    protected function makeInstance(string $class, array $constructorArguments = [])
    {
        return GeneralUtility::makeInstance($class, ...$constructorArguments);
    }

    /**
     * Returns a list of commonly used services as a "lazy" lookup method.
     *
     * @return \LaborDigital\T3BA\Core\Di\CommonServices
     * @see cs() for a short hand
     */
    protected function getCommonServices(): CommonServices
    {
        return $this->cs();
    }

    /**
     * Shorthand alias of: getCommonServices()
     * Returns a list of commonly used services as a "lazy" lookup method.
     *
     * @return \LaborDigital\T3BA\Core\Di\CommonServices
     * @see getCommonServices()
     */
    protected function cs(): CommonServices
    {
        return $this->caServices[CommonServices::class] ??
               $this->caServices[CommonServices::class]
                   = $this->getService(CommonServices::class);
    }
}