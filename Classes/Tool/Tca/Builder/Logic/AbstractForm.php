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
 * Last modified: 2021.02.09 at 21:19
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Logic;


use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Tree;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

abstract class AbstractForm
{

    /**
     * @var TcaBuilderContext
     */
    protected $context;

    /**
     * The tree that holds the forms' structural data
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Tree
     */
    protected $tree;

    /**
     * MUST return the name of the class that should be used as a default tab
     * if we can't find any other elements and have to create one.
     *
     * @return string
     */
    abstract protected function getTabClass(): string;

    /**
     * Returns the instance of the parent form / parent table
     *
     * @return AbstractTypeList|TcaTable|TcaField
     */
    abstract public function getParent();

    /**
     * Alias of getParent()
     *
     * @return AbstractTypeList|TcaTable
     */
    abstract public function getRoot();

    /**
     * AbstractTreeHolder constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext  $context
     */
    public function __construct(TcaBuilderContext $context)
    {
        $this->context = $context;
        $this->tree    = $context->cs()->di->getWithoutDi(
            Tree::class, [$this, $this->getTabClass()]
        );
    }

    /**
     * Returns the context object
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext
     */
    public function getContext(): TcaBuilderContext
    {
        return $this->context;
    }

    /**
     * Checks if a child (field / container / tab) with the given id exists in the form
     *
     * @param   string|int  $id    The id to check for
     * @param   int|null    $type  Optionally one of FormNode::TYPE_ to
     *                             narrow down the list of retrievable node
     *                             types.
     *
     * @return bool
     */
    public function hasChild($id, ?int $type = null): bool
    {
        return $this->tree->hasNode($id, $type);
    }

    /**
     * Returns a single child (field / container / tab) inside the form
     *
     * @param   string|int  $id    The id of the child to retrieve
     * @param   int|null    $type  Optionally one of FormNode::TYPE_ to
     *                             narrow down the list of retrievable node
     *                             types.
     *
     * @return AbstractField|AbstractContainer|AbstractTab|null
     */
    public function getChild($id, ?int $type = null)
    {
        $node = $this->tree->getNode($id, $type);
        if ($node === null) {
            return null;
        }

        return $node->getEl();
    }

    /**
     * Returns a list of all elements in the sorted order
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractElement|null[]|iterable
     */
    public function getAllChildren(): iterable
    {
        foreach ($this->tree->getRootNode()->getChildren() as $tab) {
            yield $tab->getEl();

            foreach ($tab->getChildren() as $child) {
                yield $child->getEl();

                if ($child->isContainer()) {
                    foreach ($child->getChildren() as $_child) {
                        yield $_child->getEl();
                    }

                    // Mark the end of a container with a "NULL" value
                    yield null;
                }
            }
        }
    }

    /**
     * Removes all elements from the current form, leaving you with a clean state
     */
    public function clear(): void
    {
        $this->removeAllChildren();
    }

    /**
     * Removes all child objects in this form.
     */
    public function removeAllChildren(): void
    {
        foreach ($this->tree->getRootNode()->getChildren() as $tab) {
            $tab->remove();
        }
    }

    /**
     * Similar to "getTab()" but always returns a new tab with a new id added to it
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTab|mixed
     * @throws \LaborDigital\T3BA\Core\Exception\NotImplementedException
     */
    public function getNewTab()
    {
        if (! method_exists($this, 'getTab')) {
            throw new NotImplementedException('The getNewTab() method is not supported on this object!');
        }

        $highestTabId = 0;
        foreach ($this->getTabs() as $tab) {
            if ($tab->getId() > $highestTabId) {
                $highestTabId = ((int)$tab->getId());
            }
        }

        return $this->getTab($highestTabId + 1);
    }

    /**
     * Return the list of all registered tab instances
     *
     * @return AbstractTab[]
     */
    public function getTabs(): iterable
    {
        return $this->findAllChildrenByType(Node::TYPE_TAB);
    }

    /**
     * Similar to getTabs() but returns only the tab keys instead of the whole object
     *
     * @return int[]
     */
    public function getTabKeys(): iterable
    {
        foreach ($this->getTabs() as $tab) {
            yield $tab->getId();
        }
    }

    /**
     * Returns the list of all registered fields that are currently inside the layout
     *
     * @return AbstractField[]
     */
    public function getFields(): iterable
    {
        return $this->findAllChildrenByType(Node::TYPE_FIELD);
    }

    /**
     * Similar to getFields() but only returns the keys of the fields instead of the whole object
     *
     * @return array
     */
    public function getFieldKeys(): iterable
    {
        foreach ($this->getFields() as $tab) {
            yield $tab->getId();
        }
    }

    /**
     * Returns true if a field with the given id is registered in this form
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function hasField(string $id): bool
    {
        return $this->tree->hasNode($id, Node::TYPE_FIELD);
    }

    /**
     * Internal helper to retrieve/create child elements easier
     *
     * @param   string|int  $id       The id of the child to retrieve
     * @param   int         $type     One of Node::TYPE_
     * @param   \Closure    $factory  The factory to create the new element instance to return
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractElement|mixed
     */
    protected function findOrCreateChild($id, int $type, \Closure $factory): AbstractElement
    {
        $node = $this->tree->getNode($id, $type);

        if (! $node) {
            $node = $this->tree->makeNewNode($id, $type);
            $node->setEl($factory($node));
        }

        return $node->getEl();
    }

    /**
     * Internal helper to resolve the ordered list of all children with the given type
     *
     * @param   int  $type
     *
     * @return iterable
     */
    protected function findAllChildrenByType(int $type): iterable
    {
        foreach ($this->tree->getSortedNodes($type) as $node) {
            yield $node->getEl();
        }
    }
}
