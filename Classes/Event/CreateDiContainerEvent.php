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
 * Last modified: 2021.02.22 at 20:58
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event;


use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * Class CreateDiContainerEvent
 *
 * Gets dispatched when the container builder instantiates a container instance
 *
 * @package LaborDigital\T3BA\Event
 */
class CreateDiContainerEvent
{

    /**
     * True if a failsafe container gets created
     *
     * @var bool
     */
    protected $failsafe;

    /**
     * The instance of the package manager
     *
     * @var \TYPO3\CMS\Core\Package\PackageManager
     */
    protected $packageManager;

    /**
     * The container that gets created
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * CreateDiContainerEvent constructor.
     *
     * @param   bool                                    $failsafe
     * @param   \TYPO3\CMS\Core\Package\PackageManager  $packageManager
     * @param   \Psr\Container\ContainerInterface       $container
     */
    public function __construct(bool $failsafe, PackageManager $packageManager, ContainerInterface $container)
    {
        $this->failsafe       = $failsafe;
        $this->packageManager = $packageManager;
        $this->container      = $container;
    }

    /**
     * Returns true if a failsafe container gets created
     *
     * @return bool
     */
    public function isFailsafe(): bool
    {
        return $this->failsafe;
    }

    /**
     * Returns the instance of the package manager
     *
     * @return \TYPO3\CMS\Core\Package\PackageManager
     */
    public function getPackageManager(): PackageManager
    {
        return $this->packageManager;
    }

    /**
     * Returns the container that gets created
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Allows you to replace the container that gets created
     *
     * @param   \Psr\Container\ContainerInterface  $container
     *
     * @return CreateDiContainerEvent
     */
    public function setContainer(ContainerInterface $container): CreateDiContainerEvent
    {
        $this->container = $container;

        return $this;
    }

}
