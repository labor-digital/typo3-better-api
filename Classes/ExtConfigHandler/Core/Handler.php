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
 * Last modified: 2020.08.24 at 21:18
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Core;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractSimpleExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractSimpleExtConfigHandler
{
    protected $configureMethod = 'configureCore';
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureTypoCoreInterface::class);
    }
    
    /**
     * @inheritDoc
     */
    protected function getConfiguratorClass(): string
    {
        return TypoCoreConfigurator::class;
    }
    
    /**
     * @inheritDoc
     */
    protected function getStateNamespace(): string
    {
        return 'typo.globals';
    }
}
