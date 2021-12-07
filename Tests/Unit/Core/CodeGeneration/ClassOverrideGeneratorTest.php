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


use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\CodeGeneration\AutoLoader;
use LaborDigital\T3ba\Core\CodeGeneration\ClassOverrideGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\LegacyContext;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideList;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideStackResolver;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\VarFs\Mount;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureExtendedOverrideClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedClass;
use LaborDigital\T3ba\Tests\Util\TestLockPick;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ClassOverrideGeneratorTest extends UnitTestCase
{
    protected $loaderBackup;
    
    protected function setUp(): void
    {
        $this->loaderBackup = ClassOverrideGenerator::getAutoLoader();
        parent::setUp();
    }
    
    protected function tearDown(): void
    {
        if ($this->loaderBackup instanceof AutoLoader) {
            ClassOverrideGenerator::init($this->loaderBackup, true);
        }
        
        parent::tearDown();
    }
    
    public function testLegacyInitialization(): void
    {
        // We can test this here, because the class will be used even in tests, therefore
        // we can safely assume that the autoLoader is already present in the system
        $loader = ClassOverrideGenerator::getAutoLoader();
        static::assertInstanceOf(AutoLoader::class, $loader);
        
        // We reset the init state in order to allow a legacy creation
        TestLockPick::setStaticProperty(ClassOverrideGenerator::class, 'initDone', false);
        
        ClassOverrideGenerator::init(
            require __DIR__ . '/../../../../vendor/autoload.php',
            (new VarFs())->getMount('test-' . microtime(true)),
            false
        );
        
        static::assertFalse(TestLockPick::getStaticProperty(ClassOverrideGenerator::class, 'isTestMode'));
        static::assertTrue(TestLockPick::getStaticProperty(ClassOverrideGenerator::class, 'initDone'));
        
        $newLoader = ClassOverrideGenerator::getAutoLoader();
        static::assertInstanceOf(AutoLoader::class, $loader);
        static::assertNotSame($loader, $newLoader);
        
        $stackResolver = (new TestLockPick($newLoader))->stackResolver;
        static::assertInstanceOf(OverrideStackResolver::class, $stackResolver);
        static::assertInstanceOf(CodeGenerator::class, $stackResolver->getCodeGenerator());
        
        // The old loader must get unregistered even if a legacy init was used
        static::assertFalse((new TestLockPick($loader))->isRegistered);
        static::assertTrue((new TestLockPick($newLoader))->isRegistered);
        
        // Check if additional initializations now will be ignored -> initDone = true
        ClassOverrideGenerator::init(
            require __DIR__ . '/../../../../vendor/autoload.php',
            (new VarFs())->getMount('test-' . microtime(true)),
            true
        );
        
        static::assertSame($newLoader, ClassOverrideGenerator::getAutoLoader());
        static::assertFalse(TestLockPick::getStaticProperty(ClassOverrideGenerator::class, 'isTestMode'));
    }
    
    public function testLegacyProtectedMethodPassThrough(): void
    {
        $c = 0;
        
        $loader = $this->createMock(AutoLoader::class);
        $loader->legacyContext = new LegacyContext(
            $this->createMock(Mount::class),
            $this->createMock(ClassLoader::class)
        );
        $loader->method('legacyHandler')->willReturnCallback(function (string $type, array $args) use (&$c) {
            $c++;
            switch ($type) {
                case 'resolveOverrideStack':
                    static::assertEquals([['a' => 'b']], $args);
                    
                    return null;
                case 'getClassAliasContent':
                    static::assertEquals(['a', 'b', 'c', 'd'], $args);
                    
                    return 'content1';
                case 'getClassCloneContentOf':
                    static::assertEquals(['a', 'b'], $args);
                    
                    return 'content2';
            }
            static::fail('An unknown callback was required');
        });
        
        ClassOverrideGenerator::init($loader, true);
        
        TestLockPick::invokeStaticMethod(ClassOverrideGenerator::class, 'resolveOverrideStack', [['a' => 'b']]);
        static::assertEquals('content1', TestLockPick::invokeStaticMethod(ClassOverrideGenerator::class, 'getClassAliasContent', ['a', 'b', 'c', 'd']));
        static::assertEquals('content2', TestLockPick::invokeStaticMethod(ClassOverrideGenerator::class, 'getClassCloneContentOf', ['a', 'b']));
        
        static::assertEquals(3, $c);
    }
    
    public function testInitialization(): void
    {
        $loader = ClassOverrideGenerator::getAutoLoader();
        $mount = (new VarFs())->getMount('test-' . microtime(true));
        
        $list = new OverrideList();
        $newLoader = new AutoLoader(
            $list,
            new OverrideStackResolver(
                new TypoEventBus(),
                $mount,
                static function () {
                    return new CodeGenerator(require __DIR__ . '/../../../../vendor/autoload.php');
                }
            ),
            new LegacyContext(
                $mount,
                require __DIR__ . '/../../../../vendor/autoload.php'
            )
        );
        
        ClassOverrideGenerator::init($newLoader, false);
        
        // The old loader is unregistered when the new one gets registered
        static::assertFalse((new TestLockPick($loader))->isRegistered);
        static::assertTrue((new TestLockPick($newLoader))->isRegistered);
        
        // Test mode should now be false
        static::assertFalse((new TestLockPick($list))->isTestMode);
        
        // Test mode should now be true
        ClassOverrideGenerator::init($newLoader, true);
        static::assertTrue((new TestLockPick($newLoader))->isRegistered);
        static::assertTrue((new TestLockPick($list))->isTestMode);
    }
    
    public function testLoadClassPassThrough(): void
    {
        $c = 0;
        $loader = $this->createMock(AutoLoader::class);
        $loader->method('loadClass')->willReturnCallback(function ($class) use (&$c) {
            $c++;
            
            static::assertEquals(FixtureNotLoadedClass::class, $class);
            
            return true;
        });
        $loader->legacyContext = new LegacyContext(
            $this->createMock(Mount::class),
            $this->createMock(ClassLoader::class)
        );
        
        ClassOverrideGenerator::init($loader, true);
        
        static::assertTrue(ClassOverrideGenerator::loadClass(FixtureNotLoadedClass::class));
        static::assertEquals(1, $c);
    }
    
    public function testOverrideListMethodsPassThrough(): void
    {
        $mount = (new VarFs())->getMount('test-' . microtime(true));
        
        $list = new OverrideList();
        $loader = new AutoLoader(
            $list,
            $this->createMock(OverrideStackResolver::class),
            new LegacyContext(
                $mount,
                require __DIR__ . '/../../../../vendor/autoload.php'
            )
        );
        ClassOverrideGenerator::init($loader, true);
        
        static::assertFalse(ClassOverrideGenerator::hasClassOverride(FixtureNotLoadedClass::class));
        static::assertTrue(ClassOverrideGenerator::canOverrideClass(FixtureNotLoadedClass::class));
        static::assertFalse($list->hasClassOverride(FixtureNotLoadedClass::class));
        static::assertTrue($list->canOverrideClass(FixtureNotLoadedClass::class));
        
        ClassOverrideGenerator::registerOverride(FixtureNotLoadedClass::class, FixtureExtendedOverrideClass::class);
        
        static::assertTrue(ClassOverrideGenerator::hasClassOverride(FixtureNotLoadedClass::class));
        static::assertFalse(ClassOverrideGenerator::canOverrideClass(FixtureNotLoadedClass::class));
        static::assertTrue($list->hasClassOverride(FixtureNotLoadedClass::class));
        static::assertFalse($list->canOverrideClass(FixtureNotLoadedClass::class));
    }
}