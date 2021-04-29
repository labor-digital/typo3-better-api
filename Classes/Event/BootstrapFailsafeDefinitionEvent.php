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
 * Last modified: 2020.10.19 at 23:18
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Event;

/**
 * Class BootstrapFailsafeDefinitionEvent
 *
 * This event informs all it's listeners about the failsafe state of the current
 * typo3 installation.
 *
 * @package LaborDigital\T3BA\Core\Event
 */
class BootstrapFailsafeDefinitionEvent
{
    /**
     * True if the app is running in failsafe mode, false if not
     *
     * @var bool
     */
    protected $failsafe = false;
    
    /**
     * BootstrapFailsafeDefinition constructor.
     *
     * @param   bool  $failsafe
     */
    public function __construct(bool $failsafe)
    {
        $this->failsafe = $failsafe;
    }
    
    /**
     * Returns true if the bootstrap is running in failsafe mode
     *
     * @return bool
     */
    public function isFailsafe(): bool
    {
        return $this->failsafe;
    }
}
