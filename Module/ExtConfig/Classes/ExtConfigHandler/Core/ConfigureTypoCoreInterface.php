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
 * Last modified: 2020.08.24 at 21:19
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;

interface ConfigureTypoCoreInterface
{

    /**
     * Allows you to configure TYPO3 core options, that did not fit in other categories
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core\TypoCoreConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                            $context
     */
    public static function configure(TypoCoreConfigurator $configurator, ExtConfigContext $context): void;

}