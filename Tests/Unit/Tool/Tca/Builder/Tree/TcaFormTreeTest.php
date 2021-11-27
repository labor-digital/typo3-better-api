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
 * Last modified: 2021.11.26 at 16:09
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tests\Unit\Tool\Tca\Builder\Tree;


use LaborDigital\T3ba\Tests\Unit\Tool\Tca\Builder\TcaBuilderTestTrait;
use LaborDigital\T3ba\Tests\Util\TestLockPick;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractField;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\InvalidNestingException;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\NonUniqueIdException;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\Tree;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TcaFormTreeTest extends UnitTestCase
{
    use TcaBuilderTestTrait;
    
    public function testTreeInstantiation(): void
    {
        $tree = $this->getTree();
        static::assertIsObject($tree);
        static::assertIsObject($tree->getDefaultNode());
        static::assertTrue($tree->getRootNode()->isRoot());
        static::assertSame($tree, $tree->getRootNode()->getTree());
    }
    
    public function testDefaultTabId(): void
    {
        $tree = $this->getTree();
        static::assertEquals(0, $tree->getDefaultTabId());
        $tree->makeNewNode('a', Node::TYPE_FIELD);
        static::assertEquals([0], array_keys(iterator_to_array($tree->getSortedNodes(Node::TYPE_TAB))));
        
        $tree = $this->getTree();
        $tree->setDefaultTabId(12);
        static::assertEquals(12, $tree->getDefaultTabId());
        $tree->makeNewNode('a', Node::TYPE_FIELD);
        static::assertEquals([12], array_keys(iterator_to_array($tree->getSortedNodes(Node::TYPE_TAB))));
    }
    
    public function provideTestNodeGenerationAndSimpleRetrievalData(): array
    {
        return [
            [
                'id' => 'f-a',
                'expectId' => 'f-a',
                'type' => Node::TYPE_FIELD,
                'typeValidation' => 'isField',
                'parentFinder' => 'getDefaultNode',
            ],
            [
                'id' => 0,
                'expectId' => 0,
                'type' => Node::TYPE_TAB,
                'typeValidation' => 'isTab',
                'parentFinder' => 'getRootNode',
            ],
            [
                'id' => 'c-a',
                'expectId' => '_c-a',
                'type' => Node::TYPE_CONTAINER,
                'typeValidation' => 'isContainer',
                'parentFinder' => 'getDefaultNode',
            ],
            [
                'id' => 'ns-1',
                'expectId' => 'ns-1',
                'type' => Node::TYPE_NL,
                'typeValidation' => 'isLineBreak',
                'parentFinder' => 'getDefaultNode',
            ],
        ];
    }
    
    /**
     * @dataProvider provideTestNodeGenerationAndSimpleRetrievalData
     *
     * @param           $id
     * @param           $expectId
     * @param   int     $type
     * @param   string  $typeValidator
     * @param   string  $parentFinder
     */
    public function testNodeGenerationAndSimpleRetrieval(
        $id,
        $expectId,
        int $type,
        string $typeValidator,
        string $parentFinder
    ): void
    {
        $tree = $this->getTree();
        $node = $tree->makeNewNode($id, $type);
        
        static::assertEquals($expectId, $node->getId());
        static::assertEquals($type, $node->getType());
        static::assertTrue($node->$typeValidator());
        static::assertSame(
            $tree->$parentFinder(),
            $node->getParent()
        );
        
        static::assertSame($node, $tree->getNode($id));
    }
    
    public function testTabRetrievalWithStringId(): void
    {
        $tree = $this->getTree();
        
        static::assertFalse($tree->isAllowTabIdStrings());
        $tree->setAllowTabIdStrings(true);
        static::assertTrue($tree->isAllowTabIdStrings());
        
        $tab = $tree->makeNewNode('foo', Node::TYPE_TAB);
        static::assertSame($tab, $tree->getNode('foo'));
    }
    
    public function testNodeGenerationAndRetrieval(): void
    {
        $tree = $this->getTree();
        
        $field = $tree->makeNewNode('a', Node::TYPE_FIELD);
        $container = $tree->makeNewNode('a', Node::TYPE_CONTAINER);
        // Tab with id "0" is implicitly added and therefore should exist
        $tab = $tree->makeNewNode(1, Node::TYPE_TAB);
        
        // Check if "has" works as expected
        static::assertTrue($tree->hasNode(0));
        static::assertTrue($tree->hasNode(1));
        static::assertTrue($tree->hasNode('a'));
        static::assertTrue($tree->hasNode('a', Node::TYPE_CONTAINER));
        static::assertTrue($tree->hasNode('_a'));
        static::assertTrue($tree->hasNode('_a', Node::TYPE_CONTAINER));
        
        // Check if the retrieval works as expected
        static::assertSame($field, $tree->getNode('a'));
        static::assertSame($container, $tree->getNode('a', Node::TYPE_CONTAINER));
        static::assertSame($container, $tree->getNode('_a'));
        static::assertSame($tab, $tree->getNode(1));
        static::assertNotSame($tab, $tree->getNode(0));
        static::assertEquals(Node::TYPE_TAB, $tree->getNode(1)->getType());
        
        // Don't retrieve the container if we explicitly ask for a field
        static::assertNull($tree->getNode('_a', Node::TYPE_FIELD));
    }
    
    public function testIfDuplicateNodeIdThrowsException(): void
    {
        $this->expectException(NonUniqueIdException::class);
        $tree = $this->getTree();
        $tree->makeNewNode('0', Node::TYPE_FIELD);
        $tree->makeNewNode('0', Node::TYPE_FIELD);
    }
    
    public function testIfInvalidNestingThrowsException(): void
    {
        $this->expectException(InvalidNestingException::class);
        $this->expectExceptionMessage(
            'You can\'t create a new node with id: "_1", and type: "2" here, because the parent with id: "_0" ' .
            'is of the same type. Did you try to nest a palette/section inside a palette/section? This does not work!');
        $tree = $this->getTree();
        $tree->setDefaultNode($tree->makeNewNode(0, Node::TYPE_CONTAINER));
        $tree->makeNewNode(1, Node::TYPE_CONTAINER);
    }
    
    public function provideTestSortedListLookupData(): array
    {
        return [
            [
                'generator' => function (Tree $tree): array {
                    return [
                        $tree->makeNewNode('a', Node::TYPE_FIELD),
                        $tree->makeNewNode('b', Node::TYPE_FIELD),
                        $tree->makeNewNode('c', Node::TYPE_FIELD),
                    ];
                },
                'lookupType' => Node::TYPE_FIELD,
            ],
            [
                'generator' => function (Tree $tree): array {
                    return [
                        $tree->makeNewNode('a', Node::TYPE_CONTAINER),
                        $tree->makeNewNode('b', Node::TYPE_CONTAINER),
                        $tree->makeNewNode('c', Node::TYPE_CONTAINER),
                    ];
                },
                'lookupType' => Node::TYPE_CONTAINER,
            ],
            [
                'generator' => function (Tree $tree): array {
                    return [
                        $tree->makeNewNode(1, Node::TYPE_TAB),
                        $tree->makeNewNode(2, Node::TYPE_TAB),
                        $tree->makeNewNode(3, Node::TYPE_TAB),
                    ];
                },
                'lookupType' => Node::TYPE_TAB,
            ],
            // Now mix and match a bit
            [
                'generator' => function (Tree $tree): array {
                    $expect = [];
                    $expect[] = $tree->makeNewNode('a', Node::TYPE_FIELD);
                    $tree->makeNewNode('a', Node::TYPE_CONTAINER);
                    $expect[] = $tree->makeNewNode('b', Node::TYPE_FIELD);
                    $tree->makeNewNode(1, Node::TYPE_TAB);
                    $container = $tree->makeNewNode('b', Node::TYPE_CONTAINER);
                    $expect[] = $containerChild = $tree->makeNewNode('c', Node::TYPE_FIELD);
                    $container->addChild($containerChild, Node::INSERT_MODE_BOTTOM);
                    
                    return $expect;
                },
                'lookupType' => Node::TYPE_FIELD,
            ],
            [
                'generator' => function (Tree $tree): array {
                    $expect = [];
                    $tree->makeNewNode('a', Node::TYPE_FIELD);
                    $expect[] = $tree->makeNewNode('a', Node::TYPE_CONTAINER);
                    $tree->makeNewNode('b', Node::TYPE_FIELD);
                    $tree->makeNewNode(1, Node::TYPE_TAB);
                    $expect[] = $tree->makeNewNode('b', Node::TYPE_CONTAINER);
                    $tree->makeNewNode('c', Node::TYPE_FIELD);
                    
                    return $expect;
                },
                'lookupType' => Node::TYPE_CONTAINER,
            ],
            [
                'generator' => function (Tree $tree): array {
                    $expect = [];
                    $tree->makeNewNode('a', Node::TYPE_FIELD);
                    $tree->makeNewNode('a', Node::TYPE_CONTAINER);
                    $tree->makeNewNode('b', Node::TYPE_FIELD);
                    $expect[] = $tree->getNode(0); // We have to fetch the implicitly created first tab here!
                    $expect[] = $tree->makeNewNode(1, Node::TYPE_TAB);
                    $tree->makeNewNode('b', Node::TYPE_CONTAINER);
                    $tree->makeNewNode('c', Node::TYPE_FIELD);
                    
                    return $expect;
                },
                'lookupType' => Node::TYPE_TAB,
            ],
        ];
    }
    
    /**
     * @dataProvider provideTestSortedListLookupData
     *
     * @param   callable  $generator
     * @param   int       $lookupType
     */
    public function testSortedListLookup(callable $generator, int $lookupType): void
    {
        $tree = $this->getTree();
        $expectedList = $generator($tree);
        
        foreach ($tree->getSortedNodes($lookupType) as $node) {
            $expectedNode = array_shift($expectedList);
            static::assertSame($expectedNode, $node);
        }
    }
    
    public function testExceptionOnInvalidSortableNodeType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        foreach ($this->getTree()->getSortedNodes(11) as $node) {
            $node;
        }
    }
    
    public function testNesting(): void
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $fieldA = $tree->makeNewNode('a', Node::TYPE_FIELD);
        $tab0 = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1 = $tree->makeNewNode(1, Node::TYPE_TAB);
        $fieldB = $tree->makeNewNode('b', Node::TYPE_FIELD);
        $fieldC = $tree->makeNewNode('c', Node::TYPE_FIELD);
        
        // Add fields to a container
        $container = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        $fieldD = $tree->makeNewNode('d', Node::TYPE_FIELD);
        $container->addChild($fieldD, Node::INSERT_MODE_BOTTOM);
        // ================================= END
        
        static::assertSame($tab0, $fieldA->getParent());
        static::assertSame($tab0, $fieldA->getContainingTab());
        static::assertEquals([$fieldA->getId() => $fieldA], $tab0->getChildren());
        
        static::assertSame($tab1, $fieldB->getParent());
        static::assertSame($tab1, $fieldB->getContainingTab());
        static::assertSame($tab1, $fieldC->getParent());
        static::assertSame($tab1, $fieldC->getContainingTab());
        
        static::assertSame($tab1, $container->getParent());
        static::assertSame($tab1, $container->getContainingTab());
        static::assertSame($container, $fieldD->getParent());
        static::assertSame($tab1, $fieldD->getContainingTab());
        static::assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldC->getId() => $fieldC,
            $container->getId() => $container,
        ], $tab1->getChildren());
    }
    
    public function testPositionParsing(): void
    {
        $tree = $this->getTree();
        $fieldA = $tree->makeNewNode('a', Node::TYPE_FIELD);
        $containerA = $tree->makeNewNode('a', Node::TYPE_CONTAINER);
        $containerB = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        $tab = $tree->makeNewNode(1, Node::TYPE_TAB);
        
        // Automatic insert mode based on the pivot node
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition('a'));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerA], $tree->parseMovePosition('_a'));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerB], $tree->parseMovePosition('container'));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition('1'));
        static::assertEquals([Node::INSERT_MODE_AFTER, null], $tree->parseMovePosition('foo'));
        // As array
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition(['a']));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerA], $tree->parseMovePosition(['_a']));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerB], $tree->parseMovePosition(['container']));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition(['1']));
        static::assertEquals([Node::INSERT_MODE_AFTER, null], $tree->parseMovePosition(['foo']));
        // Using reference
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition([$fieldA]));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerA], $tree->parseMovePosition([$containerA]));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $containerB], $tree->parseMovePosition([$containerB]));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition([$tab]));
        
        // Insert mode parsing
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition('after:a'));
        static::assertEquals([Node::INSERT_MODE_TOP, $fieldA], $tree->parseMovePosition('top:a'));
        static::assertEquals([Node::INSERT_MODE_AFTER, $containerB], $tree->parseMovePosition('after:container'));
        static::assertEquals([Node::INSERT_MODE_BEFORE, $fieldA], $tree->parseMovePosition('before:a'));
        static::assertEquals([Node::INSERT_MODE_TOP, $containerB], $tree->parseMovePosition('top:container'));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition('bottom:1'));
        // As array
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition(['after', 'a']));
        static::assertEquals([Node::INSERT_MODE_TOP, $fieldA], $tree->parseMovePosition(['top', 'a']));
        static::assertEquals([Node::INSERT_MODE_AFTER, $containerB], $tree->parseMovePosition(['after', 'container']));
        static::assertEquals([Node::INSERT_MODE_BEFORE, $fieldA], $tree->parseMovePosition(['before', 'a']));
        static::assertEquals([Node::INSERT_MODE_TOP, $containerB], $tree->parseMovePosition(['top', 'container']));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition(['bottom', '1']));
        // Using reference
        static::assertEquals([Node::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition(['after', $fieldA]));
        static::assertEquals([Node::INSERT_MODE_TOP, $fieldA], $tree->parseMovePosition(['top', $fieldA]));
        static::assertEquals([Node::INSERT_MODE_AFTER, $containerB], $tree->parseMovePosition(['after', $containerB]));
        static::assertEquals([Node::INSERT_MODE_BEFORE, $fieldA], $tree->parseMovePosition(['before', $fieldA]));
        static::assertEquals([Node::INSERT_MODE_TOP, $containerB], $tree->parseMovePosition(['top', $containerB]));
        static::assertEquals([Node::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition(['bottom', $tab]));
    }
    
    public function testInvalidPositionArrayLengthFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A position array can only contain one or exactly two items');
        
        $tree = $this->getTree();
        $tree->parseMovePosition(['foo', 'bar', 'baz']);
    }
    
    public function testInvalidPositionTypeFail(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(
            'A position can be defined either as string, array, object or number, however ' .
            'a value of type: "boolean" was given');
        
        $tree = $this->getTree();
        $tree->parseMovePosition(false);
    }
    
    public function testInvalidPivotObjectFail(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(
            'Invalid position given. The pivot-id is an object of type: "stdClass" while only objects ' .
            'of type: "' . Node::class . '" are allowed');
        
        $tree = $this->getTree();
        $tree->parseMovePosition(new \stdClass());
    }
    
    public function testMoving(): void
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $fieldA = $tree->makeNewNode('a', Node::TYPE_FIELD);
        $tab0 = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1 = $tree->makeNewNode(1, Node::TYPE_TAB);
        
        // Add fields to a container
        $container = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        $fieldB = $tree->makeNewNode('b', Node::TYPE_FIELD);
        $container->addChild($fieldB, Node::INSERT_MODE_BOTTOM);
        // ================================= END
        
        // Move $fieldA to the BOTTOM of container
        $fieldA->moveTo('container');
        static::assertSame($tab1, $fieldA->getContainingTab());
        static::assertSame($container, $fieldA->getParent());
        static::assertSame($fieldA, $tree->getNode('a'));
        static::assertEquals([], $tab0->getChildren());
        static::assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Move $fieldA to the TOP of container
        $fieldA->moveTo('top:container');
        static::assertSame($container, $fieldA->getParent());
        static::assertEquals([
            $fieldA->getId() => $fieldA,
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        
        // Move $fieldA to the TOP of $tab1
        $fieldA->moveTo('top:1');
        static::assertSame($tab1, $fieldA->getParent());
        static::assertEquals([
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        static::assertEquals([
            $fieldA->getId() => $fieldA,
            $container->getId() => $container,
        ], $tab1->getChildren());
        
        // Move $fieldA BEFORE $fieldB using TOP
        $fieldA->moveTo('top:b');
        static::assertSame($container, $fieldA->getParent());
        static::assertEquals([
            $fieldA->getId() => $fieldA,
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        
        // Move $fieldA AFTER $fieldB using BOTTOM
        $fieldA->moveTo('bottom:b');
        static::assertSame($container, $fieldA->getParent());
        static::assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Check if move to self is correctly ignored
        $fieldA->moveTo('top:a');
        static::assertSame($container, $fieldA->getParent());
        static::assertSame($tab1, $fieldA->getContainingTab());
        static::assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Check if moving by reference gets executed correctly.
        $fieldA->moveTo(['before', $fieldB]);
        static::assertEquals([
            $fieldA->getId() => $fieldA,
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        
        // Move $tab1 BEFORE $tab0
        $tab1->moveTo('before:0');
        static::assertSame($tree->getRootNode(), $tab1->getParent());
        static::assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0,
        ], $tree->getRootNode()->getChildren());
        
        // Now test if we can move $tab1 INTO $tab0 -> This should not work, and
        // the tab should end up either before or after the other tab.
        $tab1->moveTo('bottom:0');
        static::assertSame($tree->getRootNode(), $tab1->getParent());
        static::assertEquals([
            $tab0->getId() => $tab0,
            $tab1->getId() => $tab1,
        ], $tree->getRootNode()->getChildren());
        
        $tab1->moveTo('top:0');
        static::assertSame($tree->getRootNode(), $tab1->getParent());
        static::assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0,
        ], $tree->getRootNode()->getChildren());
        
        // Test to move a tab into a container -> This should not work either
        $tab0->moveTo('top:container');
        static::assertSame($tree->getRootNode(), $tab0->getParent());
        static::assertEquals([
            $tab0->getId() => $tab0, // The command translates to "before:1"
            $tab1->getId() => $tab1,
        ], $tree->getRootNode()->getChildren());
        
        $tab0->moveTo('after:container');
        static::assertSame($tree->getRootNode(), $tab0->getParent());
        static::assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0, // The command translates to "after:1"
        ], $tree->getRootNode()->getChildren());
        
        // Test if we can move a field before / after a tab
        // This should move the element to the top/bottom of the respective tab instead.
        $fieldA->moveTo('before:0');
        static::assertSame($tab0, $fieldA->getParent());
        static::assertEquals([
            $fieldA->getId() => $fieldA,  // The command translates to "top:0"
        ], $tab0->getChildren());
        
        $fieldA->moveTo('after:1');
        static::assertSame($tab1, $fieldA->getParent());
        static::assertEquals([
            $container->getId() => $container,
            $fieldA->getId() => $fieldA,
        ], $tab1->getChildren());
        
        // Test to move a container between tabs
        $fieldA->moveTo('container');
        static::assertEquals([$container->getId() => $container], $tab1->getChildren());
        $container->moveTo('0');
        static::assertSame($tab0, $container->getParent());
        static::assertSame($tab0, $container->getContainingTab());
        static::assertSame($tab0, $fieldA->getContainingTab());
        static::assertSame($tab0, $fieldB->getContainingTab());
        static::assertEquals([], $tab1->getChildren());
        static::assertEquals([$container->getId() => $container], $tab0->getChildren());
        
        // Test if we can move a container before / after a tab
        // Similar to a field, this should move the element to the top/bottom of the respective tab instead.
        $container->moveTo('before:0');
        static::assertSame($tab0, $container->getParent());
        static::assertEquals([$container->getId() => $container], $tab0->getChildren());
        
        $container->moveTo('after:1');
        static::assertSame($tab1, $container->getParent());
        static::assertEquals([$container->getId() => $container], $tab1->getChildren());
        
        // Test if we ignore a move to a non-existent element
        $container->moveTo('after:5');
        static::assertSame($tab1, $container->getParent());
        static::assertEquals([$container->getId() => $container], $tab1->getChildren());
    }
    
    public function testDefaultNodeSetting(): void
    {
        $tree = $this->getTree();
        
        $container = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        
        static::assertFalse($tree->hasConfiguredDefaultNode());
        
        $tree->setDefaultNode($container);
        static::assertTrue($tree->hasConfiguredDefaultNode());
        static::assertSame($container, $tree->getDefaultNode());
        
        $field = $tree->makeNewNode('a', Node::TYPE_FIELD);
        static::assertSame($container, $field->getParent());
        
        $field = $tree->makeNewNode('b', Node::TYPE_FIELD);
        static::assertSame($container, $field->getParent());
        
        $tree->setDefaultNode(null);
        static::assertSame($tree->getNode(0), $tree->getDefaultNode());
        static::assertFalse($tree->hasConfiguredDefaultNode());
        
        $field = $tree->makeNewNode('c', Node::TYPE_FIELD);
        static::assertSame($tree->getNode(0), $field->getParent());
        
        // Make sure fields cannot be set as default nodes
        $tree->setDefaultNode($field);
        static::assertSame($tree->getNode(0), $field->getParent());
    }
    
    public function testNodeRemoval(): void
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $tree->makeNewNode('a', Node::TYPE_FIELD);
        $tab0 = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1 = $tree->makeNewNode(1, Node::TYPE_TAB);
        $fieldB = $tree->makeNewNode('b', Node::TYPE_FIELD);
        $fieldC = $tree->makeNewNode('c', Node::TYPE_FIELD);
        
        // Add fields to a container
        $container = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        $fieldD = $tree->makeNewNode('d', Node::TYPE_FIELD);
        $container->addChild($fieldD, Node::INSERT_MODE_BOTTOM);
        // ================================= END
        
        // Remove a field from a container
        $fieldD->remove();
        static::assertEquals([], $container->getChildren());
        static::assertNull($tree->getNode('d', Node::TYPE_FIELD));
        
        // Remove a whole container
        $fieldB->moveTo('container');
        $container->remove();
        static::assertEquals([], $container->getChildren());
        static::assertNull($tree->getNode('b', Node::TYPE_FIELD));
        static::assertNull($tree->getNode('container', Node::TYPE_CONTAINER));
        static::assertEquals([$fieldC->getId() => $fieldC], $tab1->getChildren());
        
        // Remove all tabs
        $tab1->remove();
        $tab0->remove();
        static::assertEquals([], $tree->getRootNode()->getChildren());
        
        // Look inside the tree to check if the nodes have been flushed correctly
        static::assertEmpty(array_filter((new TestLockPick($tree))->nodes));
        
        // Check if the default tab gets generated again
        static::assertIsObject($tree->getDefaultNode());
        
        // Make sure the "default" object is reset correctly
        $container = $tree->makeNewNode('container', Node::TYPE_CONTAINER);
        $tree->setDefaultNode($container);
        static::assertSame($container, $tree->getDefaultNode());
        $container->remove();
        static::assertSame($tree->getNode(0), $tree->getDefaultNode());
    }
    
    public function testChildRenaming(): void
    {
        $tree = $this->getTree();
        
        $container = $tree->makeNewNode('foo', Node::TYPE_CONTAINER);
        $tree->setDefaultNode($container);
        
        $a = $tree->makeNewNode('a', Node::TYPE_FIELD);
        $b = $tree->makeNewNode('b', Node::TYPE_FIELD);
        
        static::assertEquals([
            'a' => $a,
            'b' => $b,
        ], $container->getChildren());
        
        $container->renameChild('a', 'c');
        
        static::assertEquals([
            'c' => $a,
            'b' => $b,
        ], $container->getChildren());
        
        $container->renameChild('b', 'd');
        
        static::assertEquals([
            'c' => $a,
            'd' => $b,
        ], $container->getChildren());
    }
    
    public function testSetAndGetEl(): void
    {
        $tree = $this->getTree();
        $node = $tree->makeNewNode('el', Node::TYPE_FIELD);
        $mockEl = $this->getMockForAbstractClass(AbstractField::class, [$node, $tree->getForm(), []]);
        $node->setEl($mockEl);
        static::assertSame($mockEl, $node->getEl());
    }
    
    protected function getTree(): Tree
    {
        $type = $this->getTableInstance('foo_table')->getType('foo');
        
        $type->clear();
        
        return (new TestLockPick($type))->tree;
    }
}