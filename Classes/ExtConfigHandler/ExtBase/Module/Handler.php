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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractGroupExtConfigHandler;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\SignaturePluginNameMapTrait;
use LaborDigital\T3ba\ExtConfigHandler\TypoScript\Handler as TsHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractGroupExtConfigHandler
{
    use SignaturePluginNameMapTrait;
    
    /**
     * @inheritDoc
     */
    protected function getGroupKeyOfClass(string $class): string
    {
        return $this->getElementKeyForClass($class, [$this, 'getSignatureFromClass']);
    }
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module\ConfigGenerator
     */
    protected $generator;
    
    /**
     * @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module\ModuleConfigurator
     */
    protected $configurator;
    
    /**
     * The collected list of arguments to store in the configuration
     *
     * @var array
     */
    protected $registerModuleArgs = [];
    
    /**
     * ExtBaseModuleConfigHandler constructor.
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Module\ConfigGenerator  $generator
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
        $configurator->registerInterface(ConfigureModuleInterface::class);
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
        /** @see \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Applier::registerModules() */
        $this->context->getState()->setAsJson('typo.extBase.module.args', $this->registerModuleArgs, true);
    }
    
    /**
     * @inheritDoc
     */
    public function prepareGroup(string $groupKey, array $groupClasses): void
    {
        $this->configurator = $this->getInstanceWithoutDi(
            ModuleConfigurator::class,
            [
                $groupKey,
                $this->getPluginNameForSignature($groupKey),
                $this->context,
            ]
        );
    }
    
    /**
     * @inheritDoc
     */
    public function handleGroupItem(string $class): void
    {
        call_user_func([$class, 'configureModule'], $this->configurator, $this->context);
    }
    
    /**
     * @inheritDoc
     */
    public function finishGroup(string $groupKey, array $groupClasses): void
    {
        $this->registerModuleArgs[] = $this->generator->generate($this->configurator, $this->context);
    }
}
