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


use LaborDigital\T3ba\Core\CodeGeneration\ClassOverridesException;
use LaborDigital\T3ba\Core\CodeGeneration\CodeGenerator;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureClassWithPrivateChildren;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureInvalidClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureNotLoadedClass;
use LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureOverrideClass;
use Neunerlei\PathUtil\Path;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class CodeGeneratorTest extends UnitTestCase
{
    
    public function testAliasCodeGeneration(): void
    {
        $classToOverride = FixtureNotLoadedClass::class;
        $classToOverrideWith = FixtureOverrideClass::class;
        $copyClassFullName = '@copyClassFullName';
        $finalClassName = '@finalClassName';
        
        $namespace = Path::classNamespace($classToOverride);
        $baseName = Path::classBasename($classToOverride);
        
        $expected = <<<PHP
<?php
declare(strict_types=1);
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit its contents,
 * because they will be lost as soon as the TYPO3 cache is cleared
 *
 * The original class can be found here:
 * @see \\$classToOverride
 *
 * The clone of the original class can be found here:
 * @see \\$copyClassFullName
 *
 * The class which is used as override can be found here:
 * @see \\$finalClassName
 */
Namespace $namespace;
if(!class_exists('\\$classToOverride', false)) {

    class $baseName
        extends \\$classToOverrideWith {}
}
PHP;
        
        static::assertEquals($expected, $this->makeInstance()->getClassAliasContent(
            $classToOverride, $classToOverrideWith, $finalClassName, $copyClassFullName
        ));
    }
    
    public function testClassCodeGeneration(): void
    {
        $classToOverride = FixtureClassWithPrivateChildren::class;
        $namespace = Path::classNamespace($classToOverride);
        
        $expected = <<<PHP
<?php
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit its contents,
 * because they will be lost as soon as the TYPO3 cache is cleared
 *
 * THIS FILE IS AUTOMATICALLY GENERATED!
 *
 * This is a copy of the class: $classToOverride
 *
 * It was created by the T3BA extension in order to extend core functionality.
 *
 * @see $classToOverride
 */
/*
 * Copyright LABOR.digital
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
 */

declare(strict_types=1);


namespace $namespace;


use TYPO3\CMS\Core\SingletonInterface;

class CopyClass implements SingletonInterface
{
    protected const CONSTANT = true;
    
    protected \$property = true;
    
    protected static \$staticProperty = [self::CONSTANT];
    
    // We test here if the "final" after the access modifier works too
    protected function func()
    {
    }
    
    public function pubFunc()
    {
    }
    
    function pubFuncWithoutFinal()
    {
        static::internal();
    }
    
    /**
     * @return CopyClass
     */
    static protected function internal(): FixtureClassWithPrivateChildren
    {
    }
}
PHP;
        
        static::assertEquals($expected, $this->makeInstance()->getClassCloneContentOf(
            FixtureClassWithPrivateChildren::class, 'CopyClass'
        ));
    }
    
    public function testClassCodeGenerationForTestsWithMissingClass(): void
    {
        $expected = <<<PHP
<?php
/**
 * CLASS OVERRIDE GENERATOR - GENERATED FILE
 * This file is generated dynamically! You should not edit its contents,
 * because they will be lost as soon as the TYPO3 cache is cleared
 *
 * THIS FILE IS AUTOMATICALLY GENERATED!
 *
 * This is a copy of the class: LaborDigital\Foo
 *
 * It was created by the T3BA extension in order to extend core functionality.
 *
 * @see LaborDigital\Foo
 */
namespace LaborDigital;
class Bar{}

PHP;
        $generator = $this->makeInstance();
        $generator->setTestMode(true);
        static::assertEquals($expected, $generator->getClassCloneContentOf('LaborDigital\\Foo', 'Bar'));
    }
    
    public function testGetClassCloneContentOfFailIfClassCouldNotBeResolved(): void
    {
        $this->expectException(ClassOverridesException::class);
        $this->expectExceptionMessage('Could not create a clone of class: \Foo\Bar because Composer could not resolve it\'s filename!');
        $this->makeInstance()->getClassCloneContentOf('\\Foo\\Bar', 'Baz');
    }
    
    public function testGetClassCloneContentOfFailIfClassNameDoesNotMatchAutoLoadPath(): void
    {
        $this->expectException(ClassOverridesException::class);
        $this->expectExceptionMessage('Failed to rewrite the name of class: FixtureInvalidAutoLoadPathClass to: Baz when creating a copy of class: LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureInvalidAutoLoadPathClass');
        $ns = Path::classNamespace(FixtureInvalidClass::class);
        $this->makeInstance()->getClassCloneContentOf($ns . '\\FixtureInvalidAutoLoadPathClass', 'Baz');
    }
    
    protected function makeInstance(): CodeGenerator
    {
        return new CodeGenerator(require __DIR__ . '/../../../../vendor/autoload.php');
    }
}