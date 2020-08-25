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
 * Last modified: 2020.03.19 at 02:51
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Abstracts;

use Exception;
use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTableType;
use Neunerlei\Arrays\Arrays;

abstract class AbstractFormContainer extends AbstractFormElement
{
    protected const TYPE_TAB       = 0;
    protected const TYPE_CONTAINER = 1;
    protected const TYPE_ELEMENT   = 2;
    protected const TYPE_ALL       = 3;
    
    /**
     * The list of elements in this container
     *
     * @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement[]
     */
    protected $elements = [];
    
    /**
     * Holds the default insert position of new elements.
     * This is used in the addMultiple() method to scope the given elements into their parent
     *
     * @var string
     */
    protected $defaultPosition = '';
    
    /**
     * Returns the fields inside this container
     *
     * @return array
     */
    public function getChildren(): array
    {
        return $this->elements;
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
     */
    public function getElement(string $id)
    {
        if (isset($this->elements[$id])) {
            return $this->elements[$id];
        }
        if (isset($this->elements['_' . $id])) {
            return $this->elements['_' . $id];
        }
        throw new BackendFormException("Could not find the element with ID: $id, inside the element with id: $this->id");
    }
    
    /**
     * Checks if this element has a specific element inside of itself
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function hasElement(string $id): bool
    {
        try {
            $this->getElement($id);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Removes an element with the given id from this container
     *
     * Note: This looks only in the current container, not in the whole form!
     *
     * @param   string  $id
     */
    public function removeElement(string $id)
    {
        if (isset($this->elements[$id])) {
            unset($this->elements[$id]);
            
            return;
        }
        if (isset($this->elements['_' . $id])) {
            unset($this->elements['_' . $id]);
            
            return;
        }
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
        // Ignore if this is called on a type
        if ($this instanceof TcaTableType) {
            return call_user_func($definition, $this);
        }
        
        // Default handling
        $defaultPositionBackup     = $this->defaultPosition;
        $this->defaultPosition     = 'bottom:' . ($this->elIsContainer($this) ? '_' : '') . $this->getId();
        $form                      = $this->getForm();
        $formDefaultPositionBackup = $form->defaultPosition;
        $form->defaultPosition     = $this->defaultPosition;
        call_user_func($definition, $this);
        $this->defaultPosition = $defaultPositionBackup;
        $form->defaultPosition = $formDefaultPositionBackup;
        
        return $this;
    }
    
    /**
     * Returns the instance of an element, or creates a new one using the registered generator's result
     *
     * @param   string    $id         The id of the element to look up if possible
     * @param   int       $type       One of the TYPE_... constants to specify which type of element this is
     * @param   callable  $generator  The generator function which is used to generate new instances of this element
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement|mixed
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function getOrCreateElement(string $id, int $type, callable $generator)
    {
        try {
            return $this->getElementInternal($id, $type);
        } catch (BackendFormException $exception) {
            $i = call_user_func($generator);
            if (! $i instanceof AbstractFormElement) {
                throw new BackendFormException("Error while generating element: $id! The result of the generator should be a child of AbstractFormElement!");
            }
            
            return $this->addElement($i, '');
        }
    }
    
    /**
     * Adds a new element to the structure of elements
     *
     * @param   AbstractFormElement  $el        The element to add to the structure
     * @param   string               $position  The position, where the element should be added. See moveElement() for
     *                                          details
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement|mixed
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function addElement(AbstractFormElement $el, string $position)
    {
        $elId = ($this->elIsContainer($el) && $el->getId()[0] !== '_' ? '_' : '') . $el->getId();
        if (empty($position)) {
            $position = $this->defaultPosition;
        }
        
        // Make sure we add non tabs only to a tab
        if ($this instanceof AbstractForm && ! $this->elIsTab($el)) {
            // Find the last tab and add it there
            $lastTab = end($this->getForm()->getChildren());
            if (! $lastTab instanceof AbstractFormTab) {
                throw new BackendFormException('Could not add element to form, because there are no tabs to add this element to!');
            }
            
            return $lastTab->addElement($el, $position);
        }
        
        // Update the element
        $el->__setParent($this);
        $el->__setForm($this->getForm());
        $this->elements[$elId] = $el;
        
        // Move it to the correct position
        $this->getForm()->moveElement($el->getId(), $position);
        
        // Done
        return $el;
    }
    
    /**
     * Internal helper to add an element at a certain position.
     *
     * @param   string               $id
     * @param   AbstractFormElement  $el
     * @param   string               $modifier
     * @param   string               $targetId
     */
    protected function addElementAt(string $id, AbstractFormElement $el, string $modifier, string $targetId = '')
    {
        switch ($modifier) {
            case 'top':
                $this->elements = Arrays::attach([$id => $el], $this->elements);
                break;
            case 'bottom':
                $this->elements[$id] = $el;
                break;
            case 'before':
            case 'after':
                $this->elements = Arrays::insertAt($this->elements, $targetId, $id, $el, $modifier === 'before');
                break;
        }
    }
    
    /**
     * Tries to resolve an element with the given id inside this container.
     *
     * It will first try to find a field with the id. If it could not find a field, it will
     * try to find a container that can possibly have the same id. This is sadly a problem with the
     * underlying logic that allows palettes and fields to have the same ids...
     *
     * @param   string      $id     The id of the field / container to find
     * @param   int         $type   Determines the type of element to look up. Use the TYPE_* constants to select a
     *                              specific type If no type is specified first elements with the id are looked up,
     *                              then containers and as last tabs...
     * @param   array|null  $finds  If given, it holds an array of all found elements, sorted by type
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement|mixed
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    protected function getElementInternal(string $id, int $type = self::TYPE_ALL, array &$finds = null)
    {
        // Prepare result lists
        $elements = $tabs = $containers = [];
        
        // Get the form and run the query on it
        $form = $this->getForm();
        
        // Switch when the id starts with an underscore
        if (isset($id[0]) && $id[0] === '_') {
            $id   = substr($id, 1);
            $type = self::TYPE_CONTAINER;
        }
        
        // We traverse the hierarchy like this to always make sure to FIRST return a field with an id
        // and only if we don't have a field, we then check for containers and lastly for tabs.
        // This is because a container and a field MAY have the same key ...
        foreach ($form->getChildren() as $k => $tab) {
            /** @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormTab $tab */
            // Ignore temporarily added elements
            if (! $this->elIsTab($tab)) {
                continue;
            }
            
            // Skip if we only look for tabs
            if ($type !== self::TYPE_TAB) {
                foreach ($tab->getChildren() as $_k => $child) {
                    if ($child instanceof AbstractFormContainer) {
                        // Skip if we only look for containers
                        if ($type !== self::TYPE_CONTAINER) {
                            foreach ($child->getChildren() as $__k => $element) {
                                if ($__k === $id) {
                                    return $element;
                                }
                                $elements[$__k] = $element;
                            }
                        }
                        $containers[substr($_k, 1)] = $child;
                    } else {
                        // Skip if we only look for containers
                        if ($type !== self::TYPE_CONTAINER) {
                            if ($_k === $id) {
                                return $child;
                            }
                            $elements[$_k] = $child;
                        }
                    }
                }
            }
            $tabs[$k] = $tab;
        }
        
        // Update type reference
        if (is_array($finds)) {
            $finds['containers'] = $containers;
            $finds['tabs']       = $tabs;
            $finds['elements']   = $elements;
        }
        
        // Resolve the id to an element
        if (isset($elements[$id])) {
            return $elements[$id];
        }
        if (($type === static::TYPE_CONTAINER || $type === static::TYPE_ALL) && isset($containers[$id])) {
            return $containers[$id];
        }
        if (($type === static::TYPE_TAB || $type === static::TYPE_ALL) && isset($tabs[$id])) {
            return $tabs[$id];
        }
        
        // Still not found
        throw new BackendFormException('Could not find the element with ID: ' . $id);
    }
    
    /**
     * Returns true if an element with the given $id was found inside this container
     *
     * @param   string  $id    The id too find
     * @param   int     $type  Determines the type of element to look up. Use the TYPE_* constants to select a specific
     *                         type
     *                         If no type is specified first elements with the id are looked up, then containers and as
     *                         last tabs...
     *
     * @return bool
     */
    protected function hasElementInternal(string $id, int $type = self::TYPE_ALL): bool
    {
        try {
            // Try to find the element
            $this->getElementInternal($id, $type);
            
            return true;
        } catch (BackendFormException $e) {
            return false;
        }
    }
    
    /**
     * Can be used to remove any given element from the list
     *
     * @param   string  $id
     *
     * @param   int     $type
     *
     * @return bool
     */
    protected function removeElementInternal(string $id, int $type = self::TYPE_ALL): bool
    {
        try {
            // Try to find local elements first, or make a global lookup
            if (isset($this->elements[$id])) {
                $el = $this->elements[$id];
            } elseif (isset($this->elements['_' . $id])) {
                $el = $this->elements['_' . $id];
            } else {
                $el = $this->getElementInternal($id, $type);
            }
            
            // Allow filtering
            $this->__elRemovalHook($el);
            
            // Remove a container
            if ($this->elIsContainer($el) || $this->elIsTab($el)) {
                // Remove all child elements of a container
                /** @var \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormContainer $el */
                foreach ($el->getChildren() as $child) {
                    $el->removeElement($child->getId());
                }
            }
            
            // Remove the element itself
            $elId   = ($this->elIsContainer($el) ? '_' : '') . $el->getId();
            $parent = $el->getParent();
            unset($parent->elements[$elId]);
            $el->parent = null;
            $el->form   = null;
            
            return true;
        } catch (BackendFormException $e) {
            return false;
        }
    }
    
    /**
     * Internal helper which is used to find a sibling of an element with the given id.
     * The search is only performed in the current container and not in its children
     *
     * @param   string  $id     The id of the element to find the next/prev sibling for
     * @param   bool    $after  By default the next sibling is returned, if this is set to false
     *                          the previous sibling is returned
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement
     */
    protected function getSiblingElement(string $id, bool $after = true): AbstractFormElement
    {
        $keys = array_keys($this->elements);
        $pos  = (int)array_search($id, $keys);
        if (isset($keys[$pos + ($after ? +1 : -1)])) {
            return $this->elements[$keys[$pos + ($after ? +1 : -1)]];
        }
        
        return $this->elements[$id];
    }
    
    /**
     * Checks if the given element is a container or not
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement  $el
     *
     * @return bool
     */
    protected function elIsContainer(AbstractFormElement $el): bool
    {
        return $el instanceof AbstractFormContainer
               && ! $el instanceof AbstractForm
               && ! $el instanceof AbstractFormTab;
    }
    
    /**
     * Checks if the given element is a tab or not
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement  $el
     *
     * @return bool
     */
    protected function elIsTab(AbstractFormElement $el): bool
    {
        return $el instanceof AbstractFormTab;
    }
    
    /**
     * An optional hook that is triggered before an element is removed from the list.
     * This allows child classes to update internal indexes if required
     *
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\Abstracts\AbstractFormElement  $el
     */
    protected function __elRemovalHook(AbstractFormElement $el): void
    {
        // Void by default
    }
}
