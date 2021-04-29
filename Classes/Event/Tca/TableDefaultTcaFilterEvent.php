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

namespace LaborDigital\T3BA\Event\Tca;

use LaborDigital\T3BA\Tool\Tca\Builder\Type\Table\TcaTable;

/**
 * Class TableDefaultTcaFilterEvent
 *
 * Dispatched when the defaults are applied to a tca table instance.
 * Can be used to modify the defaults on a global or per table scope
 *
 * @package LaborDigital\T3BA\Event\Tca
 */
class TableDefaultTcaFilterEvent
{
    
    /**
     * The default TCA array to apply to the table
     *
     * @var array
     */
    protected $defaultTca;
    
    /**
     * The table instance the default should be applied to
     *
     * @var TcaTable
     */
    protected $table;
    
    /**
     * TableDefaultTcaFilterEvent constructor.
     *
     * @param   array     $defaultTca
     * @param   TcaTable  $table
     */
    public function __construct(array $defaultTca, TcaTable $table)
    {
        $this->defaultTca = $defaultTca;
        $this->table = $table;
    }
    
    /**
     * Returns the table instance the default should be applied to
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
    public function getDefaultTca(): array
    {
        return $this->defaultTca;
    }
    
    /**
     * Updates the default TCA array to apply to the table
     *
     * @param   array  $defaultTca
     *
     * @return TableDefaultTcaFilterEvent
     */
    public function setDefaultTca(array $defaultTca): TableDefaultTcaFilterEvent
    {
        $this->defaultTca = $defaultTca;
        
        return $this;
    }
}
