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
 * Last modified: 2021.06.04 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm;


use Closure;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractElement;
use LaborDigital\T3ba\Tool\Tca\Builder\Logic\AbstractForm;
use LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\Factory;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use Neunerlei\Arrays\Arrays;

class Flex extends AbstractForm
{
    /**
     * Our normal period "." does not work very well for flex forms,
     * because there are often keys that have periods in them, themselves.
     * So we use the php object key as a exception to the rule here...
     */
    public const PATH_SEPARATOR = '->';
    
    /**
     * The form field reference which holds this flex form
     *
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField
     */
    protected $containingField;
    
    /**
     * The factory to initialize the sheets with
     *
     * @var Factory
     */
    protected $factory;
    
    /**
     * Contains additional metadata that was stored in the head of the flex form config
     *
     * @var array
     */
    protected $meta = [];
    
    /**
     * @inheritDoc
     */
    public function __construct(TcaField $containingField, Factory $factory, TcaBuilderContext $context)
    {
        parent::__construct($context);
        $this->tree->setAllowTabIdStrings(true);
        $this->tree->setDefaultTabId('sDEF');
        $this->containingField = $containingField;
        $this->factory = $factory;
    }
    
    /**
     * @inheritDoc
     * @return TcaField
     */
    public function getParent(): TcaField
    {
        return $this->containingField;
    }
    
    /**
     * @inheritDoc
     * @return TcaTable
     */
    public function getRoot(): TcaTable
    {
        return $this->containingField->getRoot();
    }
    
    /**
     * Returns the context object
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\TcaBuilderContext
     */
    public function getContext(): TcaBuilderContext
    {
        return $this->context;
    }
    
    /**
     * Returns additional metadata that was stored in the head of the flex form config
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
    
    /**
     * Allows you to update the additional metadata that will be stored in the head of the flex form config
     *
     * @param   array  $meta
     *
     * @return Flex
     */
    public function setMeta(array $meta): Flex
    {
        $this->meta = $meta;
        
        return $this;
    }
    
    /**
     * Returns the form field which holds this flex form structure
     *
     * IMPORTANT: THIS IS THE TCA FIELD! Don't confuse this with getField()!
     *
     * @return TcaField
     */
    public function getContainingField(): TcaField
    {
        return $this->containingField;
    }
    
    /**
     * Allows the outside world to update the field linked with this flex form structure
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaField  $field
     *
     * @return $this
     */
    public function setContainingField(TcaField $field): self
    {
        $this->containingField = $field;
        
        return $this;
    }
    
    /**
     * You can use this method to load a new flex form definition into your current form.
     * Note: This will overwrite your current configuration!.
     *
     * A definition may either be a file path like: FILE:EXT:$your_ext/.../flexForm.xml
     * A definition may also be a valid flex form string
     * In addition a definition may also be only the base name of the flex form file, like "flexForm"
     * this will then automatically look for the flex form definition in your current extensions
     * "Configuration/FlexForms" directory.
     *
     * @param   string  $definition
     *
     * @return $this
     */
    public function loadDefinition(string $definition): self
    {
        $this->factory->initialize($this, $definition);
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function hasChild($id, ?int $type = null): bool
    {
        return $this->findNodeByPath($id, $type) !== null;
    }
    
    /**
     * @inheritDoc
     */
    public function hasField(string $id): bool
    {
        return $this->findNodeByPath($id, Node::TYPE_FIELD) !== null;
    }
    
    /**
     * Returns the instance of a certain field inside your current layout
     *
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * Note: This method supports the usage of paths. FlexForm fields inside of containers may have
     * the same id's. Which makes the lookup of such fields ambiguous. There is not much you can do about that, tho...
     * To select such fields you can either select the section and then the field inside of it. Or you use paths
     * on a method like this. A path looks like: "section->field" the "->" works as a separator between the parts of
     * the path. As you see there is no Tab definition, like 0 or 1. Because we will look for "section" in all existing
     * tabs.
     *
     * @param   string  $id  The id / column name of this field in the database
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexField
     */
    public function getField(string $id): FlexField
    {
        return $this->findOrCreateChild($id, Node::TYPE_FIELD, function (Node $node) {
            /** @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexField $i */
            return $this->context->cs()->di->makeInstance(
                FlexField::class, [
                    $node,
                    $this,
                ]
            );
        });
    }
    
    /**
     * Returns the list of all registered fields that are currently inside the layout
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexField[]
     */
    public function getFields(): iterable
    {
        return parent::getFields();
    }
    
    /**
     * Returns a single section instance
     * Note: If the section not exists, a new one will be created at the end of the form
     *
     * @param   string  $id
     *
     * @return FlexSection
     */
    public function getSection(string $id): FlexSection
    {
        return $this->findOrCreateChild($id, Node::TYPE_CONTAINER, function (Node $node) {
            /** @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection $i */
            $i = $this->context->cs()->di->makeInstance(
                FlexSection::class, [$node, $this]
            );
            $i->setLabel('');
            
            return $i;
        });
    }
    
    /**
     * Returns true if the layout has a section with that id already registered
     *
     * @param   string  $id
     *
     * @return bool
     */
    public function hasSection(string $id): bool
    {
        return $this->hasChild($id, Node::TYPE_CONTAINER);
    }
    
    /**
     * Returns the list of all sections that are used inside of this form
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexSection[]|iterable
     */
    public function getSections(): iterable
    {
        return $this->findAllChildrenByType(Node::TYPE_CONTAINER);
    }
    
    /**
     * Similar to getSections() but only returns the keys of the sections instead of the whole object
     *
     * @return iterable
     */
    public function getSectionKeys(): iterable
    {
        foreach ($this->getSections() as $palette) {
            yield $palette->getId();
        }
    }
    
    /**
     * Returns true if a given tab exists, false if not
     *
     * @param   string  $id  The id of the tab to check for
     *
     * @return bool
     */
    public function hasTab(string $id): bool
    {
        return $this->tree->hasNode($id, Node::TYPE_TAB);
    }
    
    /**
     * Returns the instance of a certain tab.
     * Note: If the tab not exists, a new one will be created at the end of the form
     *
     * @param   string|null  $id  Either the name of the tab, or null if the default tab "sDEF" should be returned
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\FlexTab
     */
    public function getTab(?string $id = null): FlexTab
    {
        $id = $id ?? 'sDEF';
        
        return $this->findOrCreateChild($id, Node::TYPE_TAB, function (Node $node) {
            return ($this->context->cs()->di->makeInstance(
                $this->getTabClass(), [$node, $this]
            ))->setLabel('t3ba.tab.untitled');
        });
    }
    
    /**
     * @inheritDoc
     */
    protected function findOrCreateChild($id, int $type, Closure $factory): AbstractElement
    {
        if (strpos($id, static::PATH_SEPARATOR) !== false) {
            $node = $this->findNodeByPath($id, $type);
            
            if (! $node) {
                // Try to find the parent node
                $path = $this->parsePath($id);
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $id = array_pop($path);
                $parentNode = $this->findNodeByPath(implode(static::PATH_SEPARATOR, $path));
                
                // Create tne new node
                $node = $this->tree->getNode($id, $type);
                $node->setEl($factory($node));
                
                if ($parentNode) {
                    $parentNode->addChild($node, Node::INSERT_MODE_AFTER);
                }
            }
            
            return $node->getEl();
        }
        
        return parent::findOrCreateChild($id, $type, $factory);
    }
    
    /**
     * @inheritDoc
     */
    public function clear(): void
    {
        $this->meta = [];
        parent::clear();
    }
    
    /**
     * Internal helper that will resolve the given path by recursively looking up a node
     *
     * @param   string|int|array  $id
     * @param   int|null          $type
     *
     * @return \LaborDigital\T3ba\Tool\Tca\Builder\Tree\Node|null
     */
    protected function findNodeByPath($id, ?int $type = null): ?Node
    {
        $tree = $this->tree;
        $path = $this->parsePath($id);
        $lastK = count($path) - 1;
        $node = null;
        foreach ($path as $k => $subId) {
            $node = $tree->getNode($subId, $lastK === $k ? $type : null);
        }
        
        return $node;
    }
    
    /**
     * Internal helper which takes a path and unifies it into an array
     *
     * @param   array|string  $path
     *
     * @return array
     * @throws \LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\InvalidPathException
     */
    protected function parsePath($path): array
    {
        // Prepare the path
        if (is_string($path)) {
            $path = Arrays::parsePath($path, static::PATH_SEPARATOR);
        }
        
        if (! is_array($path) || empty($path)) {
            throw new InvalidPathException('Invalid path given! ' . SerializerUtil::serializeJson($path));
        }
        
        return $path;
    }
    
    /**
     * @inheritDoc
     */
    protected function getTabClass(): string
    {
        return FlexTab::class;
    }
    
    
}
