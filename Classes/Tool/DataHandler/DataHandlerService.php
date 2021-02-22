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
 * Last modified: 2020.09.08 at 17:37
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\DataHandler;


use LaborDigital\T3BA\Core\Di\PublicServiceInterface;
use LaborDigital\T3BA\Tool\DataHandler\Record\RecordDataHandler;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use Throwable;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerService implements PublicServiceInterface, SingletonInterface
{
    /**
     * Returns a new, empty data handler instance
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getEmptyDataHandler(): DataHandler
    {
        return GeneralUtility::makeInstance(DataHandler::class);
    }

    /**
     * Returns a simple abstraction to perform record actions using the data handler in an object oriented way
     *
     * @param   string  $tableName  The name of the database table to get the record handler for
     *
     * @return \LaborDigital\T3BA\Tool\DataHandler\Record\RecordDataHandler
     * @see RecordDataHandler
     */
    public function getRecordDataHandler(string $tableName): RecordDataHandler
    {
        return GeneralUtility::makeInstance(
            RecordDataHandler::class, NamingUtil::resolveTableName($tableName), $this
        );
    }

    /**
     * Shortcut to run process_datamap() on a fresh data handler instance.
     *
     * Occurring errors will throw a DataHandlerException
     *
     * @param   array  $data      The data array to process
     * @param   array  $commands  The commands to process
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @see DataHandler::process_datamap()
     */
    public function processData(array $data, array $commands = []): DataHandler
    {
        return $this->doProcessing($data, $commands, true);
    }

    /**
     * Shortcut to run process_cmdmap() on a fresh data handler instance.
     *
     * Occurring errors will throw a DataHandlerException
     *
     * @param   array  $commands  The commands to process
     * @param   array  $data      Optional data if required for your commands
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @see DataHandler::process_cmdmap()
     */
    public function processCommands(array $commands, array $data = []): DataHandler
    {
        return $this->doProcessing($data, $commands, false);
    }

    /**
     * Internal handler to do the processing on a fresh data handler instance
     *
     * @param   array  $data        The data array
     * @param   array  $commands    the cmd array
     * @param   bool   $handleData  True if process_datamap() should be executed, false for process_cmdmap()
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @throws \LaborDigital\T3BA\Tool\DataHandler\DataHandlerException
     */
    protected function doProcessing(array $data, array $commands, bool $handleData): DataHandler
    {
        $handler = $this->getEmptyDataHandler();
        try {
            $handler->errorLog = [];
            $handler->start($data, $commands);
            if ($handleData) {
                $handler->process_datamap();
            } else {
                $handler->process_cmdmap();
            }
        } catch (Throwable $e) {
            throw DataHandlerException::makeNewInstance($handler, $e);
        }

        if (! empty($handler->errorLog)) {
            throw DataHandlerException::makeNewInstance($handler, null);
        }

        return $handler;
    }

}
