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


use LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator;
use LaborDigital\T3ba\Core\CodeGeneration\OverrideStackResolver;
use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Core\VarFs\VarFs;
use LaborDigital\T3ba\Event\ClassOverrideContentFilterEvent;
use LaborDigital\T3ba\Event\ClassOverrideStackFilterEvent;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureExtendedOverrideClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureInvalidClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureOverrideClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureSuperExtendedOverrideClass;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OverrideStackResolverTest extends UnitTestCase
{
    /**
     * @var \LaborDigital\T3ba\Core\VarFs\Mount
     */
    protected $fsMount;
    
    /**
     * @var \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator|null
     */
    protected $codeGenerator;
    
    /**
     * @var bool
     */
    protected $codeGeneratorTestMode = false;
    
    public function testStackResolution(): void
    {
        ob_start();
        $map = $this->makeInstance()->resolve($this->getTestStack());
        ob_end_clean();
        
        static::assertEquals([
            FixtureSuperExtendedOverrideClass::class => FixtureNotLoadedClass::class,
        ], $map);
        
        foreach ($this->getExpectedFileList() as $fileName => $content) {
            static::assertTrue($this->fsMount->hasFile($fileName));
            static::assertEquals(
                $content,
                SerializerUtil::unserializeJson($this->fsMount->getFileContent($fileName)),
                'Failed to find an expected file in mount: ' . $fileName
            );
        }
    }
    
    public function testEventExecutionInStackResolution(): void
    {
        $c = 0;
        $c2 = 0;
        
        $i = $this->makeInstance();
        
        $this->eventBus->addListener(ClassOverrideStackFilterEvent::class,
            function (ClassOverrideStackFilterEvent $e) use (&$c) {
                $c += 10;
                
                static::assertEquals([true], $e->getStack());
                $e->setStack($this->getTestStack());
            });
        
        $this->eventBus->addListener(ClassOverrideContentFilterEvent::class,
            function (ClassOverrideContentFilterEvent $e) use (&$c, &$c2) {
                $c2++;
                $c += 100;
                
                $expected = $this->getExpectedEventArgs($e->getClassNameToOverride());
                
                static::assertEquals($expected[0], $e->getClassNameToOverride());
                static::assertEquals($expected[1], $e->getCopyClassName());
                static::assertEquals($expected[2], $e->getInitialClassName());
                static::assertEquals($expected[3], $e->getFinalClassName());
                static::assertEquals($expected[4], SerializerUtil::unserializeJson($e->getCloneContent()));
                static::assertEquals($expected[5], SerializerUtil::unserializeJson($e->getAliasContent()));
            });
        
        ob_start();
        $m1 = $i->resolve([true]);
        ob_end_clean();
        
        static::assertEquals(3, $c2, 'Not all expected files triggered an event');
        static::assertEquals(310, $c, 'Either not all expected files triggered an event, or the initial event was not executed');
        
        // Try it again -> Now everything should be cached
        ob_start();
        $m2 = $i->resolve([true]);
        ob_end_clean();
        
        static::assertEquals(3, $c2, 'The caching failed, and the files have been processed again');
        static::assertEquals(320, $c, 'The global caching failed, and the process was executed again');
        
        // Both maps should be equal now
        static::assertEquals($m1, $m2);
        
    }
    
    public function testCodeGeneratorTestModeInheritance(): void
    {
        $i = $this->makeInstance();
        static::assertFalse($this->codeGeneratorTestMode);
        $i->setTestMode(true);
        static::assertFalse($this->codeGeneratorTestMode);
        $i->getCodeGenerator();
        static::assertTrue($this->codeGeneratorTestMode);
    }
    
    protected function getTestStack(): array
    {
        return [
            FixtureNotLoadedClass::class => FixtureOverrideClass::class,
            FixtureOverrideClass::class => FixtureExtendedOverrideClass::class,
            FixtureExtendedOverrideClass::class => FixtureSuperExtendedOverrideClass::class,
        ];
    }
    
    protected function getExpectedEventArgs(string $classToOverride): array
    {
        $basename = Inflector::toFile($classToOverride);
        $cloneFilename = $basename . '-clone.php';
        $aliasFilename = $basename . '.php';
        $files = $this->getExpectedFileList();
        
        switch ($classToOverride) {
            case FixtureNotLoadedClass::class:
                return [
                    FixtureNotLoadedClass::class,
                    'T3BaCopyFixtureNotLoadedClass',
                    FixtureNotLoadedClass::class,
                    FixtureSuperExtendedOverrideClass::class,
                    $files[$cloneFilename],
                    $files[$aliasFilename],
                ];
            case FixtureOverrideClass::class:
                return [
                    FixtureOverrideClass::class,
                    'T3BaCopyFixtureOverrideClass',
                    FixtureNotLoadedClass::class,
                    FixtureSuperExtendedOverrideClass::class,
                    $files[$cloneFilename],
                    $files[$aliasFilename],
                ];
            case FixtureExtendedOverrideClass::class:
                return [
                    FixtureExtendedOverrideClass::class,
                    'T3BaCopyFixtureExtendedOverrideClass',
                    FixtureNotLoadedClass::class,
                    FixtureSuperExtendedOverrideClass::class,
                    $files[$cloneFilename],
                    $files[$aliasFilename],
                ];
            default:
                static::fail('There are no known event args of a class called: ' . $classToOverride);
        }
    }
    
    protected function getExpectedFileList(): array
    {
        $ns = Path::classNamespace(FixtureInvalidClass::class);
        
        return [
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixturenotloadedclass-clone.php' => [
                FixtureNotLoadedClass::class,
                'T3BaCopyFixtureNotLoadedClass',
            ],
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixturenotloadedclass.php' => [
                FixtureNotLoadedClass::class,
                FixtureOverrideClass::class,
                FixtureSuperExtendedOverrideClass::class,
                $ns . '\\T3BaCopyFixtureNotLoadedClass',
            ],
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixtureoverrideclass-clone.php' => [
                FixtureOverrideClass::class,
                'T3BaCopyFixtureOverrideClass',
            ],
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixtureoverrideclass.php' => [
                FixtureOverrideClass::class,
                FixtureExtendedOverrideClass::class,
                FixtureSuperExtendedOverrideClass::class,
                $ns . '\\T3BaCopyFixtureOverrideClass',
            ],
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixtureextendedoverrideclass-clone.php' => [
                FixtureExtendedOverrideClass::class,
                'T3BaCopyFixtureExtendedOverrideClass',
            ],
            'labordigital-t3ba-tests-fixture-core-codegeneration-fixtureextendedoverrideclass.php' => [
                FixtureExtendedOverrideClass::class,
                FixtureSuperExtendedOverrideClass::class,
                FixtureSuperExtendedOverrideClass::class,
                $ns . '\\T3BaCopyFixtureExtendedOverrideClass',
            ],
        ];
    }
    
    protected function makeInstance(): OverrideStackResolver
    {
        $this->fsMount = (new VarFs())->getMount('test-' . microtime(true));
        $this->fsMount->flush();
        
        $this->eventBus = new TypoEventBus();
        
        $this->codeGeneratorTestMode = false;
        $this->codeGenerator = $this->createMock(CodeGenerator::class);
        $this->codeGenerator->method('getClassCloneContentOf')->willReturnCallback(static function () {
            return SerializerUtil::serializeJson(func_get_args(), ['pretty']);
        });
        $this->codeGenerator->method('getClassAliasContent')->willReturnCallback(static function () {
            return SerializerUtil::serializeJson(func_get_args(), ['pretty']);
        });
        $this->codeGenerator->method('setTestMode')->willReturnCallback(function (bool $state) {
            $this->codeGeneratorTestMode = $state;
        });
        
        return new OverrideStackResolver(
            $this->eventBus,
            $this->fsMount,
            function () {
                return $this->codeGenerator;
            }
        );
    }
}