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


namespace LaborDigital\T3BA\ExtConfig\Loader;


use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\Interfaces\DiBuildTimeHandlerInterface;
use LaborDigital\T3BA\ExtConfig\Interfaces\DiRunTimeHandlerInterface;
use Neunerlei\Configuration\Finder\FilteredHandlerFinder;

class DiLoader
{
    use ContainerAwareTrait;
    
    /**
     * @var \LaborDigital\T3BA\ExtConfig\ExtConfigService
     */
    protected $extConfigService;
    
    /**
     * DiLoader constructor.
     *
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigService  $extConfigService
     */
    public function __construct(ExtConfigService $extConfigService)
    {
        $this->extConfigService = $extConfigService;
    }
    
    /**
     * Runs the loader on container build time
     */
    public function loadForBuildTime(): void
    {
        $this->runConfigLoader(false);
    }
    
    /**
     * Runs the loader on "runtime" when the container is getting set up
     */
    public function loadForRuntime(): void
    {
        $this->runConfigLoader(true);
    }
    
    /**
     * Internal helper to run the configuration loader
     *
     * @param   bool  $runtime
     */
    protected function runConfigLoader(bool $runtime): void
    {
        $key = $runtime
            ? ExtConfigService::DI_RUN_LOADER_KEY : ExtConfigService::DI_BUILD_LOADER_KEY;
        
        $allowedHandlerInterfaces = $runtime
            ? [DiRunTimeHandlerInterface::class] : [DiBuildTimeHandlerInterface::class];
        
        $loader = $this->extConfigService->makeLoader($key);
        
        if (! $runtime) {
            $loader->setCache(null);
        }
        
        $loader->setHandlerFinder(
            $this->makeInstance(
                FilteredHandlerFinder:: class,
                [
                    [],
                    $allowedHandlerInterfaces,
                ]
            )
        );
        
        $loader->load(true);
    }
}
