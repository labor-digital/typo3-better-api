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
 * Last modified: 2020.03.19 at 20:27
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\DataHandlerActionFilterEventAdapter;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerActionFilterEvent
 *
 * Is triggered when the Typo3 backend performs any kind of record operation using the data handler.
 * Can be used to change the action before it is executed
 *
 * >>This does NOT include the saving of entries!<<
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 * @see     \LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSaveFilterEvent
 */
class DataHandlerActionFilterEvent implements CoreHookEventInterface
{
    
    /**
     * The data handler command that is currently processed
     * @var string
     */
    protected $command;
    
    /**
     * The name of the table that is currently processed
     * @var string
     */
    protected $tableName;
    
    /**
     * The id of the record that is currently processed
     * @var int|string
     */
    protected $id;
    
    /**
     * This is... something (?)
     * @var mixed
     */
    protected $value;
    
    /**
     * This is... something when copying records (?)
     * @var mixed
     */
    protected $pasteSpecialData;
    
    /**
     * The instance of the data handler that is currently processing the request
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;
    
    /**
     * @inheritDoc
     */
    public static function getAdapterClass(): string
    {
        return DataHandlerActionFilterEventAdapter::class;
    }
    
    /**
     * DataHandlerActionFilterEvent constructor.
     *
     * @param string                                   $command
     * @param string                                   $tableName
     * @param                                          $id
     * @param                                          $value
     * @param                                          $pasteSpecialData
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     */
    public function __construct(string $command, string $tableName, $id, $value, $pasteSpecialData, DataHandler $dataHandler)
    {
        $this->command = $command;
        $this->tableName = $tableName;
        $this->id = $id;
        $this->value = $value;
        $this->pasteSpecialData = $pasteSpecialData;
        $this->dataHandler = $dataHandler;
    }
    
    /**
     * Returns the instance of the data handler that is currently processing the request
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
    
    /**
     * Returns the data handler command that is currently processed
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
    
    /**
     * Can be used to update the data handler command that is currently processed
     *
     * @param string $command
     *
     * @return DataHandlerActionFilterEvent
     */
    public function setCommand(string $command): DataHandlerActionFilterEvent
    {
        $this->command = $command;
        return $this;
    }
    
    /**
     * Returns the name of the table that is currently processed
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Updates the name of the table that is currently processed
     *
     * @param string $tableName
     *
     * @return DataHandlerActionFilterEvent
     */
    public function setTableName(string $tableName): DataHandlerActionFilterEvent
    {
        $this->tableName = $tableName;
        return $this;
    }
    
    /**
     * Returns the id of the record that is currently processed
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Updates the id of the record that is currently processed
     *
     * @param int|string $id
     *
     * @return DataHandlerActionFilterEvent
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * (?) @return mixed
     * @todo investigate
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * (?) @param mixed $value
     *
     * @return DataHandlerActionFilterEvent
     * @todo investigate
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * (?) @return mixed
     * @todo investigate
     */
    public function getPasteSpecialData()
    {
        return $this->pasteSpecialData;
    }
    
    /**
     * (?) @param mixed $pasteSpecialData
     *
     * @return DataHandlerActionFilterEvent
     * @todo investigate
     */
    public function setPasteSpecialData($pasteSpecialData)
    {
        $this->pasteSpecialData = $pasteSpecialData;
        return $this;
    }
}
