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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);
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
 * Last modified: 2020.05.24 at 11:27
 */

namespace LaborDigital\T3ba\Tool\Tca\Builder\Tree;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement;

class Node implements NoDiInterface
{
    
    /**
     * A list of possible node types
     */
    public const TYPE_ROOT = 0;
    public const TYPE_FIELD = 1;
    public const TYPE_CONTAINER = 2;
    public const TYPE_TAB = 4;
    public const TYPE_NL = 5;
    
    /**
     * Different modes to insert a new child into the node
     */
    public const INSERT_MODE_AFTER = 0;
    public const INSERT_MODE_BEFORE = 1;
    public const INSERT_MODE_TOP = 2;
    public const INSERT_MODE_BOTTOM = 3;
    
    /**
     * Contains the ordered list of direct children
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node[]
     */
    public $children = [];
    
    /**
     * The linked logic element represented by this node
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement
     */
    protected $el;
    
    /**
     * One of self::TYPE_ to define the type of node
     *
     * @var int
     */
    protected $type;
    
    /**
     * The parent node of the current node
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node
     */
    protected $parent;
    
    /**
     * The link to the containing tree instance
     *
     * @var Tree
     */
    protected $tree;
    
    /**
     * The unique id of this node
     *
     * @var string|int
     */
    protected $id;
    
    /**
     * FormNode constructor.
     *
     * @param   string|int  $id
     * @param   int         $type
     * @param   Tree        $tree
     */
    public function __construct($id, int $type, Tree $tree)
    {
        $this->type = $type;
        $this->tree = $tree;
        $this->id = $id;
    }
    
    /**
     * Returns the tree this node is a part of
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Tree
     */
    public function getTree(): Tree
    {
        return $this->tree;
    }
    
    /**
     * Returns the linked logic element represented by this node
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement|mixed
     */
    public function getEl(): AbstractElement
    {
        return $this->el;
    }
    
    /**
     * Sets the linked logic element represented by this node
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement  $el
     */
    public function setEl(AbstractElement $el): void
    {
        $this->el = $el;
    }
    
    /**
     * Returns one of self::TYPE_ to define the type of node
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
    
    /**
     * Returns true if the node represents a form field
     *
     * @return bool
     */
    public function isField(): bool
    {
        return $this->type === static::TYPE_FIELD;
    }
    
    /**
     * Returns true if the node represents a form container (section or pallet)
     *
     * @return bool
     */
    public function isContainer(): bool
    {
        return $this->type === static::TYPE_CONTAINER;
    }
    
    /**
     * Returns true if the node represents a tab
     *
     * @return bool
     */
    public function isTab(): bool
    {
        return $this->type === static::TYPE_TAB;
    }
    
    /**
     * Returns true if this is the tree's root node
     *
     * @return bool
     */
    public function isRoot(): bool
    {
        return $this->type === static::TYPE_ROOT;
    }
    
    /**
     * Returns true if the node represents a palette line break
     *
     * @return bool
     */
    public function isLineBreak(): bool
    {
        return $this->type === static::TYPE_NL;
    }
    
    /**
     * Returns the unique id of this node
     *
     * @return int|string
     */
    public function getId()
    {
        // Containers have a "_" as prefix in their id to avoid
        // conflicts between fields and containers with the same name
        if ($this->isContainer()) {
            return '_' . $this->id;
        }
        
        return $this->id;
    }
    
    /**
     * Returns the parent node of the current node
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node
     */
    public function getParent(): Node
    {
        return $this->parent;
    }
    
    /**
     * Updates the parent node of the current node
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node  $node
     */
    public function setParent(Node $node): void
    {
        $this->parent = $node;
    }
    
    /**
     * Returns the node of the closest tab node that contains this node
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node
     */
    public function getContainingTab(): Node
    {
        // Return me if I'm a tab
        if ($this->isTab()) {
            return $this;
        }
        
        // Return my parent if it is a tab
        if ($this->getParent()->isTab()) {
            return $this->getParent();
        }
        
        // Return my parent's parent -> this has to be a tab
        return $this->getParent()->getParent();
    }
    
    /**
     * Adds a given node as a child of this node
     *
     * @param   Node       $node            The node to add
     * @param   int        $insertMode      One of Node::INSERT_MODE_ to determine where to place the
     *                                      $nodeToMove in relation to $pivotNode
     * @param   Node|null  $pivotNode       The node to use as relation for $insertMode. This is optional.
     */
    public function addChild(
        Node $node,
        int $insertMode,
        ?Node $pivotNode = null
    ): void
    {
        // Update the node's parent
        $node->getParent()->removeChild($node);
        $node->setParent($this);
        
        // Ignore misconfiguration
        if ($pivotNode !== null && ! isset($this->children[$pivotNode->getId()])) {
            $pivotNode = null;
        }
        
        // Insert as first child
        if ($insertMode === static::INSERT_MODE_TOP
            || ($pivotNode === null && $insertMode === static::INSERT_MODE_BEFORE)) {
            $this->children = [$node->getId() => $node] + $this->children;
        } // Insert as last child
        elseif ($insertMode === static::INSERT_MODE_BOTTOM || $pivotNode === null) {
            $this->children += [$node->getId() => $node];
        } // Insert at pivot node
        else {
            $keys = array_keys($this->children);
            $values = $this->children;
            $position = array_search($pivotNode->getId(), $keys, true);
            $position += $insertMode === static::INSERT_MODE_BEFORE ? 0 : 1;
            array_splice($keys, $position, 0, $node->getId());
            array_splice($values, $position, 0, [$node]);
            $this->children = array_combine($keys, $values);
        }
    }
    
    /**
     * Removes a given node from this node's children
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node  $node
     */
    public function removeChild(Node $node): void
    {
        unset($this->children[$node->getId()]);
    }
    
    /**
     * Allows you to rename the id of a child node to something else.
     *
     * @param   string  $currentId  The id the child currently has
     * @param   string  $newId      The id the child should be given instead
     */
    public function renameChild(string $currentId, string $newId): void
    {
        $children = [];
        foreach ($this->children as $childId => $child) {
            if ($currentId === $childId) {
                $child->id = $newId;
                $children[$newId] = $child;
            } else {
                $children[$childId] = $child;
            }
        }
        $this->children = $children;
    }
    
    /**
     * Returns the lift of all children contained by this node
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }
    
    /**
     * Moves this node to a new position, defined by the position string.
     *
     * @param   string  $position  The position to move the node to
     *
     * @see \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Tree::parseMovePosition()
     */
    public function moveTo(string $position): void
    {
        [$insertMode, $pivotNode] = $this->tree->parseMovePosition($position);
        if ($pivotNode === null) {
            return;
        }
        $this->tree->moveNode($this, $insertMode, $pivotNode);
    }
    
    /**
     * Removes the current node and all it's children from the tree
     */
    public function remove(): void
    {
        $this->tree->removeNode($this);
    }
}
