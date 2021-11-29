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
 * Last modified: 2021.11.26 at 10:03
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Tool\Tca\Builder\Util;


use LaborDigital\T3ba\Tests\Fixture\FixtureUserDisplayCondition;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;
use LaborDigital\T3ba\Tool\Tca\Builder\Util\DisplayConditionBuilder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class DisplayConditionBuilderTest extends UnitTestCase
{
    public function simpleConditionDataProvider(): array
    {
        return [
            [
                ['FIELD', 'foo', '=', 'bar'],
                'FIELD:foo:=:bar',
            ],
            [
                ['foo', '!=', 'bar'],
                'FIELD:foo:!=:bar',
            ],
            [
                ['foo', '<', 'bar'],
                'FIELD:foo:<:bar',
            ],
            [
                ['foo', '<=', 'bar'],
                'FIELD:foo:<=:bar',
            ],
            [
                ['foo', '>', 'bar'],
                'FIELD:foo:>:bar',
            ],
            [
                ['foo', '>=', 'bar'],
                'FIELD:foo:>=:bar',
            ],
            [
                ['HIDE_FOR_NON_ADMINS'],
                'HIDE_FOR_NON_ADMINS',
            ],
            [
                ['REC'],
                'REC:NEW',
            ],
            [
                ['REC', 'new'],
                'REC:NEW',
            ],
            [
                ['VERSION', 'is', true],
                'VERSION:IS:true',
            ],
            [
                ['VERSION', 'is', false],
                'VERSION:IS:false',
            ],
            [
                ['foo', 'REQ'],
                'FIELD:foo:REQ:true',
            ],
            [
                ['foo', 'REQ', false],
                'FIELD:foo:REQ:false',
            ],
            [
                ['foo', 'in', [1, 2, 3]],
                'FIELD:foo:IN:1,2,3',
            ],
            [
                ['foo', '!IN', [1, 2, 3]],
                'FIELD:foo:!IN:1,2,3',
            ],
            [
                ['foo', '-', [1, 2, 3]],
                'FIELD:foo:-:1-2',
            ],
            [
                ['foo', '!-', [1, 2]],
                'FIELD:foo:!-:1-2',
            ],
            [
                ['foo', '!-', '1-3'],
                'FIELD:foo:!-:1-3',
            ],
            [
                [FixtureUserDisplayCondition::class],
                'USER:' . FixtureUserDisplayCondition::class . '->evaluate',
            ],
            [
                ['foo', '=', 'bar'],
                'FIELD:foo:=:bar',
            ],
            [
                ['FIELD', 'foo', '=', 'bar'],
                'FIELD:foo:=:bar',
            ],
            [
                [[['USER', 'MyClass->myMethod', 'my', 'argument']]],
                'USER:MyClass->myMethod:my:argument',
            ],
            [
                [['foo', '=', 'bar'], ['bar', '=', 'bar']],
                ['AND' => ['FIELD:foo:=:bar', 'FIELD:bar:=:bar']],
            ],
            [
                [['FIELD', 'foo', '=', 'bar'], ['bar', '=', 'bar']],
                ['AND' => ['FIELD:foo:=:bar', 'FIELD:bar:=:bar']],
            ],
            [
                ['OR' => [['foo', '=', 'bar'], 'FIELD:bar:=:bar']],
                ['OR' => ['FIELD:foo:=:bar', 'FIELD:bar:=:bar']],
            ],
            [
                ['OR' => [[['foo', '=', 'bar']], 'FIELD:bar:=:bar']],
                ['OR' => ['FIELD:foo:=:bar', 'FIELD:bar:=:bar']],
            ],
            [
                [
                    'OR' => [
                        [
                            ['foo', '=', 'bar'],
                            ['baz', '=', 'bar'],
                        ],
                        'FIELD:bar:=:bar',
                    ],
                ],
                [
                    'OR' => [
                        [
                            'AND' => ['FIELD:foo:=:bar', 'FIELD:baz:=:bar'],
                        ],
                        'FIELD:bar:=:bar',
                    ],
                ],
            ],
            [
                [
                    'OR' => [
                        [
                            'AND' => [
                                FixtureUserDisplayCondition::class,
                                'FIELD:baz:=:bar',
                            ],
                        ],
                        ['baz', '=', 'bar'],
                    ],
                ],
                [
                    'OR' => [
                        [
                            'AND' => [
                                'USER:' . FixtureUserDisplayCondition::class . '->evaluate',
                                'FIELD:baz:=:bar',
                            ],
                        ],
                        'FIELD:baz:=:bar',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @dataProvider simpleConditionDataProvider
     */
    public function testSimpleConditions(array $definition, $expected): void
    {
        $builder = new DisplayConditionBuilder();
        static::assertEquals($expected, $builder->build($this->getElementMock(), $definition));
    }
    
    public function unchangedConditionDataProvider(): array
    {
        return [
            [
                [
                    'OR' => [
                        'FIELD:header_layout:=:default',
                        'FIELD:header_layout:=:',
                    ],
                ],
            ],
            [
                [
                    'AND' => [
                        [
                            'OR' => [
                                'FIELD:header_layout:=:default',
                                'FIELD:header_layout:=:',
                            ],
                        ],
                        'FIELD:header_use_media:=:1',
                    ],
                ],
            ],
            [
                [
                    'OR' => [
                        'FIELD:type:=:internal',
                        'FIELD:type:REQ:false',
                    ],
                ],
            ],
        ];
    }
    
    /**
     * @dataProvider unchangedConditionDataProvider
     */
    public function testThatConditionsAreNotMangledWithIfNotNecessary(array $definition): void
    {
        $builder = new DisplayConditionBuilder();
        static::assertEquals($definition, $builder->build($this->getElementMock(), $definition));
    }
    
    public function testInvalidTypeFail(): void
    {
        $this->expectException(TcaBuilderException::class);
        $this->expectExceptionMessage(
            'Failed to build display condition on table "foo_table", because: Invalid type in rule: "FOO:bar:=:faz"');
        $builder = new DisplayConditionBuilder();
        $builder->build($this->getElementMock(), [['FOO', 'bar', '=', 'faz']]);
    }
    
    public function testInvalidNestedAssocFail(): void
    {
        $this->expectException(TcaBuilderException::class);
        $this->expectExceptionMessage(
            'Failed to build display condition on table "foo_table", because: Nested display conditions can\'t be associative arrays!');
        $builder = new DisplayConditionBuilder();
        $builder->build($this->getElementMock(), [['foo' => ['bar' => 'baz']]]);
    }
    
    public function testInvalidFieldConditionCountFail(): void
    {
        $this->expectException(TcaBuilderException::class);
        $this->expectExceptionMessage(
            'Failed to build display condition on table "foo_table", because: Invalid display condition: "FIELD:foo:", an array for a "FIELD" type can have exactly 4 elements ONLY');
        $builder = new DisplayConditionBuilder();
        $builder->build($this->getElementMock(), [['FIELD', 'foo']]);
    }
    
    public function testStringBasedConditions(): void
    {
        $builder = new DisplayConditionBuilder();
        $el = $this->getElementMock();
        
        static::assertEquals('foo', $builder->buildFromString($el, 'foo'));
        static::assertEquals('FIELD:foo:=:bar', $builder->buildFromString($el, 'FIELD:foo:=:bar'));
        static::assertEquals(\stdClass::class, $builder->buildFromString($el, \stdClass::class));
        static::assertEquals(static::class, $builder->buildFromString($el, static::class));
        static::assertEquals(static::class . 'Foo', $builder->buildFromString($el, static::class . 'Foo'));
        static::assertEquals('USER:' . FixtureUserDisplayCondition::class . '->evaluate', $builder->buildFromString($el, FixtureUserDisplayCondition::class));
    }
    
    public function testFailForField(): void
    {
        $el = $this->createMock(AbstractField::class);
        $el->method('getId')->willReturn('foo');
        $el->method('getRoot')->willReturn($this->getElementMock()->getRoot());
        
        $this->expectException(TcaBuilderException::class);
        $this->expectExceptionMessage(
            'Failed to build display condition for field "foo" on table "foo_table", because: Invalid type in rule: "FOO:bar:=:faz"');
        $builder = new DisplayConditionBuilder();
        $builder->build($el, [['FOO', 'bar', '=', 'faz']]);
    }
    
    public function testFailForContainer(): void
    {
        $el = $this->createMock(FlexSection::class);
        $el->method('getId')->willReturn('foo');
        $el->method('getRoot')->willReturn($this->getElementMock()->getRoot());
        
        $this->expectException(TcaBuilderException::class);
        $this->expectExceptionMessage(
            'Failed to build display condition for section "foo" on table "foo_table", because: Invalid type in rule: "FOO:bar:=:faz"');
        $builder = new DisplayConditionBuilder();
        $builder->build($el, [['FOO', 'bar', '=', 'faz']]);
    }
    
    protected function getElementMock(): AbstractElement
    {
        $el = $this->createMock(AbstractElement::class);
        $tcaTable = $this->createMock(TcaTableType::class);
        $tcaTable->method('getTableName')->willReturn('foo_table');
        $el->method('getRoot')->willReturn($tcaTable);
        
        return $el;
    }
}