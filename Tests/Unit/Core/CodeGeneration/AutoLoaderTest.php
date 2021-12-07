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
 * Last modified: 2021.12.07 at 14:51
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\CodeGeneration;


use LaborDigital\T3ba\Core\CodeGeneration\AutoLoader;
use LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\LegacyContext;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideList;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideStackResolver;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedInterface;
use LaborDigital\T3ba\Tests\Util\TestLockPick;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AutoLoaderTest extends UnitTestCase
{
    public function testRegistration(): void
    {
        $loader = new AutoLoader(
            $this->createMock(OverrideList::class),
            $this->createMock(OverrideStackResolver::class),
            $this->createMock(LegacyContext::class)
        );
        
        $checker = function () use ($loader) {
            foreach (spl_autoload_functions() as $l) {
                if (is_array($l) && ($l[0] ?? null) === $loader && ($l[1] ?? null) === 'loadClass') {
                    return true;
                }
            }
            
            return false;
        };
        
        $lockPick = new TestLockPick($loader);
        
        static::assertFalse($lockPick->isRegistered);
        static::assertFalse($checker());
        
        $loader->register();
        
        static::assertTrue($lockPick->isRegistered);
        static::assertTrue($checker());
        
        $loader->register();
        
        static::assertTrue($lockPick->isRegistered);
        static::assertTrue($checker());
        
        $loader->unregister();
        
        static::assertFalse($lockPick->isRegistered);
        static::assertFalse($checker());
        
        $loader->unregister();
        
        static::assertFalse($lockPick->isRegistered);
        static::assertFalse($checker());
    }
    
    public function testOverrideListRetrieval(): void
    {
        $list = $this->createMock(OverrideList::class);
        $loader = new AutoLoader(
            $list,
            $this->createMock(OverrideStackResolver::class),
            $this->createMock(LegacyContext::class)
        );
        
        static::assertSame($list, $loader->getOverrideList());
    }
    
    public function testSetTestModeInheritance(): void
    {
        $listState = false;
        $resolverState = false;
        
        $list = $this->createMock(OverrideList::class);
        $list->method('setTestMode')->willReturnCallback(function ($state) use (&$listState) {
            $listState = $state;
        });
        $resolver = $this->createMock(OverrideStackResolver::class);
        $resolver->method('setTestMode')->willReturnCallback(function ($state) use (&$resolverState) {
            $resolverState = $state;
        });
        
        $loader = new AutoLoader(
            $list,
            $resolver,
            $this->createMock(LegacyContext::class)
        );
        
        $loader->setTestMode(true);
        
        static::assertTrue($listState);
        static::assertTrue($resolverState);
        
        $loader->setTestMode(false);
        
        static::assertFalse($listState);
        static::assertFalse($resolverState);
    }
    
    public function testLoadClass(): void
    {
        $list = $this->createMock(OverrideList::class);
        $list->method('getClassStack')->willReturn([]);
        
        $resolver = $this->createMock(OverrideStackResolver::class);
        $resolver->method('resolve')->willReturn([]);
        
        $loader = new AutoLoader(
            $list,
            $resolver,
            $this->createMock(LegacyContext::class)
        );
        
        static::assertTrue($loader->loadClass(FixtureNotLoadedClass::class));
        static::assertTrue($loader->loadClass(FixtureNotLoadedInterface::class));
        
        // Already loaded elements
        static::assertFalse($loader->loadClass(AutoLoader::class));
        static::assertFalse($loader->loadClass(SingletonInterface::class));
        
        // Simulate not registered override handling
        $list = $this->createMock(OverrideList::class);
        $list->method('getClassStack')->willReturn(null);
        
        $loader = new AutoLoader(
            $list,
            $resolver,
            $this->createMock(LegacyContext::class)
        );
        
        static::assertFalse($loader->loadClass(FixtureNotLoadedClass::class));
        static::assertFalse($loader->loadClass(FixtureNotLoadedInterface::class));
    }
    
    public function testLegacyHandler(): void
    {
        $c = 0;
        
        $generator = $this->createMock(CodeGenerator::class);
        $generator->method('getClassAliasContent')->willReturnCallback(static function () use (&$c) {
            $c += 100;
            static::assertEquals(['a', 'b', 'c', 'd'], func_get_args());
            
            return 'content1';
        });
        $generator->method('getClassCloneContentOf')->willReturnCallback(static function () use (&$c) {
            $c += 1000;
            static::assertEquals(['a', 'b'], func_get_args());
            
            return 'content2';
        });
        
        $resolver = $this->createMock(OverrideStackResolver::class);
        $resolver->method('getCodeGenerator')->willReturn($generator);
        $resolver->method('resolve')->willReturnCallback(static function () use (&$c) {
            $c += 10;
            static::assertEquals([['a' => 'b']], func_get_args());
            
            return [];
        });
        
        $loader = new AutoLoader(
            $this->createMock(OverrideList::class),
            $resolver,
            $this->createMock(LegacyContext::class)
        );
        
        $loader->legacyHandler('resolveOverrideStack', [['a' => 'b']]);
        static::assertEquals('content1', $loader->legacyHandler('getClassAliasContent', ['a', 'b', 'c', 'd']));
        static::assertEquals('content2', $loader->legacyHandler('getClassCloneContentOf', ['a', 'b']));
    }
}