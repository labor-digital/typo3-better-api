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
 * Last modified: 2021.01.14 at 20:03
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryPopulatorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryTypeLoaderTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;

class TypeFactory implements TcaInitializerInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use FactoryTypeLoaderTrait;
    use FactoryPopulatorTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;

    public function injectTableFactory(TableFactory $tableFactory): void
    {
        $this->tableFactory = $tableFactory;
    }

    /**
     * Creates a new, empty instance for a certain TCA table type
     *
     * @param             $typeName
     * @param   TcaTable  $table
     *
     * @return TcaTableType
     */
    public function create($typeName, TcaTable $table): TcaTableType
    {
        return $this->getWithoutDi(
            TcaTableType::class, [
                $table->getContext(),
                $this->tableFactory,
                $this,
                $table,
            ]
        )->setTypeName($typeName);
    }

    /**
     * Finds the type TCA (the entry below "types" for the given $typename on the specified $table object
     *
     * @param                                                            $typeName
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return array
     */
    public function getTypeTca($typeName, TcaTable $table): array
    {
        $types   = $this->findTypes($table->getInitialConfig());
        $typeTca = $types[$typeName] ?? null;

        return is_array($typeTca) ? $typeTca : TableDefaults::TYPE_TCA;
    }

    /**
     * @inheritDoc
     */
    public function initialize(array $typeTca, AbstractTcaTable $type): void
    {
        if (! $type instanceof TcaTableType) {
            throw new TcaBuilderException('Invalid table given!');
        }

        $type->removeAllChildren();

        $this->populateElements($type, $typeTca);
    }

}
