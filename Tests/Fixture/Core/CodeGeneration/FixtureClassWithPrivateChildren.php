<?php
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


namespace LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration;


use TYPO3\CMS\Core\SingletonInterface;

final class FixtureClassWithPrivateChildren implements SingletonInterface
{
    private const CONSTANT = true;
    
    private $property = true;
    
    private static $staticProperty = [self::CONSTANT];
    
    // We test here if the "final" after the access modifier works too
    private final function func()
    {
    }
    
    final public function pubFunc()
    {
    }
    
    final function pubFuncWithoutFinal()
    {
        self::internal();
    }
    
    /**
     * @return \LaborDigital\T3ba\Tests\Fixture\Core\CodeGeneration\FixtureClassWithPrivateChildren
     */
    final static private function internal(): FixtureClassWithPrivateChildren
    {
    }
}