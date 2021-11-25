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
 * Last modified: 2021.11.19 at 18:01
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Pid\Site;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3ba\ExtConfig\Interfaces\ExtendedSiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface;
use LaborDigital\T3ba\ExtConfigHandler\Pid\ConfigGenerator;
use LaborDigital\T3ba\ExtConfigHandler\Pid\PidCollector;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Neunerlei\Configuration\State\ConfigState;

class Handler extends AbstractExtConfigHandler implements SiteBasedHandlerInterface, ExtendedSiteBasedHandlerInterface
{
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\Pid\PidCollector
     */
    protected $collector;
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureSitePidsInterface::class);
        $configurator->executeThisHandlerAfter(\LaborDigital\T3ba\ExtConfigHandler\Pid\Handler::class);
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
        /** @var \LaborDigital\T3ba\ExtConfigHandler\Pid\Site\ConfigureSitePidsInterface $class */
        $class::configureSitePids($this->collector, $this->context);
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()->set('pids', $this->collector->getAll());
        unset($this->collector);
    }
    
    /**
     * @inheritDoc
     */
    public function prepareSiteBasedConfig(ConfigState $state): void
    {
        $this->collector = $this->getInstanceWithoutDi(PidCollector::class);
        $pids = $this->context->getState()->get('t3ba.pids', []);
        $this->collector->setMultiple($pids);
    }
    
    /**
     * @inheritDoc
     */
    public function finishSiteBasedConfig(ConfigState $state): void
    {
        $this->getInstance(ConfigGenerator::class)->finalizePidConfig($state);
    }
    
    
}