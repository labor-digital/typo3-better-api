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
 * Last modified: 2021.10.27 at 11:30
 */

declare(strict_types=1);

namespace LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\Io\SpecialCase;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

interface TcaSpecialCaseHandlerInterface extends NoDiInterface
{
    /**
     * MUST return the list of all tables this special case handler affects
     *
     * @return array
     */
    public function provideTableNames(): array;
    
    /**
     * Executed when the TCA of a table is initialized
     *
     * @param   array     $tca    The prepared TCA for the table
     * @param   TcaTable  $table  The uninitialized table object
     *
     * @return array
     */
    public function initializeTca(array &$tca, TcaTable $table): void;
    
    /**
     * Executed after the table was dumped back into a TCA array
     *
     * @param   array     $tca    The dumped TCA of the table
     * @param   TcaTable  $table  The table object that was used to dump the TCA
     */
    public function dumpTca(array &$tca, TcaTable $table): void;
}