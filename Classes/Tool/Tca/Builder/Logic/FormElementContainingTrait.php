<?php
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
 * Last modified: 2020.05.26 at 00:20
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\Tca\Builder\Logic;

use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;

trait FormElementContainingTrait
{

    /**
     * Returns the fields/containers inside this container/tab
     *
     * @return AbstractField[]|AbstractContainer[]|mixed[]
     */
    public function getChildren(): iterable
    {
        /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node $node */
        $node = $this->node;
        foreach ($node->getChildren() as $child) {
            yield $child->getEl();
        }
    }

    /**
     * Returns a single child (field / container) inside the container
     *
     * Note: This looks ONLY inside the current container, not in the whole form!
     *
     * @param   string  $id  The id of the child to retrieve
     *
     * @return AbstractField|AbstractContainer|mixed
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException
     */
    public function getChild(string $id)
    {
        /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node $node */
        $node     = $this->node;
        $children = $node->getChildren();
        $child    = $children[$id] ?? $children['_' . $id] ?? null;
        if (is_null($child)) {
            throw new TcaBuilderException(
                'Could not find the element with ID: ' . $id . ', inside the element with id: ' . $this->getId()
            );
        }

        return $child;
    }

    /**
     * Checks if this child has a specific, other child inside of itself
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id  The id of the child to test for
     *
     * @return bool
     */
    public function hasChild(string $id): bool
    {
        try {
            $this->getChild($id);
        } catch (TcaBuilderException $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes a child with the given id from this container
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     *
     * @return $this
     */
    public function removeChild(string $id)
    {
        try {
            $this->getChild($id)->remove();
        } catch (TcaBuilderException $e) {
        }

        return $this;
    }

    /**
     * Can be used to group multiple elements inside this container.
     * This is quite useful as you can avoid using moveTo()... over and over again..
     *
     * @param   callable  $definition
     *
     * @return $this
     */
    public function addMultiple(callable $definition)
    {
        /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node $node */
        $node = $this->node;
        $tree = $node->getTree();

        // Store the current default node
        $defaultNodeBackup = $tree->hasConfiguredDefaultNode() ? $tree->getDefaultNode() : null;

        // Run the definition with this node as default node
        $tree->setDefaultNode($node);
        $definition($this);

        // Restore the default node
        $tree->setDefaultNode($defaultNodeBackup);

        // Done
        return $this;
    }
}
