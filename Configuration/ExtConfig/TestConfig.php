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
 * Last modified: 2020.08.24 at 22:00
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\Core\DependencyInjection\StaticContainerAwareTrait;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\TypoScript\ConfigureTypoScriptInterface;
use LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator;
use LaborDigital\T3BA\Tool\TypoScript\TypoScriptService;

class TestConfig implements ConfigureTypoScriptInterface
{
    use StaticContainerAwareTrait;

    /**
     * @inheritDoc
     */
    public static function configure(TypoScriptConfigurator $configurator, ExtConfigContext $context): void
    {
        $ts = static::getSingletonOf(TypoScriptService::class);
        dbge($ts->get());

        $configurator->registerDynamicContent('myTest', 'config.test = 123');

        $configurator->registerImport('dynamic:myTest');

        $configurator->registerSelectablePageTsConfigFile('EXT:{{extkey}}/Configuration/PageTs/TestPageTs.typoscript');
        $configurator->registerConstants('myExt.constant = 3');
        $configurator->registerSetup('config.setup = {$myExt.constant}');
    }

}
