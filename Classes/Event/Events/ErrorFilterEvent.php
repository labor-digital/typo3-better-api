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
 * Last modified: 2020.03.20 at 16:46
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Event\Events;

use Throwable;

/**
 * Class ErrorFilterEvent
 *
 * Dispatched when the typo3 exception handler is executed.
 * Can be used to run additional logging actions
 *
 * @package LaborDigital\Typo3BetterApi\Event\Events
 */
class ErrorFilterEvent
{
    /**
     * The error that lead to this event
     *
     * @var \Throwable
     */
    protected $error;
    
    /**
     * Should contain something if you want to block the default exception handler
     *
     * @var mixed|null
     */
    protected $result;
    
    /**
     * ErrorFilterEvent constructor.
     *
     * @param   \Throwable  $error
     * @param               $result
     */
    public function __construct(Throwable $error, $result)
    {
        $this->error  = $error;
        $this->result = $result;
    }
    
    /**
     * Returns the error that lead to this event
     *
     * @return \Throwable
     */
    public function getError(): Throwable
    {
        return $this->error;
    }
    
    /**
     * Returns the result that will be returned by the exception handler
     *
     * @return mixed|null
     */
    public function getResult()
    {
        return $this->result;
    }
    
    /**
     * Sets the result that will be returned by the exception handler
     *
     * @param   mixed|null  $result
     *
     * @return ErrorFilterEvent
     */
    public function setResult($result): ErrorFilterEvent
    {
        $this->result = $result;
        
        return $this;
    }
}
