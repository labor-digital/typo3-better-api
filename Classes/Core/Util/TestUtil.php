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
 * Last modified: 2020.08.23 at 19:22
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\Util;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use Neunerlei\EventBus\EventBusInterface;
use Psr\Container\ContainerInterface;

class TestUtil implements PublicServiceInterface
{
    use ContainerAwareTrait;

    protected $foo;
    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;

    public function __construct(ContainerInterface $container, EventBusInterface $eventBus)
    {
        $this->foo      = $container;
        $this->eventBus = $eventBus;
    }


    public function foo()
    {
        dbge($this->Container(), $this->eventBus->getConcreteListenerProvider());
    }
}
