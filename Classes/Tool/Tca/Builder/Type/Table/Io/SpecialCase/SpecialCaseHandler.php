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
 * Last modified: 2021.10.27 at 11:35
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;
use TYPO3\CMS\Core\SingletonInterface;

class SpecialCaseHandler implements PublicServiceInterface, SingletonInterface
{
    /**
     * External "API" to register addition special case handlers that are not provided by the T3BA extension
     *
     * @var TcaSpecialCaseHandlerInterface[]
     */
    public static $cases = [];
    
    /**
     * Internal list of all case instances by their table name
     *
     * @var array
     */
    protected $casesByTable = [];
    
    public function __construct()
    {
        $this->prepareCaseList([
            new SysFileReferenceCase(),
        ]);
    }
    
    public function initializeTca(array &$tca, TcaTable $table): void
    {
        foreach ($this->getCasesFor($table) as $case) {
            $case->initializeTca($tca, $table);
        }
    }
    
    public function dumpTca(array &$tca, TcaTable $table): void
    {
        foreach ($this->getCasesFor($table) as $case) {
            $case->dumpTca($tca, $table);
        }
    }
    
    /**
     * Iterates the list of all available case objects and sorts them by their table name
     *
     * @param   array  $builtInCases  The list of built-in case instances
     */
    protected function prepareCaseList(array $builtInCases): void
    {
        foreach (array_merge($builtInCases, static::$cases) as $case) {
            if (! $case instanceof TcaSpecialCaseHandlerInterface) {
                throw new \InvalidArgumentException(
                    'A special case handler (' . get_class($case) . ') does not implement the required ' .
                    TcaSpecialCaseHandlerInterface::class . ' interface!');
            }
            
            foreach ($case->provideTableNames() as $tableName) {
                $this->casesByTable[$tableName][get_class($case)] = $case;
            }
        }
    }
    
    /**
     * Returns the list of all cases for a table or an empty array
     *
     * @param   \LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable  $table
     *
     * @return TcaSpecialCaseHandlerInterface[]
     */
    protected function getCasesFor(TcaTable $table): array
    {
        return $this->casesByTable[$table->getTableName()] ?? [];
    }
}