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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\DataHandler;


use LaborDigital\T3ba\Core\Exception\T3baException;
use Throwable;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class DataHandlerException extends T3baException
{
    /**
     * The data handler that threw the exception
     *
     * @var DataHandler
     */
    protected $handler;
    
    /**
     * Returns the data handler that threw the exception
     *
     * @return \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    public function getHandler(): DataHandler
    {
        return $this->handler;
    }
    
    /**
     * Returns the list of errors that occurred
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->handler->errorLog;
    }
    
    /**
     * Creates a new instance of this exception
     *
     * @param   \TYPO3\CMS\Core\DataHandling\DataHandler  $handler
     * @param   \Throwable|null                           $previous
     *
     * @return static
     */
    public static function makeNewInstance(DataHandler $handler, ?Throwable $previous): self
    {
        $message = 'There were errors while running the data handler!';
        if (! empty($handler->errorLog)) {
            $message .= ' Errors: ';
            foreach ($handler->errorLog as $error) {
                $message .= PHP_EOL . $error;
            }
        }
        
        if ($previous !== null) {
            $message .= PHP_EOL . $previous->getMessage();
        }
        
        $i = new static($message, 1599580792, $previous);
        $i->handler = $handler;
        
        return $i;
    }
}
