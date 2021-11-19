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
 * Last modified: 2021.11.19 at 10:01
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Icon;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractExtConfigHandler
{
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\Icon\ExtConfigIconRegistry
     */
    protected $configurator;
    
    public function __construct(ExtConfigIconRegistry $iconRegistry)
    {
        $this->configurator = $iconRegistry;
    }
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureIconsInterface::class);
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
        /** @var \LaborDigital\T3ba\ExtConfigHandler\Icon\ConfigureIconsInterface $class */
        $class::configureIcons($this->configurator, $this->context);
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void { }
    
}