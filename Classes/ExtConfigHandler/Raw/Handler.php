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
 * Last modified: 2020.10.18 at 20:44
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Raw;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractExtConfigHandler
{
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureRawSettingsInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void { }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        call_user_func([$class, 'configureRaw'], $this->context->getState(), $this->context);
    }

    /**
     * @inheritDoc
     */
    public function finish(): void { }

}
