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
 * Last modified: 2021.01.14 at 19:56
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table;


use LaborDigital\T3BA\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractForm;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Tree\Node;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Traits\TypeAwareDataHookCollectorTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;

abstract class AbstractTcaTable extends AbstractForm
{
    use DataHookCollectorTrait;
    use TypeAwareDataHookCollectorTrait;

    /**
     * A list of TCA configuration keys that, if present in "setRaw" should trigger
     * a complete re-initialization of the table. We need this, because otherwise
     * we could not detect any root-level changes to columns or types.
     *
     * @var string[]
     */
    public static $configKeysToReinitializeWith
        = [
            'columns',
            'palettes',
            'types',
            'columnsOverrides',
        ];

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory
     */
    protected $typeFactory;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TcaInitializerInterface
     */
    protected $initializer;

    /**
     * Holds the name of the db table we work with
     *
     * @var string
     */
    protected $tableName;

    /**
     * The parent instance that holds the information about types and the table itself
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable
     */
    protected $form;

    /**
     * Holds the type key this instance represents
     *
     * @var string
     */
    protected $typeName;

    /**
     * @inheritDoc
     */
    public function __construct(
        string $tableName,
        TcaBuilderContext $context,
        TableFactory $tableFactory,
        TypeFactory $typeFactory,
        TcaTable $parent
    ) {
        parent::__construct($context);
        $this->tableName    = $tableName;
        $this->tableFactory = $tableFactory;
        $this->typeFactory  = $typeFactory;
        $this->form         = $parent;
    }

    /**
     * Returns the name of the linked database table
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Returns the instance of a certain field inside your current layout
     * Note: If the field not exists, a new one will be created at the end of the form
     *
     * @param   string     $id               The id / column name of this field in the database
     * @param   bool|null  $autoTransformId  By default all id's will be transformed to underscored versions,
     *                                       because TYPO3 needs this for it's db resolution. You can disable this
     *                                       feature by setting this to FALSE.
     *
     * @return TcaField
     */
    public function getField(string $id, ?bool $autoTransformId = null): TcaField
    {
        $id = $autoTransformId === false ? $id : Inflector::toDatabase($id);

        return $this->findOrCreateChild($id, Node::TYPE_FIELD, function (Node $node) use ($id) {
            // Set up default sql for this field
            $sqlBuilder = $this->context->cs()->sqlBuilder;
            if (! $sqlBuilder->hasDefinitionFor($this->getTableName(), $id)) {
                $this->context->cs()->sqlBuilder->setDefinitionFor($this->getTableName(), $id, 'text');
            }

            /** @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab $i */
            return $this->context->cs()->di->getWithoutDi(
                TcaField::class, [$node, $this]
            );
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
            $i = $this->context->cs()->di->getWithoutDi(
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
     * Similar to getPalettes() but only returns the keys of the fields instead of the whole object
     *
     * @return array
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
            return ($this->context->cs()->di->getWithoutDi(
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
            return ($this->context->cs()->di->getWithoutDi(
                TcaTab::class, [$node, $this]
            ))->setLabel('NT: Unnamed Tab'); // @todo add the correct translation label
        });
    }

    /**
     * Return the list of all registered tab instances
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTab[]
     */
    public function getTabs(): iterable
    {
        return parent::getTabs();
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
    public function setTypeName($typeName)
    {
        $oldTypeName    = $this->typeName;
        $this->typeName = (string)$typeName;

        // Update the parent
        if ($this instanceof TcaTableType) {
            $types = $this->form->getLoadedTypes();
            unset($types[$oldTypeName]);
            $types[$this->typeName] = $this;
            $this->form->setLoadedTypes($types);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRaw($key, $value = null): AbstractForm
    {
        $raw = is_array($key) ? $key : [$key => $value];

        $reinitialize = false;
        foreach ($raw as $k => $v) {
            if (in_array($k, static::$configKeysToReinitializeWith, true)) {
                $reinitialize = true;
                break;
            }
        }

        // Ignore reinitialization if the factory initializes us
        if ($raw['@factoryInit']) {
            $reinitialize = false;
            unset($raw['@factoryInit']);
        }

        // Inherit data hooks
        $this->loadDataHooksBasedOnType($raw);

        parent::setRaw(Arrays::without($raw, static::$configKeysToReinitializeWith));

        if ($reinitialize) {
            $this->initializer->initialize(
                Arrays::merge($this->getRaw(), $raw),
                $this
            );
        }

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function mergeRaw(array $rawInput): AbstractForm
    {
        return $this->setRaw(Arrays::merge($this->config, $rawInput, 'allowRemoval'));
    }

    /**
     * @inheritDoc
     */
    public function getRaw(): array
    {
        $raw = parent::getRaw();

        // Add the data hooks
        $handlers = $this->getRegisteredDataHooks();
        if (! empty($handlers)) {
            $raw[DataHookTypes::TCA_DATA_HOOK_KEY] = $handlers;
        }

        return $raw;
    }


    /**
     * Returns the instance of the parent form / parent table
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable
     */
    public function getForm(): TcaTable
    {
        return $this->form;
    }

    /**
     * @inheritDoc
     */
    protected function getTabClass(): string
    {
        return TcaTab::class;
    }
}
