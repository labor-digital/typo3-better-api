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
 * Last modified: 2020.08.23 at 16:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Di;

use LaborDigital\T3BA\Core\Di\DelegateContainer;
use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigHandler;
use LaborDigital\T3BA\ExtConfig\Interfaces\DiRunTimeHandlerInterface;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class RunTimeHandler extends AbstractExtConfigHandler implements DiRunTimeHandlerInterface
{
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $this->registerDefaultLocation($configurator);
        $configurator->registerInterface(ConfigureDiInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepare(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function handle(string $class): void
    {
        $container = $this->context->getContainer();
        if ($container instanceof DelegateContainer && $container->getSymfony()) {
            $container = $container->getSymfony();
        }

        call_user_func([$class, 'configureRuntime'], $container, $this->context);
    }

    /**
     * @inheritDoc
     */
    public function finish(): void
    {
    }

}
