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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ConfigurePluginBuilder
{
    /**
     * Generates the arguments that are required to register the plugin/content element in the TYPO3 api
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     * @param   string                                                                          $type
     *
     * @return array
     */
    public static function build(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context,
        string $type
    ): array
    {
        $extensionName = NamingUtil::extensionNameFromExtKey($context->getExtKey());
        
        /** @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin() */
        $config = array_values([
            'extensionName' => $extensionName,
            'pluginName' => $configurator->getPluginName(),
            'controllerActions' => $configurator->getActions(),
            'nonCacheableControllerActions' => $configurator->getNoCacheActions(),
            'pluginType' => $type,
        ]);
        
        // We simulate a bit of ExtensionUtility::configurePlugin() here,
        // because NamingUtil::pluginNameFromControllerAction relies on the information
        // about plugins to be available in the globals array. To use the method inside the extConfig
        // runtime we have to forcefully inject the information into the globals, even if the data
        // will be overwritten later, when configurePlugin() is executed.
        // This is only a runtime fix while the config is gathered and has no effect after the config was cached.
        foreach ($configurator->getActions() as $controllerClass => $actions) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions'][$extensionName]
            ['plugins'][$configurator->getPluginName()]['controllers'][$controllerClass]
                = [
                'className' => $controllerClass,
                'alias' => ExtensionUtility::resolveControllerAliasFromControllerClassName($controllerClass),
                'actions' => Arrays::makeFromStringList($actions),
            ];
        }
        
        return $config;
    }
}