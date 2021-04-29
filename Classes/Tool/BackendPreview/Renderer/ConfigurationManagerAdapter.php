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


namespace LaborDigital\T3BA\Tool\BackendPreview\Renderer;


use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\FrontendConfigurationManager;

class ConfigurationManagerAdapter extends ConfigurationManager
{
    /**
     * Runs the given callback while the configuration manager assumes to run in a frontend environment
     *
     * @param   \TYPO3\CMS\Extbase\Configuration\ConfigurationManager  $configurationManager
     * @param   callable                                               $callback
     *
     * @return mixed
     */
    public static function runWithFrontendManager(ConfigurationManager $configurationManager, callable $callback)
    {
        $concreteBackup = $configurationManager->concreteConfigurationManager;
        try {
            if (! $concreteBackup instanceof FrontendConfigurationManager) {
                $objectManager = $configurationManager->objectManager;
                $configurationManager->concreteConfigurationManager
                    = $objectManager->get(FrontendConfigurationManager::class);
            }
            
            return $callback();
        } finally {
            $configurationManager->concreteConfigurationManager = $concreteBackup;
        }
    }
}
