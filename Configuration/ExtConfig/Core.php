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
 * Last modified: 2020.10.18 at 20:46
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Configuration\ExtConfig;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\Fluid\ConfigureFluidInterface;
use LaborDigital\T3BA\ExtConfigHandler\Fluid\FluidConfigurator;
use LaborDigital\T3BA\ExtConfigHandler\Raw\ConfigureRawSettingsInterface;
use LaborDigital\T3BA\Tool\DataHook\FieldPacker\FlexFormFieldPacker;
use LaborDigital\T3BA\Tool\Link\LinkBrowser\LinkBuilder;
use LaborDigital\T3BA\Tool\Link\LinkBrowser\LinkHandler;
use Neunerlei\Configuration\State\ConfigState;

class Core implements ConfigureRawSettingsInterface, ConfigureFluidInterface
{

    /**
     * @inheritDoc
     */
    public static function configureRaw(ConfigState $state, ExtConfigContext $context): void
    {
        // Register the flex form field packer
        $state->mergeIntoArray('t3ba', [
            'dataHook' => [
                'fieldPackers' => [
                    FlexFormFieldPacker::class,
                ],
            ],
        ]);

        // Register globals configuration for the TYPO3 core api
        $state->mergeIntoArray('typo.globals.TYPO3_CONF_VARS', [
            'SYS' => [
                'linkHandler' => [
                    'linkSetRecord' => LinkHandler::class,
                ],
                'formEngine'  => [
                    'linkHandler' => [
                        'linkSetRecord' => LinkHandler::class,
                    ],
                ],
            ],
            'FE'  => [
                'typolinkBuilder' => [
                    'linkSetRecord' => LinkBuilder::class,
                ],
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public static function configureFluid(FluidConfigurator $configurator, ExtConfigContext $context): void
    {
        $configurator->registerViewHelpers();
    }
}
