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
use LaborDigital\T3BA\ExtConfigHandler\Raw\ConfigureRawSettingsInterface;
use LaborDigital\T3BA\Tool\DataHook\FieldPacker\FlexFormFieldPacker;
use Neunerlei\Configuration\State\ConfigState;

class CoreConfig implements ConfigureRawSettingsInterface
{

    /**
     * @inheritDoc
     */
    public static function configureRaw(ConfigState $state, ExtConfigContext $context): void
    {
        // Register the flex form field packer
        $state->useNamespace('t3ba', static function () use ($state) {
            $state->setMultiple([
                'dataHook' => [
                    'fieldPackers' => [
                        FlexFormFieldPacker::class,
                    ],
                ],
            ]);
        });
    }

}
