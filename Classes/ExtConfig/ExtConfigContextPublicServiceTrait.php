<?php
declare(strict_types=1);
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
 * Last modified: 2020.05.25 at 10:09
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig;


use LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator;
use LaborDigital\Typo3BetterApi\Container\CommonServiceDependencyTrait;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService;
use LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry;
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;

trait ExtConfigContextPublicServiceTrait
{
    use CommonServiceDependencyTrait {
        TypoContext as public;
        Db as public;
        Links as public;
        Tsfe as public;
        Page as public;
        FalFiles as public;
        EventBus as public;
        Translation as public;
        Simulator as public;
        getInstanceOf as public;
        getService as public;
    }
    
    /**
     * Returns the sql registry to build dynamic sql files
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\TableSqlGenerator
     */
    public function SqlGenerator(): TableSqlGenerator
    {
        return $this->getService(TableSqlGenerator::class);
    }
    
    /**
     * Returns the registry to add and remove data handler action handlers
     *
     * @return \LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionService
     */
    public function DataHandlerActions(): DataHandlerActionService
    {
        return $this->getService(DataHandlerActionService::class);
    }
    
    /**
     * Returns the file abstraction registry to write temporary files
     *
     * @return \LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs
     */
    public function Fs(): TempFs
    {
        return $this->getService(TempFs::class);
    }
    
    /**
     * Legacy helper to get the extension registry
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Extension\ExtConfigExtensionRegistry
     * @deprecated will be removed in v10
     */
    public function ExtensionRegistry(): ExtConfigExtensionRegistry
    {
        return $this->getService(ExtConfigExtensionRegistry::class);
    }
}
