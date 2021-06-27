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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;

/**
 * Interface ConfigureTaskInterface
 *
 * All classes that implement this interface MUST be located at /Classes/Scheduler in order to be found!
 *
 * @package LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task
 */
interface ConfigureTaskInterface
{
    /**
     * Allows you to configure this scheduler task
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\Scheduler\Task\TaskConfigurator  $taskConfigurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                        $context
     */
    public static function configure(TaskConfigurator $taskConfigurator, ExtConfigContext $context): void;
}
