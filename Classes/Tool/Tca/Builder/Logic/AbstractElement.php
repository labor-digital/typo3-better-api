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
 * Last modified: 2020.05.24 at 11:34
 */

namespace LaborDigital\T3ba\Tool\Tca\Builder\Logic;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\Traits\ElementConfigTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Flex;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;

abstract class AbstractElement implements NoDiInterface
{
    use ElementConfigTrait;
    
    /**
     * The tree node that represents this element
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node
     */
    protected $node;
    
    /**
     * The form this element is part of
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractForm
     */
    protected $form;
    
    /**
     * Contains the label for the tab
     *
     * @var string
     */
    protected $label;
    
    /**
     * AbstractFormElement constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node           $node
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractForm  $form
     */
    public function __construct(Node $node, AbstractForm $form)
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
     *    - "field": positions the element after the "id" elements
     *    - before:field positions the element in front of the element with "field" as id
     *    - after:field positions the element after the element with "field" as id
     *    - top:container positions the element as first element of a container/tab
     *    - bottom:container positions the element as last element of a container/tab
     *    - ['before', 'field']: work the same way as the string representation
     *    - $field: All field instances can be used in the same way as their "id" would
     *    - ['bottom', $container]: you can use the instance of a field/container as reference as well
     *
     * @param   string|array|AbstractElement  $position  Either the position to move the field to,
     *                                                   or the field will be added to the end of the FIRST possible tab.
     *                                                   Can be an array like this as well: ['before', 'field'] or ['before', 'fieldReference']
     *
     * @return $this
     */
    public function moveTo($position = '0')
    {
        if ($position instanceof AbstractElement) {
            $this->node->moveTo($position->node);
        } else {
            $this->node->moveTo($position);
        }
        
        return $this;
    }
    
    /**
     * Returns the parent element of this element.
     * Either the form, a tab or a container element.
     *
     * @return AbstractForm|AbstractType|AbstractElement
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
     * Returns the instance of the type this element is part of
     *
     * @return AbstractForm|AbstractType|TcaTableType|Flex
     */
    public function getForm()
    {
        return $this->form;
    }
    
    /**
     * Returns the instance of the root element that holds the elements type
     *
     * @return AbstractTypeList|TcaTable
     */
    public function getRoot()
    {
        return $this->form->getRoot();
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
        return $this->label ?? '';
    }
    
    /**
     * Can be used to set the label for this element
     *
     * @param   string|null  $label
     *
     * @return $this
     */
    public function setLabel(?string $label)
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
     * Can be used to set raw config values, that are not implemented in the TCA builder facade.
     *
     * @param   array  $raw  The new configuration to be set for this element
     *
     * @return $this
     */
    public function setRaw(array $raw)
    {
        if (isset($raw['label'])) {
            $this->label = $raw['label'];
            unset($raw['label']);
        }
        
        $this->config = $raw;
        
        return $this;
    }
    
    /**
     * Removes this element from the form
     */
    public function remove(): void
    {
        $this->node->remove();
    }
}
