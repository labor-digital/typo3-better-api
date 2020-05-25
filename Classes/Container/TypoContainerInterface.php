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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

interface TypoContainerInterface extends ContainerInterface
{
    
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * Returns the instance of a given class. Can use both methods supplied by typo3 -> Using GeneralUtility or the
     * extbase objectManager. Default is always the object manager, but you can change it by using $options["gu" =>
     * true]
     *
     * @param string $id      Identifier of the entry to look for.
     * @param array  $options Additional options
     *                        - args (DEFAULT []) A list of constructor arguments
     *                        - gu (DEFAULT FALSE) Set this to true to use the GeneralUtility::makeInstance() instead
     *                        of the object manager to create the instance
     *
     * @return mixed Entry.
     *
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     */
    public function get($id, array $options = []);
    
    /**
     * Can be used to set a given instance for a class / interface name
     *
     * This is a backdoor into the Typo3 singleton instance storage.
     * Officially you should not use it, but it is really handy when you have to work with broken extensions.
     * So: use it with care and only if there is no other option!
     *
     * @param string                             $class
     * @param \TYPO3\CMS\Core\SingletonInterface $instance
     *
     * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    public function set(string $class, SingletonInterface $instance): TypoContainerInterface;
    
    /**
     * Registers a given interface for a given classname. So If the interface is required, the class can be resolved.
     *
     * @param string $interface The interface which should be linked to the given class
     * @param string $class     The class to be resolved if the given interface is required
     *
     * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    public function setClassFor(string $interface, string $class): TypoContainerInterface;
    
    /**
     * Helper to register an xClass for another class
     *
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/ApiOverview/Xclasses/Index.html
     *
     * @param string $classToOverride
     * @param string $classToOverrideWith
     *
     * @return \LaborDigital\Typo3BetterApi\Container\TypoContainerInterface
     */
    public function setXClassFor(string $classToOverride, string $classToOverrideWith): TypoContainerInterface;
    
    /**
     * Returns the instance of the object manager
     *
     * @return \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    public function getObjectManager(): ObjectManagerInterface;
}
