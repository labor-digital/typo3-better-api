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


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io;


use LaborDigital\T3ba\Core\EventBus\TypoEventBus;
use LaborDigital\T3ba\Event\Tca\TableDumperAfterBuildEvent;
use LaborDigital\T3ba\Event\Tca\TableDumperBeforeBuildEvent;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase\SpecialCaseHandler;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Traits\DumperDataHookTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Traits\DumperGenericTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\Traits\DumperTypeGeneratorTrait;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTableType;

class Dumper
{
    use DumperGenericTrait;
    use DumperTypeGeneratorTrait;
    use DumperDataHookTrait;
    
    /**
     * @var \LaborDigital\T3ba\Core\EventBus\TypoEventBus
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase\SpecialCaseHandler
     */
    protected $specialCaseHandler;
    
    /**
     * TableDumper constructor.
     *
     * @param   \LaborDigital\T3ba\Core\EventBus\TypoEventBus  $eventBus
     */
    public function __construct(TypoEventBus $eventBus, SpecialCaseHandler $specialCaseHandler)
    {
        $this->eventBus = $eventBus;
        $this->specialCaseHandler = $specialCaseHandler;
    }
    
    public function dump(TcaTable $table): array
    {
        $this->eventBus->dispatch(new TableDumperBeforeBuildEvent($table));
        
        $this->clearDataHookCache();
        
        $tca = $this->dumpRootTca($table);
        $this->extractDataHooksFromTca($tca);
        $defaultTypeName = $table->getDefaultTypeName();
        
        foreach ($table->getLoadedTypes() as $typeName => $type) {
            // Ignore the default type -> as it is already part of the $tca
            if ((string)$defaultTypeName === (string)$typeName) {
                continue;
            }
            
            // Ignore invalid types
            if (! $type instanceof TcaTableType) {
                continue;
            }
            
            $typeName = $type->getTypeName();
            $typeTca = $this->dumpTcaTypeVariant($type);
            $this->extractDataHooksFromTca($typeTca);
            $typeRaw = $type->getRaw();
            $this->extractDataHooksFromTca($typeRaw);
            $tca['types'][$typeName] = $typeRaw;
            
            $this->dumpColumnOverrides($typeName, $tca, $typeTca, $table);
            $this->dumpTypePalettes($typeName, $tca, $typeTca);
        }
        
        $this->injectDataHooksIntoTca($tca);
        
        $this->specialCaseHandler->dumpTca($tca, $table);
        
        return $this->eventBus->dispatch(new TableDumperAfterBuildEvent($tca, $table))->getTca();
    }
}
