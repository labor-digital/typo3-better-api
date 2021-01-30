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
 * Last modified: 2021.01.14 at 19:50
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io;


use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderContext;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryPopulatorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryTcaLoaderTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryTypeLoaderTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

class TableFactory implements TcaInitializerInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use FactoryTcaLoaderTrait;
    use FactoryTypeLoaderTrait;
    use FactoryPopulatorTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TypeFactory
     */
    protected $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * Creates a new, empty instance for a certain TCA table
     *
     * @param   string                                         $tableName
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext  $configContext
     *
     * @return \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable
     */
    public function create(string $tableName, ExtConfigContext $configContext): TcaTable
    {
        return $this->getWithoutDi(
            TcaTable::class, [
                $tableName,
                $this->getWithoutDi(TcaBuilderContext::class, [$configContext]),
                $this,
                $this->typeFactory,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function initialize(array $tca, AbstractTcaTable $table): void
    {
        if (! $table instanceof TcaTable) {
            throw new TcaBuilderException('Invalid table given!');
        }

        $table->removeAllChildren();

        $table->setRaw(array_merge($tca, ['@factoryInit' => true]));
        $table->setInitialConfig($tca);
        $types       = $this->findTypes($tca);
        $defaultType = $this->findDefaultTypeName($tca, $types);

        $table->setTypeName($defaultType);

        $this->populateElements($table, $types[$defaultType] ?? []);
    }
}
