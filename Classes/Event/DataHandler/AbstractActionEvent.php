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
 * Last modified: 2020.10.19 at 12:25
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\DataHandler;


use LaborDigital\T3BA\Event\CoreHookAdapter\CoreHookEventInterface;
use LaborDigital\T3BA\Event\DataHandler\Adapter\ActionEventAdapter;
use TYPO3\CMS\Core\DataHandling\DataHandler;

abstract class AbstractActionEvent implements CoreHookEventInterface
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
     * Contains either the value for a command or the target uid when a record is copied
     *
     * @var mixed
     */
    protected $value;

    /**
     * Contains the data fields to update when a record is copied
     *
     * @var mixed
     */
    protected $pasteSpecialData;

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
        return ActionEventAdapter::class;
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
     * Returns either the value for a command or the target uid when a record is copied
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the data fields to update when a record is copied
     *
     * @return array
     */
    public function getPasteSpecialData()
    {
        return $this->pasteSpecialData;
    }

    /**
     * Sets the data fields to update when a record is copied
     *
     * @param   mixed  $pasteSpecialData
     *
     * @return $this
     */
    public function setPasteSpecialData(array $pasteSpecialData): self
    {
        $this->pasteSpecialData = $pasteSpecialData;

        return $this;
    }
}
