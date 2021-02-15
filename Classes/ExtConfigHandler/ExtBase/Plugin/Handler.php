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
 * Last modified: 2020.10.18 at 16:34
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin;


use LaborDigital\T3BA\ExtConfig\AbstractGroupExtConfigHandler;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\SignaturePluginNameMapTrait;
use LaborDigital\T3BA\ExtConfigHandler\TypoScript\Handler as TsHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractGroupExtConfigHandler
{
    use SignaturePluginNameMapTrait;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigGenerator
     */
    protected $generator;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator
     */
    protected $configurator;

    /**
     * ExtBasePluginConfigHandler constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\ConfigGenerator  $generator
     */
    public function __construct(ConfigGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/Controller');
        $configurator->executeThisHandlerAfter(TsHandler::class);
        $configurator->registerInterface(ConfigurePluginInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function prepareHandler(): void { }

    /**
     * @inheritDoc
     */
    public function finishHandler(): void
    {
        $this->generator->dump($this->context->getState());
    }

    /**
     * @inheritDoc
     */
    public function prepareGroup(string $signature, array $groupClasses): void
    {
        $this->configurator = $this->getInstanceWithoutDi(PluginConfigurator::class, [
            $signature,
            $this->getPluginNameForSignature($signature),
            $this->context,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function handleGroupItem(string $class): void
    {
        call_user_func([$class, 'configurePlugin'], $this->configurator, $this->context);
    }

    /**
     * @inheritDoc
     */
    public function finishGroup(string $groupKey, array $groupClasses): void
    {
        $this->generator->generate($this->configurator, $this->context);
    }
}
