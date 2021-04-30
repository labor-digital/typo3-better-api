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
 * Last modified: 2021.04.30 at 15:39
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Log;

use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use Throwable;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogRecord;
use TYPO3\CMS\Core\Log\Writer\AbstractWriter;
use TYPO3\CMS\Core\Log\Writer\WriterInterface;

/**
 * Class BeLogWriter
 *
 * This log writer is a hybrid of the DatabaseWriter of the PSR-3 logging implementation,
 * and the old-school $GLOBALS['BE_USER']->writelog() logger. It writes the log entries always in the
 * sys_log table, but fills the field sets of both implementations, while doing so.
 *
 * @package LaborDigital\T3BA\Tool\Log
 */
class BeLogWriter extends AbstractWriter
{
    use ContainerAwareTrait;
    
    /**
     * Table to write the log records to.
     *
     * @var string
     */
    protected $logTable = 'sys_log';
    
    /**
     * Holds the resolved backend user instance
     *
     * @var BackendUserAuthentication|null
     */
    protected $resolvedUser;
    
    /**
     * Internal helper to build the list of database fields for the given log record
     *
     * @param   \TYPO3\CMS\Core\Log\LogRecord  $record
     *
     * @return array
     */
    protected function buildFieldValues(LogRecord $record): array
    {
        $data = '';
        $recordData = $record->getData();
        if (! empty($recordData)) {
            // According to PSR3 the exception-key may hold an \Exception
            // Since json_encode() does not encode an exception, we run the _toString() here
            if (isset($recordData['exception']) && $recordData['exception'] instanceof Throwable) {
                $recordData['exception'] = (string)$recordData['exception'];
            }
            
            /** @noinspection JsonEncodingApiUsageInspection */
            $data = @json_encode($recordData);
            if (! is_string($data)) {
                $data = '[]';
            }
        }
        
        $psr3 = [
            'request_id' => $record->getRequestId(),
            'time_micro' => $record->getCreated(),
            'component' => $record->getComponent(),
            'level' => $record->getLevel(),
            'message' => $record->getMessage(),
            'data' => $data,
        ];
        
        $errorLevel = 0;
        if ($record->getLevel() <= LogLevel::WARNING) {
            $errorLevel = 1;
        }
        if ($record->getLevel() <= LogLevel::ERROR) {
            $errorLevel = 2;
        }
        
        $legacy = [
            'userid' => 0,
            'tstamp' => floor($record->getCreated()),
            'error' => $errorLevel,
            'type' => 4,
            'action' => 0,
            'details_nr' => 0,
            'details' => str_replace('%', '&percnt;', $record->getMessage()),
        ];
        
        if ($recordData['tablename']) {
            $legacy['tablename'] = $recordData['tablename'];
        }
        if ($recordData['uid']) {
            $legacy['recuid'] = $recordData['uid'];
        }
        
        try {
            $legacy['event_pid'] = $this->cs()->typoContext->Pid()->getCurrent();
        } catch (Throwable $e) {
        }
        
        $user = $this->getBackendUser();
        if ($user) {
            $legacy['workspace'] = $user->workspace;
            if (! empty($user->user['uid'])) {
                $legacy['userid'] = $user->user['uid'];
            }
            
            if (! empty($user->user['ses_backuserid'])) {
                $recordData['originalUser'] = $user->user['ses_backuserid'];
            }
        }
        
        return array_merge($psr3, $legacy);
    }
    
    /**
     * Writes the log record
     *
     * @param   LogRecord  $record  Log record
     *
     * @return \TYPO3\CMS\Core\Log\Writer\WriterInterface $this
     */
    public function writeLog(LogRecord $record): WriterInterface
    {
        $this->cs()->db->getConnectionPool()
                       ->getConnectionForTable($this->logTable)
                       ->insert($this->logTable, $this->buildFieldValues($record));
        
        return $this;
    }
    
    /**
     * Returns the instance of the backend user ur null if there is none
     *
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication|null
     */
    protected function getBackendUser(): ?BackendUserAuthentication
    {
        if ($this->resolvedUser) {
            return $this->resolvedUser;
        }
        
        try {
            return $this->resolvedUser = $this->cs()->typoContext->beUser()->getUser();
        } catch (Throwable $exception) {
            return null;
        }
    }
}
