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
 * Last modified: 2021.02.02 at 13:02
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Event\Configuration;


class ExtBasePersistenceRegistrationEvent
{
    /**
     * The extbase persistence mapping in a format that matches the TYPO3 requirements
     *
     * @var array
     * @see https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html#migration
     */
    protected $classes = [];
    
    /**
     * Returns the extbase persistence mapping
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
    
    /**
     * Allows you to update the extbase persistence mapping
     *
     * @param   array  $classes
     *
     * @return ExtBasePersistenceRegistrationEvent
     */
    public function setClasses(array $classes): ExtBasePersistenceRegistrationEvent
    {
        $this->classes = $classes;
        
        return $this;
    }
}
