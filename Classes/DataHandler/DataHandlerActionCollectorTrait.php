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
 * Last modified: 2020.03.21 at 21:18
 */

namespace LaborDigital\Typo3BetterApi\DataHandler;

use Neunerlei\Arrays\Arrays;

trait DataHandlerActionCollectorTrait
{
    
    /**
     * The list of collected handlers
     *
     * @var array
     */
    protected $dataHandlerActionHandlers = [];
    
    /**
     * Should return the table name this trait collects the data handler actions for
     *
     * @return string
     */
    abstract protected function getDataHandlerTableName(): string;
    
    /**
     * Should return the field constraint (if any is required) or an empty array if no field constraint is required
     *
     * @return array
     */
    abstract protected function getDataHandlerFieldConstraints(): array;
    
    /**
     * Register a new backend form filter for a table.
     *
     * This filter can be used to filter the tca as well as the as the raw table data when the backend builds a form
     * using the form engine. The event contains all the data that are passed to objects that implement the
     * FormDataProviderInterface interface.
     *
     * @param   string  $filterClass   The full name of the class containing the filter
     * @param   string  $filterMethod  The method of the $filterClass to call when the filter is executed
     *
     * @return $this
     */
    public function registerBackendFormFilter(string $filterClass, string $filterMethod = 'filter')
    {
        $this->dataHandlerActionHandlers[$this->getDataHandlerTableName()]['form'][$filterClass . '-' . $filterMethod]
            = [
            $filterClass,
            $filterMethod,
            $this->getDataHandlerFieldConstraints(),
        ];
        
        return $this;
    }
    
    /**
     * Can be used to remove a previously registered form filter from a table.
     *
     * @param   string  $filterClass   The full name of the class containing the filter
     * @param   string  $filterMethod  The method of the $filterClass which should no longer be called when the filter
     *                                 is executed
     *
     * @return $this
     */
    public function removeBackendFormFilter(string $filterClass, string $filterMethod = 'filter')
    {
        $this->dataHandlerActionHandlers = Arrays::removePath(
            $this->dataHandlerActionHandlers,
            [$this->getDataHandlerTableName(), 'form', $filterClass . '-' . $filterMethod]
        );
        
        return $this;
    }
    
    /**
     * The registered method is called every time the backend performs an action. Actions are deletion,
     * translation, copy or moving of a record and many others.
     *
     * @param   string  $handlerClass   The full name of the class containing the handler
     * @param   string  $handlerMethod  The method of the $filterClass to call when the filter is executed
     *
     * @return $this
     */
    public function registerDataHandlerActionHandler(string $handlerClass, string $handlerMethod = 'handle')
    {
        $this->dataHandlerActionHandlers[$this->getDataHandlerTableName()]['default'][$handlerClass . '-'
                                                                                      . $handlerMethod]
            = [
            $handlerClass,
            $handlerMethod,
            $this->getDataHandlerFieldConstraints(),
        ];
        
        return $this;
    }
    
    /**
     * Removes a previously registered backend action handler from the table.
     *
     * @param   string  $handlerClass   The full name of the class containing the handler
     * @param   string  $handlerMethod  The method of the $handlerClass which should no longer be called when the
     *                                  handler stack is executed
     *
     * @return $this
     */
    public function removeDataHandlerActionHandler(string $handlerClass, string $handlerMethod = 'handle')
    {
        $this->dataHandlerActionHandlers = Arrays::removePath(
            $this->dataHandlerActionHandlers,
            [$this->getDataHandlerTableName(), 'default', $handlerClass . '-' . $handlerMethod]
        );
        
        return $this;
    }
    
    /**
     * The registered filter called every time the typo3 backend saves data for $tableName using the backend forms.
     *
     * @param   string  $filterClass   The full name of the class containing the filter
     * @param   string  $filterMethod  The method of the $filterClass to call when the filter is executed
     *
     * @return $this
     */
    public function registerDataHandlerSaveFilter(string $filterClass, string $filterMethod = 'filter')
    {
        $this->dataHandlerActionHandlers[$this->getDataHandlerTableName()]['save'][$filterClass . '-' . $filterMethod]
            = [
            $filterClass,
            $filterMethod,
            $this->getDataHandlerFieldConstraints(),
        ];
        
        return $this;
    }
    
    /**
     * Can be used to remove a previously registered save filter from a table.
     *
     * @param   string  $filterClass   The full name of the class containing the filter
     * @param   string  $filterMethod  The method of the $filterClass which should no longer be called when the filter
     *                                 is executed
     *
     * @return $this
     */
    public function removerDataHandlerSaveFilter(string $filterClass, string $filterMethod = 'filter')
    {
        $this->dataHandlerActionHandlers = Arrays::removePath(
            $this->dataHandlerActionHandlers,
            [$this->getDataHandlerTableName(), 'save', $filterClass . '-' . $filterMethod]
        );
        
        return $this;
    }
    
    /**
     * Returns the list of all registered backend action handlers
     *
     * @return array
     */
    public function __getDataHandlerActionHandlers(): array
    {
        return $this->dataHandlerActionHandlers;
    }
}
