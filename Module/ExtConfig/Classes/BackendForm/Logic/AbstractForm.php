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
 * Last modified: 2020.05.24 at 11:35
 */

namespace LaborDigital\T3BA\ExtConfig\BackendForm\Logic;

use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode;
use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormTree;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use Neunerlei\Arrays\Arrays;

abstract class AbstractForm
{
    
    /**
     * Defines the default configuration array for a new field,
     * that is not yet known in the configuration array
     */
    public const DEFAULT_FIELD_CONFIG
        = [
            'exclude' => 1,
            'config'  => [],
        ];
    
    /**
     * The ext config context object that requires this form
     *
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    protected $context;
    
    /**
     * The tree that holds the forms' structural data
     *
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormTree
     */
    protected $tree;
    
    /**
     * The raw configuration as an array
     *
     * @var array
     */
    protected $config = [];
    
    /**
     * MUST return the name of the class that should be used as a default tab
     * if we can't find any other elements and have to create one.
     *
     * @return string
     */
    abstract protected function getTabClass(): string;
    
    /**
     * AbstractForm constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext  $context
     */
    public function __construct(ExtConfigContext $context)
    {
        $this->context = $context;
        $this->tree    = new FormTree($this, $this->getTabClass());
    }
    
    /**
     * Returns the ext config context object that requires this form
     *
     * @return \LaborDigital\T3BA\ExtConfig\ExtConfigContext
     */
    public function getContext(): ExtConfigContext
    {
        return $this->context;
    }
    
    /**
     * Checks if a child (field / container / tab) with the given id exists in the form
     *
     * @param   string|int  $id  The id to check for
     *
     * @return bool
     */
    public function hasChild($id): bool
    {
        return $this->tree->hasNode($id);
    }
    
    /**
     * Returns a single child (field / container / tab) inside the form
     *
     * @param   string|int  $id  The id of the child to retrieve
     *
     * @return AbstractFormField|AbstractFormContainer|AbstractFormTab|mixed|null
     * @throws \LaborDigital\T3BA\ExtConfig\BackendForm\BackendFormException
     */
    public function getChild($id)
    {
        $node = $this->tree->getNode($id);
        if ($node === null) {
            return $node;
        }
        
        return $node->getEl();
    }
    
    /**
     * Removes all elements from the current form, leaving you with a clean state
     */
    public function clear(): void
    {
        foreach ($this->tree->getRootNode()->getChildren() as $tab) {
            $tab->remove();
        }
    }
    
    /**
     * Return the list of all registered tab instances
     *
     * @return AbstractFormTab[]
     */
    public function getTabs(): iterable
    {
        foreach ($this->tree->getSortedNodes(FormNode::TYPE_TAB) as $tab) {
            yield $tab->getEl();
        }
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
     * Returns true if a given tab exists, false if not
     *
     * @param   int  $id  The id of the tab to check for
     *
     * @return bool
     */
    public function hasTab(int $id): bool
    {
        return $this->tree->hasNode($id, FormNode::TYPE_TAB);
    }
    
    /**
     * Returns the list of all registered fields that are currently inside the layout
     *
     * @return AbstractFormField[]
     */
    public function getFields(): iterable
    {
        foreach ($this->tree->getSortedNodes(FormNode::TYPE_FIELD) as $field) {
            yield $field->getEl();
        }
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
        return $this->tree->hasNode($id, FormNode::TYPE_FIELD);
    }
    
    /**
     * Can be used to set raw config values, that are not implemented in this facade.
     * Set either key => value pairs, or an Array of key => value pairs
     *
     * @param   array|string|int  $key    Either a key to set the given $value for, or an array of $key => $value pairs
     * @param   null              $value  The value to set for the given $key (if $key is not an array)
     *
     * @return $this
     */
    public function setRaw($key, $value = null): self
    {
        if (is_array($key)) {
            $this->config = $key;
        } else {
            $this->config[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Similar to setRaw() but will merge the given array of key/value pairs instead of
     * overwriting the original configuration.
     *
     * This method supports TYPO3's syntax of removing values from the current config if __UNSET is set as key
     *
     * @param   array  $rawInput
     *
     * @return $this
     */
    public function mergeRaw(array $rawInput): self
    {
        $this->setRaw(Arrays::merge($this->config, $rawInput, 'allowRemoval'));
        
        return $this;
    }
    
    /**
     * Returns the raw configuration array for this object
     *
     * @return array
     */
    public function getRaw(): array
    {
        return $this->config;
    }
    
    /**
     * Can be used to move any element inside the form to any other position.
     *
     * Position can be defined as "field", "container" or "0" (tabs) to move the element AFTER the defined element.
     *
     * You may also use the following modifiers:
     *    - before:field positions the element in front of the element with "field" as id
     *    - after:field positions the element after the element with "field" as id
     *    - top:container positions the element as first element of a container/tab
     *    - bottom:container positions the element as last element of a container/tab
     *
     * If top/bottom are used in combination with a field (and not a container element) it will be translated to before
     * or after respectively.
     *
     * @param   string  $id
     * @param   string  $position
     *
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     * @deprecated will be removed in v10 use getChild($id)->moveTo() instead
     */
    public function moveElement(string $id, string $position): bool
    {
        $child = $this->getChild($id);
        if ($child !== null) {
            return false;
        }
        $child->moveTo($position);
        
        return true;
    }
    
    /**
     * The same as moveElement() but receives the element instance instead of an id to move
     *
     * @param   AbstractFormElement  $el
     * @param   string               $position
     *
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     *
     * @see        moveElement() for further details.
     * @deprecated will be removed in v10 use $el->moveTo($position) instead
     */
    public function moveElementInstance(AbstractFormElement $el, string $position): bool
    {
        $el->moveTo($position);
        
        return true;
    }
    
    /**
     * Can be used to remove any given element from the list
     *
     * @param   string  $id
     *
     * @return bool
     * @deprecated will be removed in v10 use getChild($id)->remove() instead
     */
    public function removeElement(string $id): bool
    {
        $child = $this->getChild($id);
        if ($child !== null) {
            return false;
        }
        $child->remove();
        
        return true;
    }
    
    /**
     * Removes all current elements from the form, leaving you with a clean slate
     *
     * @return bool
     * @deprecated will be removed in v10 use clear()
     */
    public function removeAllElements(): bool
    {
        $this->clear();
        
        return true;
    }
}
