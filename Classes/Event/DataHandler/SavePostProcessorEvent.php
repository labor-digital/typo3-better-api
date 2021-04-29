<?php
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event\DataHandler;

/**
 * Class SavePostProcessorEvent
 *
 * Is triggered when the Typo3 backend saves any kind of record to the database using the data handler.
 * Can be used to handle data after the record was stored in the database
 *
 * @package LaborDigital\T3BA\Event\DataHandler
 */
class SavePostProcessorEvent extends AbstractLateSaveEvent
{
    /**
     * The id of the entry that is saved.
     * May be the numeric id or a string with "NEW_..." at the beginning
     *
     * @var string|int
     */
    protected $id;
    
    /**
     * Returns the id of the entry that is saved
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Updates the id of the entry that is saved.
     *
     * @param   int|string  $id
     *
     * @return self
     */
    public function setId($id): self
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * Updates the row that was given by to the data handler
     *
     * @param   array  $row
     *
     * @return self
     */
    public function setRow(array $row): self
    {
        $this->row = $row;
        
        return $this;
    }
    
}
