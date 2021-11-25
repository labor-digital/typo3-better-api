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
 * Last modified: 2021.11.23 at 10:41
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript\PreParseFunction;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;

class Handler extends AbstractExtConfigHandler
{
    protected $functions = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/TypoScript/PreParseFunction');
        $configurator->registerInterface(ConfigurePreParseFunctionInterface::class);
        $configurator->executeThisHandlerAfter(\LaborDigital\T3ba\ExtConfigHandler\TypoScript\Handler::class);
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
        /** @var ConfigurePreParseFunctionInterface $class */
        $this->functions[$class::getIdentifier()] = $class . '->process';
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()
                      ->mergeIntoArray(
                          'typo.globals.TYPO3_CONF_VARS.SC_OPTIONS.t3lib/class\\.t3lib_tsparser\\.php.preParseFunc',
                          $this->functions
                      );
    }
    
}