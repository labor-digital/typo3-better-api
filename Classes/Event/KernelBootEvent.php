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
 * Last modified: 2020.08.22 at 21:33
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event;


use LaborDigital\T3BA\Core\Kernel;

/**
 * Class KernelBootEvent
 *
 * Emitted after the Better Api Kernel prepared all registered boot stages and is ready to turn on the lights ;)
 *
 * @package LaborDigital\T3BA\Event
 */
class KernelBootEvent
{
    /**
     * The kernel instance which is being initialized
     *
     * @var \LaborDigital\T3BA\Core\Kernel
     */
    protected $kernel;

    /**
     * KernelBootEvent constructor.
     *
     * @param   \LaborDigital\T3BA\Core\Kernel  $kernel
     */
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns the kernel instance which is being initialized
     *
     * @return \LaborDigital\T3BA\Core\Kernel
     */
    public function getKernel(): Kernel
    {
        return $this->kernel;
    }
}
