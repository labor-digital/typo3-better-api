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
 * Last modified: 2020.08.24 at 14:00
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\DependencyInjection;


use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;

class FailsafeDelegateContainer implements ContainerInterface
{
    /**
     * @var \TYPO3\CMS\Core\DependencyInjection\FailsafeContainer
     */
    protected $failsafeContainer;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * FailsafeDelegateContainer constructor.
     *
     * @param   \Psr\Container\ContainerInterface  $failsafeContainer
     * @param   \Psr\Container\ContainerInterface  $container
     */
    public function __construct(ContainerInterface $failsafeContainer, ContainerInterface $container)
    {
        $this->failsafeContainer = $failsafeContainer;
        $this->container         = $container;
    }

    /**
     * @inheritDoc
     */
    public function get($id)
    {
        if ($this->failsafeContainer->has($id)) {
            return $this->failsafeContainer->get($id);
        }

        return $this->container->get($id);
    }

    /**
     * @inheritDoc
     */
    public function has($id)
    {
        return $this->container->has($id) || $this->failsafeContainer->has($id);
    }

    /**
     * Returns the instance of the default container
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
}
