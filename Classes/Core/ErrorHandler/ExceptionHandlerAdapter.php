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

namespace LaborDigital\T3BA\Core\ErrorHandler;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Exception\T3BAException;
use LaborDigital\T3BA\Event\Core\ErrorFilterEvent;
use Throwable;
use TYPO3\CMS\Core\Error\ExceptionHandlerInterface;
use TYPO3\CMS\Core\Error\ProductionExceptionHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExceptionHandlerAdapter extends ProductionExceptionHandler
{
    /**
     * The name of the registered exception handler
     *
     * @var string
     */
    protected static $defaultExceptionHandler;
    
    /**
     * The instance of the registered default exception handler
     *
     * @var ExceptionHandlerInterface
     */
    protected $defaultExceptionHandlerInstance;
    
    /**
     * @inheritDoc
     * @throws \LaborDigital\T3BA\Core\Exception\T3BAException
     */
    public function __construct()
    {
        if (empty(static::$defaultExceptionHandler)) {
            throw new T3BAException(
                'Could not create instance of: ' . static::class
                . ' because no default exception handler was registered!');
        }
        $this->defaultExceptionHandlerInstance = GeneralUtility::makeInstance(static::$defaultExceptionHandler);
        
        // Disable the child exception handler's handling -> We will take care of that
        restore_exception_handler();
        
        // Register myself as real exception handler
        parent::__construct();
    }
    
    /**
     * @inheritDoc
     */
    public function handleException(Throwable $exception)
    {
        TypoEventBus::getInstance()->dispatch(($e = new ErrorFilterEvent($exception, null)));
        
        return $e->getResult() ?? $this->defaultExceptionHandlerInstance->handleException($exception);
    }
    
    /**
     * @inheritDoc
     */
    public function echoExceptionWeb(Throwable $exception)
    {
        return $this->defaultExceptionHandlerInstance->handleException($exception);
    }
    
    /**
     * @inheritDoc
     */
    public function echoExceptionCLI(Throwable $exception)
    {
        return $this->defaultExceptionHandlerInstance->handleException($exception);
    }
    
    /**
     * Internal helper to inject the default exception handler class
     *
     * @param   string  $defaultExceptionHandler
     */
    public static function setDefaultExceptionHandler(string $defaultExceptionHandler): void
    {
        static::$defaultExceptionHandler = $defaultExceptionHandler;
    }
}
