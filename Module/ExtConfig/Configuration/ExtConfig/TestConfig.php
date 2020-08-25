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
 * Last modified: 2020.08.24 at 21:54
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\Configuration\ExtConfig;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core\ConfigureTypoCoreInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigHandler\Core\TypoCoreConfigurator;
use TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;

class TestConfig implements ConfigureTypoCoreInterface
{
    /**
     * @inheritDoc
     */
    public static function configure(TypoCoreConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->registerXClass(A::class, B::class);

        $configurator->registerCache('test', VariableFrontend::class, Typo3DatabaseBackend::class, [
            'options' => ['{{extKey}}'],
        ]);
    }

}