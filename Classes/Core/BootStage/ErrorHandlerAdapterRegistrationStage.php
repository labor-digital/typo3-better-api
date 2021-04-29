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
 * Last modified: 2020.08.23 at 12:49
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Core\BootStage;


use LaborDigital\T3BA\Core\ErrorHandler\DebugExceptionHandler;
use LaborDigital\T3BA\Core\ErrorHandler\ProductionExceptionHandler;
use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\Core\Kernel;
use LaborDigital\T3BA\Event\BootstrapInitializesErrorHandlingEvent;
use LaborDigital\T3BA\Event\Core\ExtLocalConfLoadedEvent;

class ErrorHandlerAdapterRegistrationStage implements BootStageInterface
{
    /**
     * @inheritDoc
     */
    public function prepare(TypoEventBus $eventBus, Kernel $kernel): void
    {
        $eventBus->addListener(BootstrapInitializesErrorHandlingEvent::class,
            function () { $this->registerErrorHandlerAdapter(); });
        
        $eventBus->addListener(ExtLocalConfLoadedEvent::class,
            function () { $this->registerErrorHandlerAdapter(); },
            ['priority' => -500]);
    }
    
    /**
     * Registers our error handling adapter in the global configuration
     */
    protected function registerErrorHandlerAdapter(): void
    {
        // Register production exception handler
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']
            !== ProductionExceptionHandler::class) {
            ProductionExceptionHandler::setDefaultExceptionHandler(
                (string)$GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['productionExceptionHandler']
                = ProductionExceptionHandler::class;
        }
        
        // Register debug exception handler
        if ($GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']
            !== DebugExceptionHandler::class) {
            DebugExceptionHandler::setDefaultExceptionHandler(
                (string)$GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']);
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['debugExceptionHandler']
                = DebugExceptionHandler::class;
        }
    }
}
