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
use LaborDigital\T3ba\Core\CodeGeneration\ClassOverridesException;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideList;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureExtendedOverrideClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureOverrideClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureSuperExtendedOverrideClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OverrideListTest extends UnitTestCase
{
    public function testRegisterSimpleOverride(): void
    {
        $l = new OverrideList();
        
        static::assertTrue($l->canOverrideClass(FixtureNotLoadedClass::class));
        static::assertFalse($l->hasClassOverride(FixtureNotLoadedClass::class));
        
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
        
        static::assertFalse($l->canOverrideClass(FixtureNotLoadedClass::class));
        static::assertTrue($l->hasClassOverride(FixtureNotLoadedClass::class));
        
        static::assertEquals([FixtureNotLoadedClass::class => FixtureOverrideClass::class], $l->getClassStack(FixtureNotLoadedClass::class));
    }
    
    public function testRegisterOverrideOfOverride(): void
    {
        $l = new OverrideList();
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
        $l->registerOverride(FixtureOverrideClass::class, FixtureExtendedOverrideClass::class);
        $l->registerOverride(FixtureExtendedOverrideClass::class, FixtureSuperExtendedOverrideClass::class);
        static::assertEquals(
            [
                FixtureNotLoadedClass::class => FixtureOverrideClass::class,
                FixtureOverrideClass::class => FixtureExtendedOverrideClass::class,
                FixtureExtendedOverrideClass::class => FixtureSuperExtendedOverrideClass::class,
            ],
            $l->getClassStack(FixtureNotLoadedClass::class)
        );
    }
    
    public function testRegisterOverrideInTestModeWithAutoLoader(): void
    {
        $c = 0;
        $autoLoader = $this->createMock(AutoLoader::class);
        $autoLoader->method('loadClass')
                   ->willReturnCallback(function () use (&$c) {
                       $c++;
                   });
        
        $l = new OverrideList();
        $l->setAutoLoader($autoLoader);
        $l->setTestMode(true);
        
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureExtendedOverrideClass::class);
        
        static::assertEquals(1, $c, 'the "loadClass" method of the AutoLoader class was not executed');
    }
    
    public function testRegisterOverrideWithoutTestModeWithAutoLoader(): void
    {
        $c = 0;
        $autoLoader = $this->createMock(AutoLoader::class);
        $autoLoader->method('loadClass')
                   ->willReturnCallback(function () use (&$c) {
                       $c++;
                   });
        
        $l = new OverrideList();
        $l->setAutoLoader($autoLoader);
        
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureExtendedOverrideClass::class);
        
        static::assertEquals(0, $c, 'the "loadClass" method of the AutoLoader class was executed, but it shouldn\'t have been');
    }
    
    public function testRegisterOverrideFailIfClassIsAlreadyLoaded(): void
    {
        $this->expectException(ClassOverridesException::class);
        $this->expectExceptionMessage(
            'The class: ' . OverrideListTest::class . ' can not be overridden, because it is already loaded!');
        
        (new OverrideList())->registerOverride(OverrideListTest::class, FixtureNotLoadedClass::class);
    }
    
    public function testRegisterOverrideFailIfAnOverrideAlreadyExists(): void
    {
        $this->expectException(ClassOverridesException::class);
        $this->expectExceptionMessage(
            'The class: ' . FixtureNotLoadedClass::class . ' is already overridden with: ' . FixtureOverrideClass::class .
            ' and therefore, can not be overridden again!');
        
        $l = new OverrideList();
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
    }
    
    public function testRegisterOverrideWithOverruleOptionSet(): void
    {
        $l = new OverrideList();
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureExtendedOverrideClass::class, true);
        static::assertEquals(
            [FixtureNotLoadedClass::class => FixtureExtendedOverrideClass::class],
            $l->getClassStack(FixtureNotLoadedClass::class)
        );
    }
    
    public function testCanOverrideClass(): void
    {
        $l = new OverrideList();
        
        static::assertTrue($l->canOverrideClass(FixtureNotLoadedClass::class));
        static::assertFalse($l->canOverrideClass(OverrideListTest::class));
        
        $l->registerOverride(FixtureNotLoadedClass::class, FixtureOverrideClass::class);
        
        static::assertFalse($l->canOverrideClass(FixtureNotLoadedClass::class));
        static::assertTrue($l->canOverrideClass(FixtureNotLoadedClass::class, true));
    }
}