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
 * Last modified: 2021.02.02 at 09:54
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Table\PostProcessor;


use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;

interface TcaPostProcessorStepInterface extends PublicServiceInterface
{
    /**
     * This method gets called once for every table that is registered in the tca
     *
     * @param   string  $tableName  The name of the table to process
     * @param   array   $config     The configuration of the table to process
     * @param   array   $meta       Allows to store meta-data that gets injected into the config state object at
     *                              tca.meta. Everything you store here must be json encodeable!
     */
    public function process(string $tableName, array &$config, array &$meta): void;
}
