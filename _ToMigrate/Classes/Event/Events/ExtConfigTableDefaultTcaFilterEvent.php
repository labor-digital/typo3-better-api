<?php
/**
 * Copyright 2020 LABOR.digital
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
 * Last modified: 2020.03.19 at 11:28
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable;

/**
 * Class ExtConfigTableDefaultTcaFilterEvent
 *
 * Dispatched when the defaults are applied to a tca table instance.
 * Can be used to modify the defaults on a global or per table scope
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ExtConfigTableDefaultTcaFilterEvent
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
     * @var \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
     */
    protected $table;
    
    /**
     * ExtConfigTableDefaultTcaFilterEvent constructor.
     *
     * @param   array                                                        $defaultTca
     * @param   \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable  $table
     */
    public function __construct(array $defaultTca, TcaTable $table)
    {
        $this->defaultTca = $defaultTca;
        $this->table      = $table;
    }
    
    /**
     * Returns the table instance the default should be applied to
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TcaForms\TcaTable
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
     * @return ExtConfigTableDefaultTcaFilterEvent
     */
    public function setDefaultTca(array $defaultTca): ExtConfigTableDefaultTcaFilterEvent
    {
        $this->defaultTca = $defaultTca;
        
        return $this;
    }
}
