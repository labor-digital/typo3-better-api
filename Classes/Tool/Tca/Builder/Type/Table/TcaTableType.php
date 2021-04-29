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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table;


use LaborDigital\T3BA\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\Traits\ElementConfigTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\TcaDataHookCollectorAddonTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

class TcaTableType extends AbstractType
{
    use ElementConfigTrait;
    use DataHookCollectorTrait;
    use TcaDataHookCollectorAddonTrait;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable
     */
    protected $parent;
    
    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory
     */
    protected $typeFactory;
    
    /**
     * If set to true all field id issues will be ignored
     *
     * @var bool
     */
    protected $ignoreFieldIdIssues = false;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        TcaTable $parent,
        $typeName,
        TcaBuilderContext $context,
        TypeFactory $typeFactory
    )
    {
        parent::__construct($parent, $typeName, $context);
        $this->typeFactory = $typeFactory;
    }
    
    /**
     * Returns the name of the linked database table
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->parent->getTableName();
    }
    
    /**
     * Allows you to ignore all field id issues for this type
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function ignoreFieldIdIssues(bool $state = true): self
    {
        $this->ignoreFieldIdIssues = $state;
        
        return $this;
    }
    
    /**
     * Returns the instance of a certain field inside your current layout
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * @param   string     $id                   The id / column name of this field in the database
     * @param   bool|null  $ignoreFieldIdIssues  If set to true, the field id will not be validated
     *                                           against TYPO3s field naming schema
     *
     * @return TcaField
     * @throws \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\InvalidFieldIdException
     */
    public function getField(string $id, ?bool $ignoreFieldIdIssues = null): TcaField
    {
        // Special validation for $id names -> this a human failsafe.
        if ($this->ignoreFieldIdIssues !== true && $ignoreFieldIdIssues !== true
            && $id !== Inflector::toDatabase($id)
            && $this->context->getExtConfigContext()->getTypoContext()->env()->isDev()) {
            throw new InvalidFieldIdException(
                'Your field: "' . $id . '" of table: "' . $this->getTableName() . '" (type: ' . $this->getTypeName() .
                ') will probably not be mapped correctly when you work with ext base domain models. ' .
                'TYPO3 expects a db field name of: "' . Inflector::toDatabase($id) . '"' .
                ', if you use a model property with name: "$' . Inflector::toProperty($id) . '". ' .
                'If that is no issue for your usage, please set the second argument of this method to true, ' .
                'to disable this exception: getField(\'' . $id .
                '\', true); Keep in mind that you have to manually map the field, tho!');
        }
        
        return $this->findOrCreateChild($id, Node::TYPE_FIELD, function (Node $node) use ($id, $ignoreFieldIdIssues) {
            /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField $i */
            $i = $this->context->cs()->di->makeInstance(
                TcaField::class, [
                    $node,
                    $this,
                ]
            );
            
            // Inherit the configuration for a field
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($this->getTypeName() != $this->parent->getDefaultTypeName()) {
                // Special handling if we are not in the default type
                $defaultType = $this->parent->getType();
                if ($defaultType->hasField($id)) {
                    $i->inheritFrom($defaultType->getField($id, $ignoreFieldIdIssues));
                    
                    return $i;
                }
            }
            
            // Inherit from the column config if possible
            $columns = $this->parent->getRaw(true)['columns'] ?? [];
            if ($columns[$id]) {
                $i->setRaw($columns[$id]);
            } else {
                $i->setRaw(TableDefaults::FIELD_TCA);
            }
            
            return $i;
        });
    }
    
    /**
     * Returns the list of all registered fields that are currently inside the layout
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaField[]
     */
    public function getFields(): iterable
    {
        return parent::getFields();
    }
    
    /**
     * Returns a single palette instance
     * Note: If the palette not exists, a new one will be created at the end of the form
     *
     * @param   string  $id
     *
     * @return TcaPalette
     */
    public function getPalette(string $id): TcaPalette
    {
        return $this->findOrCreateChild($id, Node::TYPE_CONTAINER, function (Node $node) {
            /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPalette $i */
            $i = $this->context->cs()->di->makeInstance(
                TcaPalette::class, [$node, $this]
            );
            $i->setLabel('');
            
            return $i;
        });
    }
    
    /**
     * Returns true if the layout has a palette with that id already registered
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function hasPalette(string $id): bool
    {
        return $this->hasChild($id, Node::TYPE_CONTAINER);
    }
    
    /**
     * Returns the list of all palettes that are used inside of this form
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaPalette[]|iterable
     */
    public function getPalettes(): iterable
    {
        return $this->findAllChildrenByType(Node::TYPE_CONTAINER);
    }
    
    /**
     * Similar to getPalettes() but only returns the keys of the palettes instead of the whole object
     *
     * @return iterable
     */
    public function getPaletteKeys(): iterable
    {
        foreach ($this->getPalettes() as $palette) {
            yield $palette->getId();
        }
    }
    
    /**
     * Adds a new line break to palettes
     *
     * @param   string|null  $position  The position where to add the tab. See moveTo() for details
     *
     * @return string
     */
    public function addLineBreak(?string $position = null): string
    {
        $id = 'lb-' . md5((string)microtime(true));
        
        $el = $this->findOrCreateChild($id, Node::TYPE_NL, function ($node) {
            return ($this->context->cs()->di->makeInstance(
                TcaPaletteLineBreak::class, [$node, $this]
            ));
        });
        
        if ($position !== null) {
            $el->moveTo($position);
        }
        
        return $id;
    }
    
    /**
     * Returns the instance of a certain tab.
     *
     * Note: If the tab not exists, a new one will be created at the end of the form
     *
     * @param   int  $id
     *
     * @return TcaTab
     */
    public function getTab(int $id): TcaTab
    {
        return $this->findOrCreateChild($id, Node::TYPE_TAB, function (Node $node) {
            return ($this->context->cs()->di->makeInstance(
                $this->getTabClass(), [$node, $this]
            ))->setLabel('t3ba.tab.untitled');
        });
    }
    
    /**
     * @inheritDoc
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab
     */
    public function getNewTab(): TcaTab
    {
        return parent::getNewTab();
    }
    
    /**
     * @inheritDoc
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab[]
     */
    public function getTabs(): iterable
    {
        return parent::getTabs();
    }
    
    /**
     * Can be used to set raw config values, that are not implemented in the TCA builder facade.
     *
     * @param   array  $raw         The configuration to set
     * @param   bool   $repopulate  Not all parts of the tca are updated automatically after you changed them.
     *                              "columnOverrides" and "showitem" are only stored in their initial state.
     *                              Those elements are represented by a tree of objects containing the actual
     *                              information. If you want to recreate this tree, set this property to true.
     *                              This forces the system to invalidate all existing objects and recreate
     *                              them based on your provided $data. All existing instances and their configuration
     *                              will be dropped and set to the provided configuration in data. Be careful with this!
     */
    public function setRaw(array $raw, bool $repopulate = false): self
    {
        if ($repopulate) {
            $this->typeFactory->populate($this, $raw);
        } else {
            $this->loadDataHooks($raw);
            $this->config = $raw;
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = Arrays::without($this->config, ['columnsOverrides', 'showitem']);
        $this->dumpDataHooks($raw);
        
        return $raw;
    }
    
    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        parent::clear();
        $this->config = [];
    }
    
    /**
     * @inheritDoc
     */
    protected function getTabClass(): string
    {
        return TcaTab::class;
    }
}
