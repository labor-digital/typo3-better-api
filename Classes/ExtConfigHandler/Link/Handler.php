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
 * Last modified: 2020.09.04 at 16:30
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Link;


use LaborDigital\T3BA\ExtConfig\AbstractSimpleExtConfigHandler;
use LaborDigital\T3BA\ExtConfigHandler\Pid\Handler as PidHandler;
use LaborDigital\T3BA\ExtConfigHandler\TypoScript\Handler as TsHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractSimpleExtConfigHandler
{
    protected $configureMethod = 'configureLinks';

    /**
     * @inheritDoc
     */
    protected function getConfiguratorClass(): string
    {
        return DefinitionCollector::class;
    }

    /**
     * @inheritDoc
     */
    protected function getStateNamespace(): string
    {
        return 't3ba.link';
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureLinksInterface::class);
        $configurator->executeThisHandlerAfter(TsHandler::class);
        $configurator->executeThisHandlerAfter(PidHandler::class);
    }

}
