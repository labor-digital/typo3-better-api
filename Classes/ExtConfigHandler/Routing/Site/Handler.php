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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Routing\Site;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractSimpleExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractSimpleExtConfigHandler implements SiteBasedHandlerInterface, NoDiInterface
{
    protected $configureMethod = 'configureSiteRouting';
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureSiteRoutingInterface::class);
    }
    
    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
        $this->configurator = $this->getInstanceWithoutDi(SiteRoutingConfigurator::class, [$this->context->getSite()]);
    }
    
    /**
     * @inheritDoc
     */
    protected function getConfiguratorClass(): string
    {
        return '';
    }
    
    /**
     * @inheritDoc
     */
    protected function getStateNamespace(): string
    {
        return '';
    }
}
