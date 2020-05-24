<?php
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

namespace LaborDigital\T3BA\ExtConfig\BackendForm\Tree;


use LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormElement;

class FormNode
{
    
    /**
     * A list of possible node types
     */
    public const TYPE_ROOT      = 0;
    public const TYPE_FIELD     = 1;
    public const TYPE_CONTAINER = 2;
    public const TYPE_TAB       = 4;
    
    /**
     * Different modes to insert a new child into the node
     */
    public const INSERT_MODE_AFTER  = 0;
    public const INSERT_MODE_BEFORE = 1;
    public const INSERT_MODE_TOP    = 2;
    public const INSERT_MODE_BOTTOM = 3;
    
    /**
     * Contains the ordered list of direct children
     *
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode[]
     */
    public $children = [];
    
    /**
     * The linked logic element represented by this node
     *
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormElement
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
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode
     */
    protected $parent;
    
    /**
     * The link to the containing tree instance
     *
     * @var FormTree
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
     * @param   FormTree    $tree
     */
    public function __construct($id, int $type, FormTree $tree)
    {
        $this->type = $type;
        $this->tree = $tree;
        $this->id   = $id;
    }
    
    /**
     * Returns the linked logic element represented by this node
     *
     * @return \LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormElement|mixed
     */
    public function getEl(): AbstractFormElement
    {
        return $this->el;
    }
    
    /**
     * Sets the linked logic element represented by this node
     *
     * @param   \LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractFormElement  $el
     */
    public function setEl(AbstractFormElement $el): void
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
     * Returns the unique id of this node
     *
     * @return int|string
     */
    public function getId()
    {
        // Containers have a "_" as prefix in their id to avoid
        // conflicts between fields and containers with the same name
        if ($this->isContainer()) {
            return "_" . $this->id;
        }
        
        return $this->id;
    }
    
    /**
     * Returns the parent node of the current node
     *
     * @return \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode
     */
    public function getParent(): FormNode
    {
        return $this->parent;
    }
    
    /**
     * Updates the parent node of the current node
     *
     * @param   \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode  $node
     */
    public function setParent(FormNode $node): void
    {
        $this->parent = $node;
    }
    
    /**
     * Returns the node of the closest tab node that contains this node
     *
     * @return \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode
     */
    public function getContainingTab(): FormNode
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
     * @param   FormNode       $node        The node to add
     * @param   int            $insertMode  One of FormNode::INSERT_MODE_ to
     *                                      determine where to place the
     *                                      $nodeToMove in relation to
     *                                      $pivotNode
     * @param   FormNode|null  $pivotNode   The node to use as relation for
     *                                      $insertMode. This is optional.
     */
    public function addChild(
        FormNode $node,
        int $insertMode,
        ?FormNode $pivotNode = null
    ): void {
        
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
            $keys     = array_keys($this->children);
            $values   = $this->children;
            $position = array_search($pivotNode->getId(), $keys, true);
            $position += static::INSERT_MODE_BEFORE ? 0 : 1;
            array_splice($keys, $position, 0, $node->getId());
            array_splice($values, $position, 0, [$node]);
            $this->children = array_combine($keys, $values);
        }
    }
    
    /**
     * Removes a given node from this node's children
     *
     * @param   \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode  $node
     */
    public function removeChild(FormNode $node): void
    {
        unset($this->children[$node->getId()]);
    }
    
    /**
     * Returns the lift of all children contained by this node
     *
     * @return \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode[]
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
     * @see \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormTree::parseMovePosition()
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