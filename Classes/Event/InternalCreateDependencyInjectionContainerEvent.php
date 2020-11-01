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
 * Last modified: 2020.10.19 at 23:11
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event;


use Closure;
use LaborDigital\T3BA\Core\DependencyInjection\FailsafeDelegateContainer;
use LaborDigital\T3BA\Core\Util\FailsafeWrapper;
use Psr\Container\ContainerInterface;

class InternalCreateDependencyInjectionContainerEvent
{
    /**
     * True if a failsafe container is required
     *
     * @var bool
     */
    protected $isFailsafe;

    /**
     * The given creation arguments
     *
     * @var array
     */
    protected $args;

    /**
     * The generator closure to create a container instance with
     *
     * @var \Closure
     */
    protected $generator;

    /**
     * The normal container interface as a singleton
     *
     * @var ContainerInterface
     */
    protected $normalContainer;

    /**
     * The container instance to return
     *
     * @var ContainerInterface
     */
    protected $returnedContainer;

    /**
     * InternalCreateDependencyInjectionContainerEvent constructor.
     *
     * @param   bool      $isFailSafe
     * @param   array     $args
     * @param   \Closure  $generator
     */
    public function __construct(bool $isFailSafe, array $args, Closure $generator)
    {
        $this->isFailsafe = $isFailSafe;
        $this->args       = $args;
        $this->generator  = $generator;
    }

    /**
     * Returns true if a failsafe container is required
     *
     * @return bool
     */
    public function isFailsafe(): bool
    {
        return $this->isFailsafe;
    }

    /**
     * Sets the returned container to use a failsafe container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function makeFailsafeContainer(): ContainerInterface
    {
        $args    = $this->args;
        $args[2] = true;

        return $this->returnedContainer = call_user_func_array($this->generator, $args);
    }

    /**
     * Sets the returned container to use a normal container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function makeNormalContainer(): ContainerInterface
    {
        if (isset($this->normalContainer)) {
            return $this->returnedContainer = $this->normalContainer;
        }

        $args    = $this->args;
        $args[2] = false;

        return $this->returnedContainer = $this->normalContainer = call_user_func_array($this->generator, $args);
    }

    /**
     * Allows you to set the normal container instance
     *
     * @param   \Psr\Container\ContainerInterface  $container
     */
    public function setNormalContainer(ContainerInterface $container): void
    {
        $this->normalContainer = $container;
    }

    /**
     * Sets the returned container to use the failsafe delegate container which uses both containers
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function makeFailsafeDelegateContainer(): ContainerInterface
    {
        return $this->returnedContainer = new FailsafeDelegateContainer(
            $this->makeFailsafeContainer(),
            $this->makeNormalContainer()
        );
    }

    /**
     * Returns the container instance to be returned by the createDependencyInjectionContainer() method
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        // Auto-select the matching container implementation
        if (empty($this->returnedContainer)) {
            if ($this->isFailsafe) {
                return FailsafeWrapper::handleEither(function () {
                    return $this->makeFailsafeDelegateContainer();
                }, function ($d) {
                    dbge($d);

                    return $this->makeFailsafeContainer();
                });
            }

            return $this->returnedContainer = call_user_func_array($this->generator, $this->args);
        }

        return $this->returnedContainer;
    }
}
