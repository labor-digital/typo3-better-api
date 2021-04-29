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
 * Last modified: 2021.04.29 at 22:13
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\Translation\ConfigureTranslationInterface;
use LaborDigital\T3BA\ExtConfigHandler\Translation\TranslationConfigurator;

class Translation implements ConfigureTranslationInterface
{
    /**
     * @inheritDoc
     */
    public static function configureTranslation(TranslationConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->registerNamespace('t3ba');
    }

}
