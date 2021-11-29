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
 * Last modified: 2021.11.29 at 09:57
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\Di;


use LaborDigital\T3ba\Core\Di\CommonServices;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\Di\MiniContainer;
use LaborDigital\T3ba\Tests\Util\TestLockPick;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ContainerAwareTraitTest extends UnitTestCase
{
    public function testInstanceSettingAndRetrieval(): void
    {
        $c = $this->makeInstance();
        
        static::assertFalse($c->hasService('foo'));
        static::assertFalse($c->hasSetService('foo'));
        static::assertTrue($c->hasService('service'));
        static::assertFalse($c->hasSetService('service'));
        
        $c->setService('foo', new \stdClass());
        static::assertTrue($c->hasService('foo'));
        static::assertTrue($c->hasSetService('foo'));
        
        static::assertInstanceOf(\stdClass::class, $c->getService('foo'));
        static::assertInstanceOf(\stdClass::class, $c->getService('service'));
    }
    
    public function testMakeInstance(): void
    {
        $c = $this->makeInstance();
        
        static::assertInstanceOf(MiniContainer::class, $c->makeInstance(MiniContainer::class));
        
        $i = $c->makeInstance(MiniContainer::class, [['bar' => new \stdClass()]]);
        static::assertInstanceOf(MiniContainer::class, $i);
        static::assertTrue($i->has('bar'));
        
        $c->setService(MiniContainer::class, $i);
        static::assertSame($i, $c->getService(MiniContainer::class));
        static::assertSame($i, $c->makeInstance(MiniContainer::class));
        static::assertNotSame($i, $c->makeInstance(MiniContainer::class, [['baz' => new \stdClass()]]));
    }
    
    public function testGetServiceOrInstance(): void
    {
        $c = $this->makeInstance();
        
        $i = $c->getContainer()->get('service');
        static::assertSame($i, $c->getServiceOrInstance('service'));
        static::assertInstanceOf(MiniContainer::class, $c->getServiceOrInstance(MiniContainer::class));
    }
    
    public function testCommonServiceRetrieval(): void
    {
        $c = $this->makeInstance();
        
        $cs1 = $c->getCommonServices();
        $cs2 = $c->cs();
        static::assertSame($cs1, $cs2);
        
        $cs3 = new CommonServices($c->getContainer());
        $c->setService(CommonServices::class, $cs3);
        static::assertSame($cs3, $c->getCommonServices());
        static::assertSame($cs3, $c->cs());
    }
    
    protected function setUp(): void
    {
        $this->initializeDelegateContainer();
        parent::setUp();
    }
    
    protected function tearDown(): void
    {
        TestLockPick::setStaticProperty(DelegateContainer::class, 'instance', null);
        parent::tearDown();
    }
    
    protected function makeInstance()
    {
        return new class {
            use ContainerAwareTrait {
                hasService as public;
                hasSetService as public;
                getContainer as public;
                getService as public;
                makeInstance as public;
                getServiceOrInstance as public;
                getCommonServices as public;
                cs as public;
            }
        };
    }
    
    protected function initializeDelegateContainer(): void
    {
        $c = new DelegateContainer();
        $c->setContainer(DelegateContainer::TYPE_INTERNAL, new MiniContainer(
            [
                'service' => new \stdClass(),
            ]
        ));
        DelegateContainer::setInstance($c);
    }
}