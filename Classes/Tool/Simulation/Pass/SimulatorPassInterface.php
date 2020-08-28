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
 * Last modified: 2020.07.16 at 20:29
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Simulation\Pass;


interface SimulatorPassInterface
{
    /**
     * SimulatorPassInterface constructor.
     * For performance reasons a pass should not have constructor arguments!
     */
    public function __construct();

    /**
     * Adds new option definitions to the list
     *
     * @param   array  $options
     *
     * @return array
     */
    public function addOptionDefinition(array $options): array;

    /**
     * Receives the prepared options and should check if simulation is required
     *
     * @param   array  $options  The prepared option list
     *
     * @return bool
     */
    public function requireSimulation(array $options): bool;

    /**
     * Sets up the simulation
     *
     * @param   array  $options
     */
    public function setup(array $options): void;

    /**
     * Rolls back the simulation to the original state
     */
    public function rollBack(): void;
}
