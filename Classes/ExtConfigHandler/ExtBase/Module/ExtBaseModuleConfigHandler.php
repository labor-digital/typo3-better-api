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
 * Last modified: 2020.09.09 at 01:07
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Module;


use LaborDigital\T3BA\ExtConfig\AbstractGroupExtConfigHandler;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\SignaturePluginNameMapTrait;
use LaborDigital\T3BA\ExtConfigHandler\TypoScript\ConfigureTypoScriptHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtBaseModuleConfigHandler extends AbstractGroupExtConfigHandler
{
    use SignaturePluginNameMapTrait;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Module\ModuleConfigGenerator
     */
    protected $generator;

    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Module\ModuleConfigurator
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
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Module\ModuleConfigGenerator  $generator
     */
    public function __construct(ModuleConfigGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/Controller');
        $configurator->executeThisHandlerAfter(ConfigureTypoScriptHandler::class);
        $configurator->registerInterface(ConfigureExtBaseModuleInterface::class);
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
        $this->context->getState()->set('typo.extBase.module.args',
            json_encode($this->registerModuleArgs, JSON_THROW_ON_ERROR));
    }

    /**
     * @inheritDoc
     */
    public function prepareGroup(string $signature, array $groupClasses): void
    {
        $this->configurator = GeneralUtility::makeInstance(
            ModuleConfigurator::class,
            $signature,
            $this->getPluginNameForSignature($signature),
            $this->context
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
