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
 * Last modified: 2021.01.13 at 18:12
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Fluid;


use LaborDigital\T3BA\ExtConfig\AbstractSimpleExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class ConfigureFluidHandler extends AbstractSimpleExtConfigHandler
{
    protected $configureMethod = 'configureFluid';

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureFluidInterface::class);
    }

    /**
     * @inheritDoc
     */
    protected function getConfiguratorClass(): string
    {
        return FluidConfigurator::class;
    }

    /**
     * @inheritDoc
     */
    protected function getStateNamespace(): string
    {
        return 'typo.fluid';
    }

}
