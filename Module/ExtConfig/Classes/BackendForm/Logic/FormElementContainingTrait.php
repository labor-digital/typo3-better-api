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

namespace LaborDigital\T3BA\ExtConfig\BackendForm\Logic;

use LaborDigital\T3BA\ExtConfig\BackendForm\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer;
use LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement;

trait FormElementContainingTrait
{
    
    /**
     * Returns the fields/containers inside this container/tab
     *
     * @return AbstractFormField[]|AbstractFormContainer[]|mixed[]
     */
    public function getChildren(): iterable
    {
        /** @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode $node */
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
     * @return AbstractFormField|AbstractFormContainer|mixed
     * @throws \LaborDigital\T3BA\ExtConfig\BackendForm\BackendFormException
     */
    public function getChild(string $id)
    {
        /** @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode $node */
        $node     = $this->node;
        $children = $node->getChildren();
        $child    = $children[$id] ?? $children['_' . $id] ?? null;
        if (is_null($child)) {
            throw new BackendFormException(
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
        } catch (BackendFormException $e) {
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
    public function removeChild(string $id): self
    {
        try {
            $this->getChild($id)->remove();
        } catch (BackendFormException $e) {
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
        /** @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode $node */
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
    
    /**
     * Returns a single element (field / container) inside this container
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     *
     * @return AbstractFormElement|AbstractFormContainer|mixed
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     * @deprecated Will be removed in v10 - Use getChild() instead!
     */
    public function getElement(string $id)
    {
        return $this->getChild($id);
    }
    
    /**
     * Checks if this element has a specific element inside of itself
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     *
     * @return bool
     * @deprecated Will be removed in v10 - Use hasChild() instead!
     */
    public function hasElement(string $id): bool
    {
        return $this->hasChild($id);
    }
    
    /**
     * Removes an element with the given id from this container
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     *
     * @deprecated Will be removed in v10 - Use removeChild() instead!
     */
    public function removeElement(string $id): void
    {
        $this->removeChild($id);
    }
}
