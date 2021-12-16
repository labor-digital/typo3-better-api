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
 * Last modified: 2021.12.16 at 12:22
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Core\Di;


use LaborDigital\T3ba\Core\Kernel;
use LaborDigital\T3ba\Tool\Translation\Translator;
use LaborDigital\T3ba\Tool\Tsfe\TsfeService;
use Neunerlei\Configuration\State\ConfigState;
use TYPO3\CMS\Core\Package\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider
{
    /**
     * @inheritDoc
     */
    protected static function getPackagePath(): string
    {
        return __DIR__ . '/../';
    }
    
    /**
     * @inheritDoc
     */
    public function getFactories(): array
    {
        return [
            Translator::class => [static::class, 'getTranslator'],
        ];
    }
    
    public static function getTranslator(): Translator
    {
        $kernel = Kernel::getInstance();
        
        $c = $kernel->getContainer();
        if ($c->getSymfony()) {
            return new Translator(
                $kernel->getEventBus(),
                $c->get(TsfeService::class),
                $c->get(ConfigState::class)
            );
        }
        
        return new Translator(
            $kernel->getEventBus(),
            static::makeDummyTsfeService(),
            new ConfigState([])
        );
    }
    
    protected static function makeDummyTsfeService(): TsfeService
    {
        return new class extends TsfeService {
            /**
             * @inheritDoc
             */
            public function hasTsfe(): bool
            {
                return false;
            }
        };
    }
    
}