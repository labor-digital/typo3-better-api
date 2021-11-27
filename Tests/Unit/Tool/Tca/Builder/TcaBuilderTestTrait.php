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
 * Last modified: 2021.11.26 at 16:41
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Tool\Tca\Builder;


use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\Di\MiniContainer;
use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\Tool\Sql\SqlRegistry;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderServices;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase\SpecialCaseHandler;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\TypoContext\DependencyInjectionFacet;
use Neunerlei\Configuration\Loader\LoaderContext;
use Neunerlei\Configuration\State\ConfigState;

trait TcaBuilderTestTrait
{
    protected function getTableInstance(string $tableName): TcaTable
    {
        $container = new MiniContainer();
        
        $context = new ExtConfigContext(
            $this->createMock(ExtConfigService::class)
        );
        
        $cs = new TcaBuilderServices($container, $context);
        $di = $this->createMock(DependencyInjectionFacet::class);
        $di->method('makeInstance')->willReturnCallback(static function (string $class, array $args) {
            return new $class(...$args);
        });
        $cs->setInstance('di', $di);
        
        $container->set(TcaBuilderServices::class, $cs);
        
        $lContext = new LoaderContext();
        $lContext->container = $container;
        $context->initialize($lContext, new ConfigState([]));
        
        $factory = new TableFactory(
            new TypeFactory(),
            $this->createMock(SqlRegistry::class),
            $this->createMock(SpecialCaseHandler::class)
        );
        
        $_container = new DelegateContainer();
        $_container->setContainer(DelegateContainer::TYPE_INTERNAL, $container);
        
        $factory->setService('delegate', $_container);
        
        return $factory->create($tableName, $context);
    }
}