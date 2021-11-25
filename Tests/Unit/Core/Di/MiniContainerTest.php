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
 * Last modified: 2021.11.25 at 21:35
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Core\Di;


use LaborDigital\T3ba\Core\Di\MiniContainer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MiniContainerTest extends UnitTestCase
{
    public function testInitialInstancesCanBeSet(): void
    {
        $c = new class ( ) { };
        $i = new MiniContainer(['foo' => $c]);
        static::assertTrue($i->has('foo'));
        static::assertSame($c, $i->get('foo'));
    }
    
    public function testSetGetAndHasWork(): void
    {
        $i = new MiniContainer();
        static::assertFalse($i->has('foo'));
        static::assertFalse($i->has('false'));
        static::assertNull($i->get('foo'));
        
        $c = new class ( ) { };
        $i->set('foo', $c);
        static::assertTrue($i->has('foo'));
        static::assertFalse($i->has('false'));
        static::assertSame($c, $i->get('foo'));
    }
}