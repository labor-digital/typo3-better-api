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

namespace LaborDigital\T3ba\ExtConfigHandler\Pid;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;

/**
 * Used to configure the list of "pids". Pids is short for page-ids and is a global registry of "well-known"
 * page ids. The ids are stored as a "key" => "value" pair, meaning you can work with easily to remember
 * aliases and hold all ids in a centralized repository.
 *
 * You can reference pids by their "key" in all T3BA configuration options instead of defining their numeric value.
 * Simply provide them as "@pid.yourKey" and the matching page id will be resolved for you.
 * You can also access the pid mapping as TypoScript Constants at "$config.t3ba.pid.yourKey"
 *
 * @see \LaborDigital\T3ba\ExtConfigHandler\Pid\Site\ConfigureSitePidsInterface for a site-based configuration
 */
interface ConfigurePidsInterface
{
    /**
     * Is used to collect the pids this configuration provides
     *
     * @param   PidCollector      $collector
     * @param   ExtConfigContext  $context
     *
     * @return void
     */
    public static function configurePids(PidCollector $collector, ExtConfigContext $context): void;
}
