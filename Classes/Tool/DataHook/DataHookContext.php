<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.21 at 20:48
 */

namespace LaborDigital\T3BA\Tool\DataHook;

use LaborDigital\T3BA\Event\FormEngine\FormFilterEvent;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition;
use LaborDigital\T3BA\Tool\DataHook\Definition\DataHookHandlerDefinition;

class DataHookContext
{
    
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    protected $hookDefinition;
    
    /**
     * @var \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookHandlerDefinition
     */
    protected $handlerDefinition;
    
    /**
     * @var object
     */
    protected $event;
    
    /**
     * True if the data was set  by the hook handler
     *
     * @var bool
     */
    protected $dataWasSet = false;
    
    /**
     * The data set by the hook handler
     *
     * @var mixed
     */
    protected $data;
    
    public function __construct(
        DataHookDefinition $hookDefinition,
        DataHookHandlerDefinition $handlerDefinition,
        object $event
    )
    {
        $this->event = $event;
        $this->hookDefinition = $hookDefinition;
        $this->handlerDefinition = $handlerDefinition;
    }
    
    /**
     * This method returns true if the current context applies to a table,
     * and not a specific field in said table.
     *
     * @return bool
     */
    public function isAppliesToTable(): bool
    {
        return $this->handlerDefinition->appliesToTable;
    }
    
    /**
     * The action that is currently performed in the backend
     *
     * Possible values are:
     *  - save: The element was saved, either a new element or an update to an existing element
     *  - formFilter: Called when the backend is currently rendering the form
     *  - copy: Creates a copy of an existing record
     *  - move: Moves a record to another location
     *  - delete: A record is currently deleted
     *  - undelete: A record is restored from the recycle bin
     *  - localize: ?
     *  - copyToLanguage: ?
     *  - inlineLocalizeSynchronize: ?
     *  - version: ?
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->hookDefinition->type;
    }
    
    /**
     * Returns the name of the table this element is part of
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->hookDefinition->tableName;
    }
    
    /**
     * Returns the uid of the current element's record in the table defined in $tableName
     * If this has a new record's context this will be a string of some sort
     *
     * @return int|string
     */
    public function getUid()
    {
        if (method_exists($this->event, 'getId')) {
            return $this->event->getId();
        }
        
        if (method_exists($this->event, 'getUid')) {
            return $this->event->getUid();
        }
        
        if ($this->event instanceof FormFilterEvent) {
            return $this->event->getData()['databaseRow']['uid'];
        }
        
        return $this->handlerDefinition->data['uid'] ?? 0;
    }
    
    /**
     * Returns true if this is a new record, false if it is already persisted in the database
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return ! is_numeric($this->getUid());
    }
    
    /**
     * Returns the key of either a field, or the name of a table that is currently
     * used by the context. If isAppliesToTable() returns true, this will return the table name,
     * otherwise a field name.
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->handlerDefinition->key;
    }
    
    /**
     * Returns the value for the current element either loaded from the database
     * or given to the dataHandler in the backend save filter
     *
     * If isAppliesToTable() returns true, this will return the table row.
     *
     * @return mixed
     */
    public function getData()
    {
        if ($this->dataWasSet) {
            return $this->data;
        }
        
        return $this->handlerDefinition->appliesToTable ? $this->hookDefinition->data : $this->handlerDefinition->data;
    }
    
    /**
     * Can be used to change the value of this field to anything else.
     *
     * @param   mixed  $value
     *
     * @return DataHookContext
     */
    public function setData($value): self
    {
        $this->dataWasSet = true;
        $this->data = $value;
        
        return $this;
    }
    
    /**
     * Returns the row of the record we are currently working with.
     * This does not have to be the whole row of the record!
     * In the backend save filter this is probably just a part of the record we received
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->hookDefinition->data;
    }
    
    /**
     * Returns he TCA config for this elements table column if isAppliesToTable() returns false,
     * if isAppliesToTable() returns true this will return the whole table TCA
     *
     * @return array
     */
    public function getTca(): array
    {
        return $this->handlerDefinition->tca;
    }
    
    /**
     * Returns the instance of the event, which may contain additional data, that was not handled by this interface
     *
     * @return object
     */
    public function getEvent(): object
    {
        return $this->event;
    }
    
    /**
     * Returns true if the data of this hook was changed AND is now a different value than before
     * -> meaning we have to update it
     *
     * @return bool
     */
    public function isDirty(): bool
    {
        return $this->dataWasSet && $this->data != $this->handlerDefinition->data;
    }
    
    /**
     * Returns the path, inside the current row on which the value is stored
     * This returns an empty array if isAppliesToTable() returns true.
     *
     * @return array
     */
    public function getPath(): array
    {
        return $this->handlerDefinition->path;
    }
    
    /**
     * Returns the data hook definition object if you need access to the root configuration object
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookDefinition
     */
    public function getHookDefinition(): DataHookDefinition
    {
        return $this->hookDefinition;
    }
    
    /**
     * Returns the definition of this context's handler
     *
     * @return \LaborDigital\T3BA\Tool\DataHook\Definition\DataHookHandlerDefinition
     */
    public function getHandlerDefinition(): DataHookHandlerDefinition
    {
        return $this->handlerDefinition;
    }
    
    
}
