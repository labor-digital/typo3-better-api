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
 * Last modified: 2021.01.14 at 19:51
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Event\Tca\TableDumperAfterBuildEvent;
use LaborDigital\T3BA\Event\Tca\TableDumperBeforeBuildEvent;
use LaborDigital\T3BA\Event\Tca\TableDumperTypeFilterEvent;
use LaborDigital\T3BA\Tool\Tca\Builder\TcaBuilderException;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\AbstractTcaTable;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\DumperGenericTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\DumperMergingTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\DumperTableTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\Io\Traits\DumperTypeGeneratorTrait;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

class TableDumper implements TcaDumperInterface
{
    use DumperGenericTrait;
    use DumperMergingTrait;
    use DumperTypeGeneratorTrait;
    use DumperTableTrait;

    /**
     * @var \LaborDigital\T3BA\Core\EventBus\TypoEventBus
     */
    protected $eventBus;

    /**
     * TableDumper constructor.
     *
     * @param   \LaborDigital\T3BA\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(TypoEventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @inheritDoc
     */
    public function dump(AbstractTcaTable $table): array
    {
        if (! $table instanceof TcaTable) {
            throw new TcaBuilderException('Invalid table given!');
        }

        $this->eventBus->dispatch(new TableDumperBeforeBuildEvent($table));

        $tca        = $this->dumpBasicTca($table);
        $initialTca = $table->getInitialConfig();
        $this->mergeMissingColumns($initialTca, $tca);

        // Dump types
        foreach ($table->getLoadedTypes() as $type) {
            $typeName = $type->getTypeName();
            $typeTca  = $this->dumpBasicTca($type);

            $this->eventBus->dispatch($e = new TableDumperTypeFilterEvent(
                $typeTca, $type, $table
            ));
            $typeTca = $e->getTypeTca();

            $this->dumpColumnOverrides($typeName, $tca, $typeTca, $table);
            $this->dumpTypePalettes($typeName, $tca, $typeTca);
            $this->mergeTableDataHooks($tca, $typeTca);
        }

        $this->mergeMissingTypes($initialTca, $tca);
        $this->dumpSql($table, $tca);

        $this->eventBus->dispatch($e = new TableDumperAfterBuildEvent($tca, $table));
        $tca = $e->getTca();

        dbge($table, $tca);
        // TODO: Implement dump() method.
    }
}
