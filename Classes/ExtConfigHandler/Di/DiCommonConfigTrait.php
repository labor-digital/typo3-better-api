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
 * Last modified: 2021.04.29 at 09:53
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Di;


use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

trait DiCommonConfigTrait
{
    /**
     * Creates a new pseudo service called "cache.$cacheIdentifier" that can be used in your container configuration.
     *
     * @param   ContainerConfigurator  $configurator
     * @param   string                 $cacheIdentifier
     */
    protected static function registerCache(ContainerConfigurator $configurator, string $cacheIdentifier): void
    {
        $services = $configurator->services();
        
        $services->set('cache.' . $cacheIdentifier)
                 ->class(FrontendInterface::class)
                 ->factory([service(CacheManager::class), 'getCache'])
                 ->args([$cacheIdentifier]);
    }
}
