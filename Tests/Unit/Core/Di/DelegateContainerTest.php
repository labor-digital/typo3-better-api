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
 * Last modified: 2021.11.29 at 08:31
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\Di;


use LaborDigital\T3ba\Core\Di\DelegateContainer;
use LaborDigital\T3ba\Core\Di\MiniContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use TYPO3\CMS\Core\DependencyInjection\FailsafeContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DelegateContainerTest extends UnitTestCase
{
    public function testSymfonyRetrieval(): void
    {
        $c = new DelegateContainer();
        static::assertNull($c->getSymfony());
        
        $cs1 = $this->makeSymfonyContainer();
        $c->setContainer(DelegateContainer::TYPE_SYMFONY, $cs1);
        static::assertSame($cs1, $c->getSymfony());
        
        // The GeneralUtility container should always win, even if a container was set to the delegate
        $cs2 = $this->makeSymfonyContainer();
        GeneralUtility::setContainer($cs2);
        static::assertSame($cs2, $c->getSymfony());
        
        $prop = (new \ReflectionClass(GeneralUtility::class))->getProperty('container');
        $prop->setAccessible(true);
        $prop->setValue(null);
    }
    
    public function testInternalRetrieval(): void
    {
        $c = new DelegateContainer();
        $ci = $this->makeInternalContainer();
        $c->setContainer(DelegateContainer::TYPE_INTERNAL, $ci);
        static::assertSame($ci, $c->getInternal());
    }
    
    public function testInvalidTypeGivenToSetContainer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid container type "bar" given, allowed are: "internal", "symfony", "failsafe"');
        $c = new DelegateContainer();
        $c->setContainer('bar', $this->makeSymfonyContainer());
    }
    
    public function testSettingService(): void
    {
        $c = new DelegateContainer();
        
        $ci = $this->makeInternalContainer();
        $c->setContainer(DelegateContainer::TYPE_INTERNAL, $ci);
        
        $c->set('set:service', new \stdClass());
        static::assertTrue($ci->has('set:service'));
        
        $cs = $this->makeSymfonyContainer();
        $c->setContainer(DelegateContainer::TYPE_SYMFONY, $cs);
        $c->set('set:service2', new \stdClass());
        static::assertTrue($cs->has('set:service2'));
        static::assertFalse($ci->has('set:service2'));
    }
    
    public function testSettingFailIfNoContainerExists(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('There is currently no container available to set the service on!');
        $c = new DelegateContainer();
        $c->set('foo', new \stdClass());
    }
    
    public function testHasService(): void
    {
        $c = new DelegateContainer();
        
        static::assertFalse($c->has('internal:service'));
        
        $c->setContainer(DelegateContainer::TYPE_INTERNAL, $this->makeInternalContainer());
        static::assertTrue($c->has('internal:service'));
        
        static::assertFalse($c->has('symfony:service'));
        $c->setContainer(DelegateContainer::TYPE_SYMFONY, $this->makeSymfonyContainer());
        static::assertTrue($c->has('symfony:service'));
        static::assertTrue($c->has('internal:service'));
        
        static::assertFalse($c->has('failsafe:service'));
        $c->setContainer(DelegateContainer::TYPE_FAILSAFE, $this->makeFailsafeContainer());
        static::assertTrue($c->has('failsafe:service'));
        static::assertTrue($c->has('symfony:service'));
        static::assertTrue($c->has('internal:service'));
    }
    
    public function testGetService(): void
    {
        $c = new DelegateContainer();
        
        $c->setContainer(DelegateContainer::TYPE_INTERNAL, $this->makeInternalContainer());
        static::assertEquals('internalService', $c->get('internal:service')->type);
        
        $c->setContainer(DelegateContainer::TYPE_SYMFONY, $this->makeSymfonyContainer());
        static::assertEquals('symfonyService', $c->get('symfony:service')->type);
        static::assertEquals('internalService', $c->get('internal:service')->type);
        
        $c->setContainer(DelegateContainer::TYPE_FAILSAFE, $this->makeFailsafeContainer());
        static::assertEquals('failsafeService', $c->get('failsafe:service')->type);
        static::assertEquals('symfonyService', $c->get('symfony:service')->type);
        static::assertEquals('internalService', $c->get('internal:service')->type);
    }
    
    public function testGetFailThroughSymfonyContainer(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "foo".');
        $c = new DelegateContainer();
        $c->setContainer(DelegateContainer::TYPE_SYMFONY, $this->makeSymfonyContainer());
        $c->get('foo');
    }
    
    public function testGetFailWithoutSymfonyContainer(): void
    {
        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service "foo".');
        $c = new DelegateContainer();
        $c->get('foo');
    }
    
    protected function makeSymfonyContainer(): Container
    {
        $c = new Container();
        $c->set('symfony:service', (object)['type' => 'symfonyService']);
        
        return $c;
    }
    
    protected function makeInternalContainer(): MiniContainer
    {
        return new MiniContainer(['internal:service' => (object)['type' => 'internalService']]);
    }
    
    protected function makeFailsafeContainer(): FailsafeContainer
    {
        $c = $this->createMock(FailsafeContainer::class);
        $c->method('get')->willReturn((object)['type' => 'failsafeService']);
        $c->method('has')->willReturnCallback(static function ($id) {
            return $id === 'failsafe:service';
        });
        
        return $c;
    }
}