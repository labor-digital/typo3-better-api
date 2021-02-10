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
 * Last modified: 2021.02.01 at 15:01
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Logic;


use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\FlexSheet;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

abstract class AbstractTypeList
{

    /**
     * @var TcaBuilderContext
     */
    protected $context;

    /**
     * Contains the list of all instantiated tca types of this list
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/DataFormats/T3datastructure/SheetReferences/Index.html
     *
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType[]
     */
    protected $types = [];

    /**
     * AbstractTypeList constructor.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext  $context
     */
    public function __construct(TcaBuilderContext $context)
    {
        $this->context = $context;
    }

    /**
     * Returns the context object
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext
     */
    public function getContext(): TcaBuilderContext
    {
        return $this->context;
    }

    /**
     * Returns the instance of a certain tca type.
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     *
     * @param   string|int|null  $typeName
     *
     * @return AbstractType|TcaTableType
     */
    public function getType($typeName = null): AbstractType
    {
        if ($typeName === null) {
            $typeName = $this->getDefaultTypeName();
        }

        if (isset($this->types[$typeName])) {
            return $this->types[$typeName];
        }

        return $this->types[$typeName] = $this->loadType($typeName);
    }

    /**
     * Returns true if the given type name is currently registered
     *
     * @param $typeName
     *
     * @return bool
     */
    public function hasType($typeName): bool
    {
        return in_array($typeName, $this->getTypeNames(), false);
    }

    /**
     * Returns the list of all type names that are currently registered (both loaded and defined)
     *
     * @return array
     */
    public function getTypeNames(): array
    {
        return array_keys($this->types);
    }

    /**
     * Returns the name of the default type (normally the first one in the list of type names)
     *
     * @return int|string
     */
    public function getDefaultTypeName()
    {
        $types = $this->getTypeNames();

        return empty($types) ? 0 : reset($types);
    }

    /**
     * Returns true if a certain type is currently loaded as object representation
     *
     * @param $typeName
     *
     * @return bool
     */
    public function isTypeLoaded($typeName): bool
    {
        return isset($this->types[$typeName]);
    }

    /**
     * Allows you to completely replace all type instances for this list.
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType[]  $types
     *
     * @return $this
     */
    public function setLoadedTypes(array $types)
    {
        $this->types = [];
        foreach ($types as $k => $t) {
            $this->setLoadedType($k, $t);
        }

        return $this;
    }

    /**
     * Adds a new type to the list of loaded types
     *
     * @param   int|string    $typeName
     * @param   AbstractType  $type
     *
     * @return $this
     */
    public function setLoadedType($typeName, AbstractType $type)
    {
        $this->types[$typeName] = $type;

        return $this;
    }

    /**
     * Returns the list of all types
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType[]
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Types/Index.html#types
     */
    public function getLoadedTypes(): array
    {
        return $this->types;
    }

    /**
     * Removes all types and configuration from the list, leaving you with a clean state
     */
    public function clear(): void
    {
        $this->types = [];
    }

    /**
     * This method must be implemented by the child class and should
     * return a new type instance for the given type name.
     *
     * @param $typeName
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Logic\AbstractType
     */
    abstract protected function loadType($typeName): AbstractType;
}
