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
 * Last modified: 2020.05.24 at 11:34
 */

namespace LaborDigital\T3BA\ExtConfig\BackendForm\Logic;

use LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode;
use LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm;
use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;

abstract class AbstractFormElement
{
    /**
     * The tree node that represents this element
     *
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode
     */
    protected $node;
    
    /**
     * The form this element is part of
     *
     * @var \LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractForm
     */
    protected $form;
    
    /**
     * Contains the label for the tab
     *
     * @var string
     */
    protected $label;
    
    /**
     * The configuration of this element
     *
     * @var array
     */
    protected $config = [];
    
    /**
     * AbstractFormElement constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\BackendForm\Tree\FormNode       $node
     * @param   \LaborDigital\T3BA\ExtConfig\BackendForm\Logic\AbstractForm  $form
     */
    public function __construct(FormNode $node, AbstractForm $form)
    {
        $this->node = $node;
        $this->form = $form;
    }
    
    /**
     * Moves this element to a new position, defined by the position string.
     *
     * Position can be defined as "field", "container" or "0" (tabs) to move the element AFTER the defined element.
     *
     * You may also use the following modifiers:
     *    - before:field positions the element in front of the element with "field" as id
     *    - after:field positions the element after the element with "field" as id
     *    - top:container positions the element as first element of a container/tab
     *    - bottom:container positions the element as last element of a container/tab
     *
     * @param   string  $position  Either the position to move the field to, or the field will be added to the end of
     *                             the FIRST possible tab
     *
     * @return $this
     */
    public function moveTo(string $position = '0'): self
    {
        $this->node->moveTo($position);
        
        return $this;
    }
    
    /**
     * Returns the parent element of this element.
     * Either the form, a tab or a container element.
     *
     * @return AbstractForm|AbstractFormElement
     */
    public function getParent()
    {
        // Return the form if this element is a tab
        if ($this->node->isTab()) {
            return $this->form;
        }
        
        // Return the parent element
        return $this->node->getParent()->getEl();
    }
    
    /**
     * Returns the instance of the parent form / parent table
     *
     * @return AbstractForm|TcaTable|FlexForm
     */
    public function getForm()
    {
        return $this->form;
    }
    
    /**
     * Returns the id for this element
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->node->getId();
    }
    
    /**
     * Returns the currently set label for this element
     *
     * @return string
     */
    public function getLabel(): string
    {
        return (string)$this->label;
    }
    
    /**
     * Can be used to set the label for this element
     *
     * @param   string|null  $label
     *
     * @return $this
     */
    public function setLabel(?string $label): self
    {
        if ($label === '') {
            $label = null;
        }
        $this->label = $label;
        
        return $this;
    }
    
    /**
     * Returns true if the element has a defined label, false if not
     *
     * @return bool
     */
    public function hasLabel(): bool
    {
        return ! is_null($this->label);
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
        
        // Special handling for the labels
        if (isset($this->config['label'])) {
            $this->label = $this->config['label'];
        }
        unset($this->config['label']);
        
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
     * Removes this element from the form
     */
    public function remove(): void
    {
        $this->node->remove();
    }
}