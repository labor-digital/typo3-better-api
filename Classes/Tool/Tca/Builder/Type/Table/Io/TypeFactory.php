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


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\DataHook\DataHookTypes;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryDataHookTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryPopulatorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\FactoryTypeLoaderTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TableDefaults;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType;
use LaborDigital\T3BA\Tool\Tca\TcaUtil;

class TypeFactory implements PublicServiceInterface
{
    use ContainerAwareTrait;
    use FactoryTypeLoaderTrait;
    use FactoryPopulatorTrait;
    use FactoryDataHookTrait;

    /**
     * @var \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory
     */
    protected $tableFactory;

    /**
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\TableFactory  $tableFactory
     */
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
        return $this->makeInstance(
            TcaTableType::class, [
                $table,
                $typeName,
                $table->getContext(),
                $this,
            ]
        );
    }

    /**
     * Populates the type based on the TCA showitem string
     *
     * @param   \LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTableType  $type
     * @param   array|null                                                   $typeTca
     */
    public function populate(TcaTableType $type, ?array $typeTca = null): void
    {
        // Prepare the tca to match the selected type
        $tca            = $type->getParent()->getRaw(true);
        $typeTca        = $typeTca ?? $tca['types'][$type->getTypeName()] ?? TableDefaults::TYPE_TCA;
        $tca['columns'] = TcaUtil::applyColumnOverrides(
            $tca['columns'] ?? [], $typeTca['columnsOverrides'] ?? []
        );

        // Inherit the data hooks from the table
        if (! isset($typeTca[DataHookTypes::TCA_DATA_HOOK_KEY])
            || ! is_array($typeTca[DataHookTypes::TCA_DATA_HOOK_KEY])) {
            $typeTca[DataHookTypes::TCA_DATA_HOOK_KEY] = $tca[DataHookTypes::TCA_DATA_HOOK_KEY];
        }

        $tca['types'][$type->getTypeName()] = $typeTca;
        $type->setRaw($typeTca);

        // Start the population
        $type->removeAllChildren();
        $this->populateElements($type, $tca);
    }

}
