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
 * Last modified: 2021.12.12 at 23:06
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;

/**
 * Used to configure a provider for the "lowLevel" extensions "configuration" module through plain PHP
 *
 * Location: EXT:ExtKey\Vendor\Classes\ConfigurationModule\ProviderName
 * Executed: At DI Container build
 *
 * @see \TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\AbstractProvider
 * @see \TYPO3\CMS\Lowlevel\ConfigurationModuleProvider\ProviderInterface
 */
interface ConfigureConfigurationModuleProviderInterface
{
    /**
     * MUST return a unique, not empty identifier that will be used for the configuration module provider
     *
     * @return string
     */
    public static function getProviderIdentifier(): string;
    
    /**
     * Configure the class as configuration provider and allows additional configuration.
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ConfigurationModule\ProviderConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                 $context
     */
    public static function configureProvider(ProviderConfigurator $configurator, ExtConfigContext $context): void;
}