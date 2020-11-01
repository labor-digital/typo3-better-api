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
 * Last modified: 2020.10.18 at 20:42
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Raw;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use Neunerlei\Configuration\State\ConfigState;

interface ConfigureRawSettingsInterface
{

    /**
     * Allows you to apply raw configuration settings.
     * This is useful for project configuration or other settings you don't need more than once or twice in a decade.
     *
     * @param   \Neunerlei\Configuration\State\ConfigState     $state
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext  $context
     */
    public static function configureRaw(ConfigState $state, ExtConfigContext $context): void;

}
