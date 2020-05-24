<?php
declare(strict_types=1);

namespace LaborDigital\T3BA\ExtConfig\Tests\BackendForms;

use InvalidArgumentException;
use LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractForm;
use LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormField;
use LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormTab;
use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode;
use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormTree;
use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\NonUniqueIdException;
use PHPUnit\Framework\TestCase;

/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.05.24 at 10:31
 */
class TreeTest extends TestCase
{
    
    public function testTreeInstantiation()
    {
        $this->assertIsObject($this->getTree());
        $this->assertIsObject($this->getTree()->getDefaultNode());
        $this->assertTrue($this->getTree()->getRootNode()->isRoot());
    }
    
    public function provideTestNodeGenerationAndSimpleRetrievalData(): array
    {
        return [
            [
                "id"             => "f-a",
                "expectId"       => "f-a",
                "type"           => FormNode::TYPE_FIELD,
                "typeValidation" => "isField",
                "parentFinder"   => "getDefaultNode",
            ],
            [
                "id"             => 0,
                "expectId"       => 0,
                "type"           => FormNode::TYPE_TAB,
                "typeValidation" => "isTab",
                "parentFinder"   => "getRootNode",
            ],
            [
                "id"             => "c-a",
                "expectId"       => "_c-a",
                "type"           => FormNode::TYPE_CONTAINER,
                "typeValidation" => "isContainer",
                "parentFinder"   => "getDefaultNode",
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
    ): void {
        $tree = $this->getTree();
        $node = $tree->makeNewNode($id, $type);
        
        $this->assertEquals($expectId, $node->getId());
        $this->assertEquals($type, $node->getType());
        $this->assertTrue($node->$typeValidator());
        $this->assertSame($tree->$parentFinder(),
            $node->getParent());
        
        $this->assertSame($node, $tree->getNode($id));
    }
    
    public function testNodeGenerationAndRetrieval()
    {
        $tree = $this->getTree();
        
        $field     = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $container = $tree->makeNewNode("a", FormNode::TYPE_CONTAINER);
        // Tab with id "0" is implicitly added and therefore should exist
        $tab = $tree->makeNewNode(1, FormNode::TYPE_TAB);
        
        // Check if "has" works as expected
        $this->assertTrue($tree->hasNode(0));
        $this->assertTrue($tree->hasNode(1));
        $this->assertTrue($tree->hasNode("a"));
        $this->assertTrue($tree->hasNode("a", FormNode::TYPE_CONTAINER));
        $this->assertTrue($tree->hasNode("_a"));
        $this->assertTrue($tree->hasNode("_a", FormNode::TYPE_CONTAINER));
        
        // Check if the retrieval works as expected
        $this->assertSame($field, $tree->getNode("a"));
        $this->assertSame($container, $tree->getNode("a", FormNode::TYPE_CONTAINER));
        $this->assertSame($container, $tree->getNode("_a"));
        $this->assertSame($tab, $tree->getNode(1));
        $this->assertNotSame($tab, $tree->getNode(0));
        $this->assertEquals(FormNode::TYPE_TAB, $tree->getNode(1)->getType());
        
        // Don't retrieve the container if we explicitly ask for a field
        $this->assertNull($tree->getNode("_a", FormNode::TYPE_FIELD));
    }
    
    public function testIfDuplicateNodeIdThrowsException()
    {
        $this->expectException(NonUniqueIdException::class);
        $tree = $this->getTree();
        $tree->makeNewNode("0", FormNode::TYPE_FIELD);
        $tree->makeNewNode("0", FormNode::TYPE_FIELD);
    }
    
    public function provideTestSortedListLookupData(): array
    {
        return [
            [
                "generator"  => function (FormTree $tree): array {
                    return [
                        $tree->makeNewNode("a", FormNode::TYPE_FIELD),
                        $tree->makeNewNode("b", FormNode::TYPE_FIELD),
                        $tree->makeNewNode("c", FormNode::TYPE_FIELD),
                    ];
                    
                },
                "lookupType" => FormNode::TYPE_FIELD,
            ],
            [
                "generator"  => function (FormTree $tree): array {
                    return [
                        $tree->makeNewNode("a", FormNode::TYPE_CONTAINER),
                        $tree->makeNewNode("b", FormNode::TYPE_CONTAINER),
                        $tree->makeNewNode("c", FormNode::TYPE_CONTAINER),
                    ];
                },
                "lookupType" => FormNode::TYPE_CONTAINER,
            ],
            [
                "generator"  => function (FormTree $tree): array {
                    return [
                        $tree->makeNewNode(1, FormNode::TYPE_TAB),
                        $tree->makeNewNode(2, FormNode::TYPE_TAB),
                        $tree->makeNewNode(3, FormNode::TYPE_TAB),
                    ];
                },
                "lookupType" => FormNode::TYPE_TAB,
            ],
            // Now mix and match a bit
            [
                "generator"  => function (FormTree $tree): array {
                    $expect   = [];
                    $expect[] = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
                    $tree->makeNewNode("a", FormNode::TYPE_CONTAINER);
                    $expect[] = $tree->makeNewNode("b", FormNode::TYPE_FIELD);
                    $tree->makeNewNode(1, FormNode::TYPE_TAB);
                    $container = $tree->makeNewNode("b", FormNode::TYPE_CONTAINER);
                    $expect[]  = $containerChild = $tree->makeNewNode("c", FormNode::TYPE_FIELD);
                    $container->addChild($containerChild, FormNode::INSERT_MODE_BOTTOM);
                    
                    return $expect;
                },
                "lookupType" => FormNode::TYPE_FIELD,
            ],
            [
                "generator"  => function (FormTree $tree): array {
                    $expect = [];
                    $tree->makeNewNode("a", FormNode::TYPE_FIELD);
                    $expect[] = $tree->makeNewNode("a", FormNode::TYPE_CONTAINER);
                    $tree->makeNewNode("b", FormNode::TYPE_FIELD);
                    $tree->makeNewNode(1, FormNode::TYPE_TAB);
                    $expect[] = $tree->makeNewNode("b", FormNode::TYPE_CONTAINER);
                    $tree->makeNewNode("c", FormNode::TYPE_FIELD);
                    
                    return $expect;
                },
                "lookupType" => FormNode::TYPE_CONTAINER,
            ],
            [
                "generator"  => function (FormTree $tree): array {
                    $expect = [];
                    $tree->makeNewNode("a", FormNode::TYPE_FIELD);
                    $tree->makeNewNode("a", FormNode::TYPE_CONTAINER);
                    $tree->makeNewNode("b", FormNode::TYPE_FIELD);
                    $expect[] = $tree->getNode(0); // We have to fetch the implicitly created first tab here!
                    $expect[] = $tree->makeNewNode(1, FormNode::TYPE_TAB);
                    $tree->makeNewNode("b", FormNode::TYPE_CONTAINER);
                    $tree->makeNewNode("c", FormNode::TYPE_FIELD);
                    
                    return $expect;
                },
                "lookupType" => FormNode::TYPE_TAB,
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
        $tree         = $this->getTree();
        $expectedList = $generator($tree);
        
        foreach ($tree->getSortedNodes($lookupType) as $node) {
            $expectedNode = array_shift($expectedList);
            $this->assertSame($expectedNode, $node);
        }
    }
    
    public function testExceptionOnInvalidSortableNodeType()
    {
        $this->expectException(InvalidArgumentException::class);
        $tree = $this->getTree();
        foreach ($tree->getSortedNodes(11) as $node) {
            $node;
        }
    }
    
    public function testNesting()
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $fieldA = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $tab0   = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1   = $tree->makeNewNode(1, FormNode::TYPE_TAB);
        $fieldB = $tree->makeNewNode("b", FormNode::TYPE_FIELD);
        $fieldC = $tree->makeNewNode("c", FormNode::TYPE_FIELD);
        
        // Add fields to a container
        $container = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        $fieldD    = $tree->makeNewNode("d", FormNode::TYPE_FIELD);
        $container->addChild($fieldD, FormNode::INSERT_MODE_BOTTOM);
        // ================================= END
        
        $this->assertSame($tab0, $fieldA->getParent());
        $this->assertSame($tab0, $fieldA->getContainingTab());
        $this->assertEquals([$fieldA->getId() => $fieldA], $tab0->getChildren());
        
        $this->assertSame($tab1, $fieldB->getParent());
        $this->assertSame($tab1, $fieldB->getContainingTab());
        $this->assertSame($tab1, $fieldC->getParent());
        $this->assertSame($tab1, $fieldC->getContainingTab());
        
        $this->assertSame($tab1, $container->getParent());
        $this->assertSame($tab1, $container->getContainingTab());
        $this->assertSame($container, $fieldD->getParent());
        $this->assertSame($tab1, $fieldD->getContainingTab());
        $this->assertEquals([
            $fieldB->getId()    => $fieldB,
            $fieldC->getId()    => $fieldC,
            $container->getId() => $container,
        ], $tab1->getChildren());
    }
    
    public function testPositionParsing()
    {
        $tree       = $this->getTree();
        $fieldA     = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $containerA = $tree->makeNewNode("a", FormNode::TYPE_CONTAINER);
        $containerB = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        $tab        = $tree->makeNewNode(1, FormNode::TYPE_TAB);
        
        // Automatic insert mode based on the pivot node
        $this->assertEquals([FormNode::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition("a"));
        $this->assertEquals([FormNode::INSERT_MODE_BOTTOM, $containerA], $tree->parseMovePosition("_a"));
        $this->assertEquals([FormNode::INSERT_MODE_BOTTOM, $containerB], $tree->parseMovePosition("container"));
        $this->assertEquals([FormNode::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition("1"));
        $this->assertEquals([FormNode::INSERT_MODE_AFTER, null], $tree->parseMovePosition("foo"));
        
        // Insert mode parsing
        $this->assertEquals([FormNode::INSERT_MODE_AFTER, $fieldA], $tree->parseMovePosition("after:a"));
        $this->assertEquals([FormNode::INSERT_MODE_TOP, $fieldA], $tree->parseMovePosition("top:a"));
        $this->assertEquals([FormNode::INSERT_MODE_AFTER, $containerB], $tree->parseMovePosition("after:container"));
        $this->assertEquals([FormNode::INSERT_MODE_BEFORE, $fieldA], $tree->parseMovePosition("before:a"));
        $this->assertEquals([FormNode::INSERT_MODE_TOP, $containerB], $tree->parseMovePosition("top:container"));
        $this->assertEquals([FormNode::INSERT_MODE_BOTTOM, $tab], $tree->parseMovePosition("bottom:1"));
    }
    
    public function testMoving()
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $fieldA = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $tab0   = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1 = $tree->makeNewNode(1, FormNode::TYPE_TAB);
        
        // Add fields to a container
        $container = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        $fieldB    = $tree->makeNewNode("b", FormNode::TYPE_FIELD);
        $container->addChild($fieldB, FormNode::INSERT_MODE_BOTTOM);
        // ================================= END
        
        // Move $fieldA to the BOTTOM of container
        $fieldA->moveTo("container");
        $this->assertSame($tab1, $fieldA->getContainingTab());
        $this->assertSame($container, $fieldA->getParent());
        $this->assertSame($fieldA, $tree->getNode("a"));
        $this->assertEquals([], $tab0->getChildren());
        $this->assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Move $fieldA to the TOP of container
        $fieldA->moveTo("top:container");
        $this->assertSame($container, $fieldA->getParent());
        $this->assertEquals([
            $fieldA->getId() => $fieldA,
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        
        // Move $fieldA to the TOP of $tab1
        $fieldA->moveTo("top:1");
        $this->assertSame($tab1, $fieldA->getParent());
        $this->assertEquals([
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        $this->assertEquals([
            $fieldA->getId()    => $fieldA,
            $container->getId() => $container,
        ], $tab1->getChildren());
        
        // Move $fieldA BEFORE $fieldB using TOP
        $fieldA->moveTo("top:b");
        $this->assertSame($container, $fieldA->getParent());
        $this->assertEquals([
            $fieldA->getId() => $fieldA,
            $fieldB->getId() => $fieldB,
        ], $container->getChildren());
        
        // Move $fieldA AFTER $fieldB using BOTTOM
        $fieldA->moveTo("bottom:b");
        $this->assertSame($container, $fieldA->getParent());
        $this->assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Check if move to self is correctly ignored
        $fieldA->moveTo("top:a");
        $this->assertSame($container, $fieldA->getParent());
        $this->assertSame($tab1, $fieldA->getContainingTab());
        $this->assertEquals([
            $fieldB->getId() => $fieldB,
            $fieldA->getId() => $fieldA,
        ], $container->getChildren());
        
        // Move $tab1 BEFORE $tab0
        $tab1->moveTo("before:0");
        $this->assertSame($tree->getRootNode(), $tab1->getParent());
        $this->assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0,
        ], $tree->getRootNode()->getChildren());
        
        // Now test if we can move $tab1 INTO $tab0 -> This should not work, and
        // the tab should end up either before or after the other tab.
        $tab1->moveTo("bottom:0");
        $this->assertSame($tree->getRootNode(), $tab1->getParent());
        $this->assertEquals([
            $tab0->getId() => $tab0,
            $tab1->getId() => $tab1,
        ], $tree->getRootNode()->getChildren());
        
        $tab1->moveTo("top:0");
        $this->assertSame($tree->getRootNode(), $tab1->getParent());
        $this->assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0,
        ], $tree->getRootNode()->getChildren());
        
        // Test to move a tab into a container -> This should not work either
        $tab0->moveTo("top:container");
        $this->assertSame($tree->getRootNode(), $tab0->getParent());
        $this->assertEquals([
            $tab0->getId() => $tab0, // The command translates to "before:1"
            $tab1->getId() => $tab1,
        ], $tree->getRootNode()->getChildren());
        
        $tab0->moveTo("after:container");
        $this->assertSame($tree->getRootNode(), $tab0->getParent());
        $this->assertEquals([
            $tab1->getId() => $tab1,
            $tab0->getId() => $tab0, // The command translates to "after:1"
        ], $tree->getRootNode()->getChildren());
        
        // Test if we can move a field before / after a tab
        // This should move the element to the top/bottom of the respective tab instead.
        $fieldA->moveTo("before:0");
        $this->assertSame($tab0, $fieldA->getParent());
        $this->assertEquals([
            $fieldA->getId() => $fieldA,  // The command translates to "top:0"
        ], $tab0->getChildren());
        
        $fieldA->moveTo("after:1");
        $this->assertSame($tab1, $fieldA->getParent());
        $this->assertEquals([
            $container->getId() => $container,
            $fieldA->getId()    => $fieldA,
        ], $tab1->getChildren());
        
        // Test to move a container between tabs
        $fieldA->moveTo("container");
        $this->assertEquals([$container->getId() => $container], $tab1->getChildren());
        $container->moveTo("0");
        $this->assertSame($tab0, $container->getParent());
        $this->assertSame($tab0, $container->getContainingTab());
        $this->assertSame($tab0, $fieldA->getContainingTab());
        $this->assertSame($tab0, $fieldB->getContainingTab());
        $this->assertEquals([], $tab1->getChildren());
        $this->assertEquals([$container->getId() => $container], $tab0->getChildren());
        
        // Test if we can move a container before / after a tab
        // Similar to a field, this should move the element to the top/bottom of the respective tab instead.
        $container->moveTo("before:0");
        $this->assertSame($tab0, $container->getParent());
        $this->assertEquals([$container->getId() => $container], $tab0->getChildren());
        
        $container->moveTo("after:1");
        $this->assertSame($tab1, $container->getParent());
        $this->assertEquals([$container->getId() => $container], $tab1->getChildren());
        
        // Test if we ignore a move to a non-existent element
        $container->moveTo("after:5");
        $this->assertSame($tab1, $container->getParent());
        $this->assertEquals([$container->getId() => $container], $tab1->getChildren());
    }
    
    public function testDefaultNodeSetting()
    {
        $tree = $this->getTree();
        
        $container = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        
        $tree->setDefaultNode($container);
        $this->assertSame($container, $tree->getDefaultNode());
        
        $field = $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $this->assertSame($container, $field->getParent());
        
        $field = $tree->makeNewNode("b", FormNode::TYPE_FIELD);
        $this->assertSame($container, $field->getParent());
        
        $tree->setDefaultNode(null);
        $this->assertSame($tree->getNode(0), $tree->getDefaultNode());
        
        $field = $tree->makeNewNode("c", FormNode::TYPE_FIELD);
        $this->assertSame($tree->getNode(0), $field->getParent());
        
        // Make sure fields cannot be set as default nodes
        $tree->setDefaultNode($field);
        $this->assertSame($tree->getNode(0), $field->getParent());
    }
    
    public function testNodeRemoval()
    {
        $tree = $this->getTree();
        
        // Generate the tree
        // ================================= START
        $tree->makeNewNode("a", FormNode::TYPE_FIELD);
        $tab0 = $tree->getNode(0);
        
        // A new tab is generated -> This means all new fields will implicitly be added to the new tab
        $tab1   = $tree->makeNewNode(1, FormNode::TYPE_TAB);
        $fieldB = $tree->makeNewNode("b", FormNode::TYPE_FIELD);
        $fieldC = $tree->makeNewNode("c", FormNode::TYPE_FIELD);
        
        // Add fields to a container
        $container = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        $fieldD    = $tree->makeNewNode("d", FormNode::TYPE_FIELD);
        $container->addChild($fieldD, FormNode::INSERT_MODE_BOTTOM);
        // ================================= END
        
        // Remove a field from a container
        $fieldD->remove();
        $this->assertEquals([], $container->getChildren());
        $this->assertNull($tree->getNode("d", FormNode::TYPE_FIELD));
        
        // Remove a whole container
        $fieldB->moveTo("container");
        $container->remove();
        $this->assertEquals([], $container->getChildren());
        $this->assertNull($tree->getNode("b", FormNode::TYPE_FIELD));
        $this->assertNull($tree->getNode("container", FormNode::TYPE_CONTAINER));
        $this->assertEquals([$fieldC->getId() => $fieldC], $tab1->getChildren());
        
        // Remove all tabs
        $tab1->remove();
        $tab0->remove();
        $this->assertEquals([], $tree->getRootNode()->getChildren());
        
        // Look inside the tree to check if the nodes have been flushed correctly
        $ref     = new \ReflectionObject($tree);
        $propRef = $ref->getProperty("nodes");
        $propRef->setAccessible(true);
        $nodes = $propRef->getValue($tree);
        $this->assertEmpty(array_filter($nodes));
        
        // Check if the default tab gets generated again
        $this->assertIsObject($tree->getDefaultNode());
        
        // Make sure the "default" object is reset correctly
        $container = $tree->makeNewNode("container", FormNode::TYPE_CONTAINER);
        $tree->setDefaultNode($container);
        $this->assertSame($container, $tree->getDefaultNode());
        $container->remove();
        $this->assertSame($tree->getNode(0), $tree->getDefaultNode());
    }
    
    public function testSetAndGetEl()
    {
        $tree   = $this->getTree();
        $mockEl = $this->getMockForAbstractClass(AbstractFormField::class);
        $node   = $tree->makeNewNode("el", FormNode::TYPE_FIELD);
        $node->setEl($mockEl);
        $this->assertSame($mockEl, $node->getEl());
    }
    
    protected function getTree(): FormTree
    {
        /** @var mixed $form */
        $form     = $this->getMockForAbstractClass(AbstractForm::class);
        $tabClass = get_class($this->getMockForAbstractClass(AbstractFormTab::class));
        
        return new FormTree($form, $tabClass);
    }
}