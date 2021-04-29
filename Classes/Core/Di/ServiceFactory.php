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


namespace LaborDigital\T3BA\Core\Di;


use LaborDigital\T3BA\Core\EventBus\TypoEventBus;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\ExtConfig\Loader\DiLoader;
use LaborDigital\T3BA\ExtConfig\Loader\MainLoader;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Class ServiceFactory
 *
 * Factory to create service instances in the dependency injection container
 *
 * @package LaborDigital\T3BA\Core\DependencyInjection
 */
class ServiceFactory
{
    
    public static function getListenerProvider(): ListenerProviderInterface
    {
        return TypoEventBus::getInstance()->getConcreteListenerProvider();
    }
    
    public static function getMainExtConfigLoader(ContainerInterface $container): MainLoader
    {
        return $container->get(ExtConfigService::class)->getMainLoader();
    }
    
    public static function getDiConfigLoader(ContainerInterface $container): DiLoader
    {
        return $container->get(ExtConfigService::class)->getDiLoader();
    }
    
    public static function getExtConfigContext(ContainerInterface $container): ExtConfigContext
    {
        return $container->get(ExtConfigService::class)->getContext();
    }
}
