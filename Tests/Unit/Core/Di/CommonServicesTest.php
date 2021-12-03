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
 * Last modified: 2021.11.29 at 10:47
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\Di;


use LaborDigital\T3ba\Core\Di\CommonServices;
use LaborDigital\T3ba\Core\Di\MiniContainer;
use LaborDigital\T3ba\Core\Di\UnknownCommonServiceNameException;
use LaborDigital\T3ba\Tests\Util\TestLockPick;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\TypoContext\DependencyInjectionFacet;
use Neunerlei\EventBus\EventBusInterface;
use Psr\Container\ContainerInterface;
use TYPO3\CMS\Core\DependencyInjection\FailsafeContainer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Container\Container;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CommonServicesTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;
    
    public function testMappedRetrieval(): void
    {
        $ctx = new TypoContext(['di' => DependencyInjectionFacet::class]);
        $c = new MiniContainer([
            DependencyInjectionFacet::class => new DependencyInjectionFacet(),
            TypoContext::class => $ctx,
        ]);
        GeneralUtility::setContainer($c);
        $extbaseContainer = new Container($c);
        $c->set(ObjectManager::class, new ObjectManager($c, $extbaseContainer));
        $ctx->setService('delegate', $c);
        $cs = new CommonServices($c);
        
        static::assertSame($c, $cs->container);
        static::assertSame($ctx, $cs->typoContext);
        static::assertInstanceOf(DependencyInjectionFacet::class, $cs->di);
        static::assertInstanceOf(ObjectManager::class, $cs->objectManager);
    }
    
    public function testSettingServices(): void
    {
        $c = new MiniContainer();
        $cs = new CommonServices($c);
        
        $c2 = new MiniContainer();
        $cs->setInstance('container', $c2);
        static::assertNotSame($c, $cs->container);
        static::assertSame($c2, $cs->container);
    }
    
    public function testSettingUnknownServiceFail(): void
    {
        $this->expectException(UnknownCommonServiceNameException::class);
        $this->expectExceptionMessage(
            'You can\'t set a common service with name: "foo" because it is not part of the object\'s definition');
        
        $cs = new CommonServices(new MiniContainer());
        $cs->setInstance('foo', new \stdClass());
    }
    
    public function testDefinitionExtension(): void
    {
        $cs = $this->getExtendedCommonServices();
        
        static::assertInstanceOf(\stdClass::class, $cs->customFactory);
        static::assertInstanceOf(MiniContainer::class, $cs->customClass);
        static::assertInstanceOf(MiniContainer::class, $cs->customInstance);
    }
    
    public function testRetrievingUnknownServiceFail(): void
    {
        $this->expectException(UnknownCommonServiceNameException::class);
        $this->expectExceptionMessage('There is no registered common service with name: "foo"');
        
        (new CommonServices(new MiniContainer()))->foo;
    }
    
    public function testInterfaceResolutionThroughInternalContainerIfTypoUsesFailsafeContainer(): void
    {
        $c = new MiniContainer([
            EventBusInterface::class => new \stdClass(),
        ]);
        $cs = new CommonServices($c);
        GeneralUtility::setContainer(new FailsafeContainer());
        
        static::assertInstanceOf(\stdClass::class, $cs->eventBus);
    }
    
    public function testInterfaceResolutionFailsThroughFailsafeContainer(): void
    {
        if (version_compare(phpversion(), '7.4.0', '<')) {
            $this->expectException(\Error::class);
        } else {
            $this->expectError();
        }
        GeneralUtility::setContainer(new FailsafeContainer());
        (new CommonServices(new MiniContainer()))->eventBus;
    }
    
    protected function tearDown(): void
    {
        TestLockPick::setStaticProperty(GeneralUtility::class, 'container', null);
        parent::tearDown();
    }
    
    protected function getExtendedCommonServices()
    {
        return new class(new MiniContainer()) extends CommonServices {
            public function __construct(ContainerInterface $container)
            {
                parent::__construct($container);
                
                $this->def['customFactory'] = static function (ContainerInterface $container, string $name) {
                    return new \stdClass();
                };
                
                $this->def['customClass'] = MiniContainer::class;
                
                $this->def['customInstance'] = new MiniContainer();
            }
        };
        
    }
}