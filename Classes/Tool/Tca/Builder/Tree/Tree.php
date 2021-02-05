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

namespace LaborDigital\T3BA\Tool\Tca\Builder\Tree;

use InvalidArgumentException;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTab;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType;

class Tree
{

    /**
     * The form that is linked with this tree
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType
     */
    protected $type;

    /**
     * A list of form nodes by their type and their id for direct lookup
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node[][]
     */
    protected $nodes = [];

    /**
     * The root node that stores the sorted tabs
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node
     */
    protected $root;

    /**
     * The name of the form element to create if there is no tab in the
     * root node. The class MUST extend the AbstractFormTab class!
     *
     * @var string
     * @see \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTab
     */
    protected $tabClass;

    /**
     * The node in which all new nodes should be automatically added to.
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node|null
     */
    protected $defaultNode;

    /**
     * FormTree constructor.
     *
     * @param   AbstractType  $type
     * @param   string        $tabClass
     */
    public function __construct(AbstractType $type, string $tabClass)
    {
        $this->type     = $type;
        $this->tabClass = $tabClass;
        $this->root     = $type->getContext()->cs()
            ->di->getWithoutDi(
                Node::class,
                ['root', Node::TYPE_ROOT, $this]
            );
    }

    /**
     * Returns the linked form instance
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType
     */
    public function getType(): AbstractType
    {
        return $this->type;
    }

    /**
     * Factory to create a new, empty node
     *
     * @param   string|int  $id  The unique id of the node to create.
     *                           If the node already exist
     * @param   int         $type
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\Tree\NonUniqueIdException
     */
    public function makeNewNode($id, int $type): Node
    {
        $node = $this->type->getContext()->cs()
            ->di->getWithoutDi(Node::class, [$id, $type, $this]);

        $parent = $type === Node::TYPE_TAB ? $this->root : $this->getDefaultNode();

        $node->setParent($parent);
        $parent->addChild($node, Node::INSERT_MODE_AFTER);

        if (isset($this->nodes[$type][$node->getId()])) {
            throw new NonUniqueIdException(
                'You can\'t create a new node with ' . $id . ', and ' . $type
                . ', because it already exists!');
        }

        $this->nodes[$type][$node->getId()] = $node;

        return $node;
    }

    /**
     * Returns true if a node with a given id exist
     *
     * @param   string|int  $id    The id of the node to check for
     * @param   int|null    $type  Optionally one of FormNode::TYPE_ to
     *                             narrow down the list of retrievable node
     *                             types.
     *
     * @return bool
     */
    public function hasNode($id, ?int $type = null): bool
    {
        return $this->getNode($id, $type) !== null;
    }

    /**
     * Returns either the instance of a node with the given id or null,
     * if it does not exist.
     *
     * @param   string|int  $id    The id of the node to retrieve
     * @param   int|null    $type  Optionally one of FormNode::TYPE_ to
     *                             narrow down the list of retrievable node
     *                             types.
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node|null
     */
    public function getNode($id, ?int $type = null): ?Node
    {
        // Check if we got a specific request
        if ($type !== null) {
            // Special handling for container ids
            if ($type === Node::TYPE_CONTAINER) {
                return $this->nodes[$type][$id] ?? $this->nodes[$type]['_' . $id] ?? null;
            }

            // Normal lookup
            return $this->nodes[$type][$id] ?? null;
        }

        // Check if we need to retrieve a field
        if ($type !== Node::TYPE_CONTAINER && isset($this->nodes[Node::TYPE_FIELD][$id])) {
            return $this->nodes[Node::TYPE_FIELD][$id];
        }

        // Check if we need to retrieve a line break
        if ($type !== Node::TYPE_CONTAINER && isset($this->nodes[Node::TYPE_NL][$id])) {
            return $this->nodes[Node::TYPE_NL][$id];
        }

        // Numeric values -> this has to be a tab
        if (is_numeric($id)) {
            return $this->nodes[Node::TYPE_TAB][(int)$id] ?? null;
        }

        // Return either a container, or a tab
        return $this->nodes[Node::TYPE_CONTAINER][$id] ??
               $this->nodes[Node::TYPE_CONTAINER]['_' . $id] ??
               null;
    }

    /**
     * Returns the list of nodes based on their currently
     * configured order in the form.
     *
     * @param   int  $type  One of FormNode::TYPE_ to select the type
     *                      of elements to retrieve
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node[]|iterable
     */
    public function getSortedNodes(int $type): iterable
    {
        switch ($type) {
            case Node::TYPE_TAB:
                foreach ($this->root->getChildren() as $tabNode) {
                    yield $tabNode;
                }
                break;
            case Node::TYPE_CONTAINER:
                foreach ($this->root->getChildren() as $tab) {
                    foreach ($tab->getChildren() as $child) {
                        if (! $child->isContainer()) {
                            continue;
                        }
                        yield $child;
                    }
                }
                break;
            case Node::TYPE_FIELD:
                foreach ($this->root->getChildren() as $tab) {
                    foreach ($tab->getChildren() as $child) {
                        if (! $child->isContainer()) {
                            yield $child;
                            continue;
                        }
                        foreach ($child->getChildren() as $field) {
                            yield $field;
                        }
                    }
                }
                break;
            default:
                throw new InvalidArgumentException('The given type is not supported!');
        }
    }

    /**
     * Helper to parse a "position" string into both the insert mode and the
     * instance of the pivot id.
     *
     * A position string can look like the following:
     * - "id": positions the element after the "id" elements
     *  - "before:id" positions the element in front of the "id" element
     *  - "after:id" positions the element after the "id" element
     *  - "top:containerId" positions the element as first element of a container/tab
     *  - "bottom:containerId" positions the element as last element of a container/tab
     *
     * If top/bottom are used in combination with a field (and not a container
     * element) it will be translated to before or after respectively.
     *
     * If the "id" could not be resolved into a node the second entry in the
     * result array will be null.
     *
     * @param   string  $position  The position to parse
     *
     * @return array A numeric array where the first entry is the
     *               selected insert mode, the second entry is the pivot
     *               node or null if it could not be found
     */
    public function parseMovePosition(string $position): array
    {
        // Parse position into node
        $positionParts = explode(':', $position);
        $pivotId       = $positionParts[1] ?? $positionParts[0];
        $pivotNode     = $this->getNode($pivotId);

        // Build the insert mode
        $defaultInsertMode = $pivotNode !== null && ! $pivotNode->isField() ? 'bottom' : 'after';
        $insertMode        = isset($positionParts[1]) ? $positionParts[0] : $defaultInsertMode;
        if ($insertMode === 'bottom') {
            $insertMode = Node::INSERT_MODE_BOTTOM;
        } elseif ($insertMode === 'top') {
            $insertMode = Node::INSERT_MODE_TOP;
        } elseif ($insertMode === 'before') {
            $insertMode = Node::INSERT_MODE_BEFORE;
        } elseif ($insertMode === 'after') {
            $insertMode = Node::INSERT_MODE_AFTER;
        }

        // Done
        return [$insertMode, $pivotNode];
    }

    /**
     * Moves a given node to a new position, relative to the
     * given pivot node.
     *
     * @param   Node  $nodeToMove      The node to move somewhere
     * @param   int   $insertMode      One of FormNode::INSERT_MODE_ to determine where to place the $nodeToMove
     *                                 in relation to $pivotNode
     * @param   Node  $pivotNode       The node to use as relation
     */
    public function moveNode(
        Node $nodeToMove,
        int $insertMode,
        Node $pivotNode
    ): void {
        // Ignore if the node to move is the pivot node -> this is wrong
        if ($nodeToMove === $pivotNode) {
            return;
        }

        // True if only "before and after" are allowed
        $allowOnlyBeforeAndAfter = false;

        // Move Tabs only in the root node
        if ($nodeToMove->isTab()) {
            $nodeToAddNodeTo         = $this->getRootNode();
            $pivotNode               = $pivotNode->getContainingTab();
            $allowOnlyBeforeAndAfter = true;
            if ($insertMode === Node::INSERT_MODE_TOP) {
                $insertMode = Node::INSERT_MODE_BEFORE;
            } elseif ($insertMode === Node::INSERT_MODE_BOTTOM) {
                $insertMode = Node::INSERT_MODE_AFTER;
            }
        } // Only add containers to tabs
        elseif ($nodeToMove->isContainer()) {
            $nodeToAddNodeTo = $pivotNode->getContainingTab();
        } elseif ($nodeToMove->isField()) {
            // Move field to other field
            if ($pivotNode->isField()) {
                $nodeToAddNodeTo         = $pivotNode->getParent();
                $allowOnlyBeforeAndAfter = true;
            } elseif ($insertMode === Node::INSERT_MODE_TOP || $insertMode === Node::INSERT_MODE_BOTTOM
                      || $pivotNode->isTab()) {
                $nodeToAddNodeTo = $pivotNode;
            } else {
                $nodeToAddNodeTo = $pivotNode->getParent();
            }
        } else {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        // Simplify the insert mode
        if ($allowOnlyBeforeAndAfter) {
            if ($insertMode === Node::INSERT_MODE_TOP) {
                $insertMode = Node::INSERT_MODE_BEFORE;
            } elseif ($insertMode === Node::INSERT_MODE_BOTTOM) {
                $insertMode = Node::INSERT_MODE_AFTER;
            }
        }

        // Add the node as a child
        $nodeToAddNodeTo->addChild($nodeToMove, $insertMode, $pivotNode);
    }

    /**
     * Completely removes a node from the tree
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node  $node
     */
    public function removeNode(Node $node): void
    {
        // Remove all child nodes of the given node
        foreach ($node->getChildren() as $child) {
            $this->removeNode($child);
        }

        // Remove the node from its parent
        $node->getParent()->removeChild($node);

        // Unlink the default node if it is the given node
        if ($node === $this->defaultNode) {
            unset($this->defaultNode);
        }

        // Remove the node from the types' list
        unset($this->nodes[$node->getType()][$node->getId()]);
    }

    /**
     * Returns true if there is a default node configured, false if not
     *
     * @return bool
     */
    public function hasConfiguredDefaultNode(): bool
    {
        return $this->defaultNode !== null;
    }

    /**
     * Sets the node to which all new nodes will be automatically added
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node|null  $node
     */
    public function setDefaultNode(?Node $node): void
    {
        // Ignore if a field is given
        if ($node !== null && $node->isField()) {
            return;
        }
        $this->defaultNode = $node;
    }

    /**
     * Returns the node that is currently configured as "default".
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node
     */
    public function getDefaultNode(): Node
    {
        // Check if we currently have a default node
        if (isset($this->defaultNode)) {
            return $this->defaultNode;
        }

        // Make sure we have at least a single tab
        if (empty($this->nodes[Node::TYPE_TAB])) {
            $node = $this->makeNewNode(0, Node::TYPE_TAB);
            $tab  = $this->type->getContext()->cs()
                ->di->getWithoutDi($this->tabClass, [$node, $this->type]);

            if ($tab instanceof AbstractTab) {
                $tab->setLabel('betterApi.tab.general');
            }

            $node->setEl($tab);
        }

        // Return the last possible tab
        return end($this->nodes[Node::TYPE_TAB]);
    }

    /**
     * Returns the tree's root node
     *
     * @return Node
     */
    public function getRootNode(): Node
    {
        return $this->root;
    }
}
