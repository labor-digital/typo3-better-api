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

namespace LaborDigital\T3BA\Tool\Tca\Builder\Logic;

use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

abstract class AbstractType extends AbstractForm
{
    
    /**
     * The parent instance that holds the information about all available types
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTypeList
     */
    protected $parent;
    
    /**
     * Holds the type key this instance represents
     *
     * @var string|int
     */
    protected $typeName;
    
    /**
     * AbstractForm constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractTypeList  $parent
     * @param   string|int                                                  $typeName
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext       $context
     */
    public function __construct(AbstractTypeList $parent, $typeName, TcaBuilderContext $context)
    {
        parent::__construct($context);
        $this->parent = $parent;
        $this->typeName = $typeName;
    }
    
    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     * @inheritDoc
     */
    public function getRoot()
    {
        return $this->getParent();
    }
    
    /**
     * Returns the currently set name of the type represented by this object
     *
     * @return string|int
     */
    public function getTypeName()
    {
        return $this->typeName;
    }
    
    /**
     * Allows you to override the name of this type -> be careful with this! Overwrites are handled without warning!
     *
     * @param   int|string  $typeName
     *
     * @return $this
     */
    public function setTypeName($typeName): AbstractType
    {
        $oldTypeName = $this->typeName;
        $this->typeName = $typeName;
        
        // Update the parent
        if ($this instanceof TcaTableType) {
            $types = $this->parent->getLoadedTypes();
            unset($types[$oldTypeName]);
            $types[$typeName] = $this;
            $this->parent->setLoadedTypes($types);
        }
        
        return $this;
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
        return $this->tree->hasNode($id, Node::TYPE_TAB);
    }
    
}
