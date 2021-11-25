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
 * Last modified: 2021.11.19 at 18:08
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfig\Interfaces;


use Neunerlei\Configuration\State\ConfigState;

/**
 * Can be added to a class that implements SiteBasedHandlerInterface
 * It allows the handler to perform modification on the global state before and AFTER
 * the handler is/was executed
 *
 * @see \LaborDigital\T3ba\ExtConfig\Interfaces\SiteBasedHandlerInterface
 */
interface ExtendedSiteBasedHandlerInterface
{
    /**
     * Executed before any handler is executed, receives the global config state
     * in order to process them before the site-based state and context take over.
     *
     * This method will be executed BEFORE prepare(), it will even be executed if there
     * are NO config classes available!
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function prepareSiteBasedConfig(ConfigState $state): void;
    
    /**
     * Executed after ALL sites have been processed and the handler injected their
     * values into the state object. This method allows you to perform last-minute
     * changes on the main state object that need be executed only once.
     *
     * This method will be executed AFTER finish(), it will even be executed if there
     * are NO config classes available!
     */
    public function finishSiteBasedConfig(ConfigState $state): void;
}