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
 * Last modified: 2021.11.29 at 10:11
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\Util;


use LaborDigital\T3ba\Core\Exception\SingletonNotSetException;
use LaborDigital\T3ba\Tests\Fixture\FixtureSingletonInstance;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class SingletonInstanceTraitTest extends UnitTestCase
{
    public function testFailIfNoInstanceWasSet(): void
    {
        $this->expectException(SingletonNotSetException::class);
        $this->expectExceptionMessage('The singleton instance was not injected using setInstance()');
        FixtureSingletonInstance::getInstance();
    }
    
    public function testSetAndRetrieval(): void
    {
        $i = new FixtureSingletonInstance();
        FixtureSingletonInstance::setInstance($i);
        static::assertSame($i, FixtureSingletonInstance::getInstance());
    }
}