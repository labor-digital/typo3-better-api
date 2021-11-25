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
 * Last modified: 2021.11.23 at 10:45
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript\ExpressionLanguage;


use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigHandler;
use Neunerlei\Configuration\Handler\HandlerConfigurator;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\ExpressionLanguage\ProviderInterface;

class Handler extends AbstractExtConfigHandler
{
    protected $providers = [];
    protected $functionProviders = [];
    
    /**
     * @inheritDoc
     */
    public function configure(HandlerConfigurator $configurator): void
    {
        $configurator->registerLocation('Classes/TypoScript/ExpressionLanguage');
        $configurator->registerInterface(ExpressionFunctionProviderInterface::class);
        $configurator->registerInterface(ProviderInterface::class);
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
        if (in_array(ProviderInterface::class, class_implements($class), true)) {
            $this->providers[] = $class;
            
            return;
        }
        
        $this->functionProviders[] = $class;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(): void
    {
        $this->context->getState()->useNamespace('typo.typoScript.expressionLanguage', function ($state) {
            $state->set('providers', array_unique($this->providers))
                  ->set('functionProviders', array_unique($this->functionProviders));
        });
    }
    
}