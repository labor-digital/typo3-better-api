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
 * Last modified: 2020.03.19 at 20:30
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\Typo3BetterApi\Event\Events\CoreHookAdapter\DataHandlerActionFilterEventAdapter;
use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Class DataHandlerActionPostProcessorEvent
 *
 * Is triggered when the Typo3 backend performs any kind of record operation.
 * Can be used to handle data after the record action was performed
 *
 * >>This does NOT include the saving of entries!<<
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 * @see     \LaborDigital\Typo3BetterApi\Event\Events\DataHandlerSavePostProcessorEvent
 */
class DataHandlerActionPostProcessorEvent implements CoreHookEventInterface
{
    /**
     * The data handler command that is currently processed
     *
     * @var string
     */
    protected $command;
    
    /**
     * The name of the table that is currently processed
     *
     * @var string
     */
    protected $tableName;
    
    /**
     * The id of the record that is currently processed
     *
     * @var int|string
     */
    protected $id;
    
    /**
     * The new id of the record that is currently processed if it was copied
     *
     * @var int|string
     */
    protected $newId;
    
    /**
     * This is... something (?)
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * This is... something when copying records (?)
     *
     * @var mixed
     */
    protected $pasteSpecialData;
    
    /**
     * This is... something when copying records (?)
     *
     * @var mixed
     */
    protected $pasteDataMap;
    
    /**
     * The instance of the data handler that is currently processing the request
     *
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
     * @param   string                                    $command
     * @param   string                                    $tableName
     * @param                                             $id
     * @param                                             $newId
     * @param                                             $value
     * @param                                             $pasteSpecialData
     * @param                                             $pasteDataMap
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $dataHandler
     */
    public function __construct(
        string $command,
        string $tableName,
        $id,
        $newId,
        $value,
        $pasteSpecialData,
        $pasteDataMap,
        DataHandler $dataHandler
    ) {
        $this->command          = $command;
        $this->tableName        = $tableName;
        $this->id               = $id;
        $this->value            = $value;
        $this->pasteSpecialData = $pasteSpecialData;
        $this->dataHandler      = $dataHandler;
        $this->newId            = $newId;
        $this->pasteDataMap     = $pasteDataMap;
    }
    
    /**
     * Returns the instance of the data handler that is currently processing the request
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getDataHandler(): DataHandler
    {
        return $this->dataHandler;
    }
    
    /**
     * Returns the data handler command that is currently processed
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }
    
    /**
     * Returns the name of the table that is currently processed
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the id of the record that is currently processed
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Returns the new id of the record that is currently processed if it was copied
     *
     * @return int|string
     */
    public function getNewId()
    {
        return $this->newId;
    }
    
    /**
     * (?)
     * @return mixed
     * @todo investigate
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * (?)
     * @return mixed
     * @todo investigate
     */
    public function getPasteSpecialData()
    {
        return $this->pasteSpecialData;
    }
    
    /**
     * (?)
     * @param   mixed  $pasteSpecialData
     *
     * @return DataHandlerActionPostProcessorEvent
     * @todo investigate
     */
    public function setPasteSpecialData($pasteSpecialData): DataHandlerActionPostProcessorEvent
    {
        $this->pasteSpecialData = $pasteSpecialData;
        
        return $this;
    }
    
    /**
     * (?)
     * @return mixed
     * @todo investigate
     */
    public function getPasteDataMap()
    {
        return $this->pasteDataMap;
    }
    
    /**
     * (?)
     * @param   mixed  $pasteDataMap
     *
     * @return DataHandlerActionPostProcessorEvent
     * @todo investigate
     */
    public function setPasteDataMap($pasteDataMap)
    {
        $this->pasteDataMap = $pasteDataMap;
        
        return $this;
    }
}
