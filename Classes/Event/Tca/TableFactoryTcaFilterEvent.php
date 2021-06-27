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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Event\Tca;


use LaborDigital\T3ba\Tool\Tca\Builder\Type\Table\TcaTable;

/**
 * Class TableFactoryTcaFilterEvent
 *
 * Dispatched when the TCA builder factory loads the TCA of a specific table.
 * Can be used to modify the configuration on a global or per table scope
 *
 * @package LaborDigital\T3ba\Event\Tca
 */
class TableFactoryTcaFilterEvent
{
    /**
     * The default TCA array to apply to the table
     *
     * @var array
     */
    protected $tca;
    
    /**
     * The table instance the default should be applied to
     *
     * @var TcaTable
     */
    protected $table;
    
    /**
     * TableFactoryTcaFilterEvent constructor.
     *
     * @param   array     $tca
     * @param   TcaTable  $table
     */
    public function __construct(array $tca, TcaTable $table)
    {
        $this->tca = $tca;
        $this->table = $table;
    }
    
    /**
     * Returns the table instance the configuration should be applied to
     *
     * @return TcaTable
     */
    public function getTable(): TcaTable
    {
        return $this->table;
    }
    
    /**
     * Returns the default TCA array to apply to the table
     *
     * @return array
     */
    public function getTca(): array
    {
        return $this->tca;
    }
    
    /**
     * Updates the TCA array to apply to the table
     *
     * @param   array  $tca
     *
     * @return $this
     */
    public function setTca(array $tca): self
    {
        $this->tca = $tca;
        
        return $this;
    }
    
}
