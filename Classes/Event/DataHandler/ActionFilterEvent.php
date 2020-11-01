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

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerActionFilterEvent
 *
 * Is triggered when the Typo3 backend performs any kind of record operation using the data handler.
 * Can be used to change the action before it is executed
 *
 * >>This does NOT include the saving of entries!<<
 *
 * @package LaborDigital\T3BA\Event\Events
 * @see     \LaborDigital\T3BA\Event\DataHandler\SaveFilterEvent
 */
class ActionFilterEvent extends AbstractActionEvent
{
    /**
     * DataHandlerActionFilterEvent constructor.
     *
     * @param   string                                    $command
     * @param   string                                    $tableName
     * @param                                             $id
     * @param                                             $value
     * @param                                             $pasteSpecialData
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     */
    public function __construct(
        string $command,
        string $tableName,
        $id,
        $value,
        $pasteSpecialData,
        DataHandler $dataHandler
    ) {
        $this->command          = $command;
        $this->tableName        = $tableName;
        $this->id               = $id;
        $this->value            = $value;
        $this->pasteSpecialData = $pasteSpecialData;
        $this->dataHandler      = $dataHandler;
    }

    /**
     * Can be used to update the data handler command that is currently processed
     *
     * @param   string  $command
     *
     * @return ActionFilterEvent
     */
    public function setCommand(string $command): ActionFilterEvent
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Updates the name of the table that is currently processed
     *
     * @param   string  $tableName
     *
     * @return ActionFilterEvent
     */
    public function setTableName(string $tableName): ActionFilterEvent
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Updates the id of the record that is currently processed
     *
     * @param   int|string  $id
     *
     * @return ActionFilterEvent
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * (?) @param   mixed  $value
     *
     * @return ActionFilterEvent
     * @todo investigate
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }
}
