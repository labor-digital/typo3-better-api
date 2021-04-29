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


use LaborDigital\T3BA\Core\Di\PublicServiceInterface;

interface SimulatorPassInterface extends PublicServiceInterface
{
    /**
     * Adds new option definitions to the list
     *
     * @param   array  $options  The option list to add the pass' definition to
     *
     * @return array
     */
    public function addOptionDefinition(array $options): array;
    
    /**
     * Receives the prepared options and should check if simulation is required
     *
     * @param   array  $options  The prepared option list
     * @param   array  $storage  A storage array to store potential backups for the rollBack method on
     *
     * @return bool
     */
    public function requireSimulation(array $options, array &$storage): bool;
    
    /**
     * Sets up the simulation
     *
     * @param   array  $options  The prepared option list
     * @param   array  $storage  A storage array to store potential backups for the rollBack method on
     */
    public function setup(array $options, array &$storage): void;
    
    /**
     * Rolls back the simulation to the original state
     *
     * @param   array  $storage  The list of stored values that should be restored
     */
    public function rollBack(array $storage): void;
}
