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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\DataHandler;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Throwable;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class DataHandlerService implements PublicServiceInterface, SingletonInterface
{
    use ContainerAwareTrait;
    
    /**
     * Returns a new, empty data handler instance
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getEmptyDataHandler(): DataHandler
    {
        return $this->makeInstance(DataHandler::class);
    }
    
    /**
     * Returns a simple abstraction to perform record actions using the data handler in an object oriented way
     *
     * @param   string  $tableName  The name of the database table to get the record handler for
     *
     * @return \LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler
     * @see RecordDataHandler
     */
    public function getRecordDataHandler(string $tableName): RecordDataHandler
    {
        return $this->makeInstance(
            RecordDataHandler::class, [NamingUtil::resolveTableName($tableName), $this]
        );
    }
    
    /**
     * Shortcut to run process_datamap() on a fresh data handler instance.
     *
     * Occurring errors will throw a DataHandlerException
     *
     * @param   array      $data      The data array to process
     * @param   array      $commands  The commands to process
     * @param   bool|null  $force     True to force the execution as admin user
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @see DataHandler::process_datamap()
     */
    public function processData(array $data, array $commands = [], ?bool $force = null): DataHandler
    {
        return $this->doProcessing($data, $commands, true, $force);
    }
    
    /**
     * Shortcut to run process_cmdmap() on a fresh data handler instance.
     *
     * Occurring errors will throw a DataHandlerException
     *
     * @param   array      $commands  The commands to process
     * @param   array      $data      Optional data if required for your commands
     * @param   bool|null  $force     True to force the execution as admin user
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @see DataHandler::process_cmdmap()
     */
    public function processCommands(array $commands, array $data = [], ?bool $force = null): DataHandler
    {
        return $this->doProcessing($data, $commands, false, $force);
    }
    
    /**
     * Internal handler to do the processing on a fresh data handler instance
     *
     * @param   array      $data        The data array
     * @param   array      $commands    the cmd array
     * @param   bool       $handleData  True if process_datamap() should be executed, false for process_cmdmap()
     * @param   bool|null  $force       True to force the execution as admin user
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     * @throws \LaborDigital\T3ba\Tool\DataHandler\DataHandlerException
     */
    protected function doProcessing(array $data, array $commands, bool $handleData, ?bool $force = null): DataHandler
    {
        // This is a hotfix, to automatically initialize the backend user if we are running in
        // cli mode, otherwise the user has to do that for every command class.
        $context = $this->cs()->typoContext;
        if ($context->env()->isCli() && empty($context->beUser()->getUser()->user)) {
            $context->beUser()->getUser()->backendCheckLogin();
        }
        
        return $this->forceWrapper(function () use ($data, $commands, $handleData) {
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
        }, (bool)$force);
    }
    
    /**
     * Internal helper to run the given callback either as forced user or as the current user
     *
     * @param   callable  $callback  The callback to execute
     * @param   bool      $force     True to run as a forced admin user
     *
     * @return mixed
     */
    protected function forceWrapper(callable $callback, bool $force)
    {
        if (! $force) {
            return $callback();
        }
        
        return $this->cs()->simulator->runWithEnvironment(['asAdmin'], $callback);
    }
    
}
