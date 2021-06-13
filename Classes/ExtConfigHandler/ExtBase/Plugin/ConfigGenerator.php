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
 * Last modified: 2021.06.13 at 20:14
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Plugin;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\FluidTemplateBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\IconBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\AbstractConfigGenerator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\BackendPreviewBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\ConfigurePluginBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\TsBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\TsConfigBuilder;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\Builder\Type\FlexForm\Io\Dumper;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ConfigGenerator extends AbstractConfigGenerator
{
    /**
     * @inheritDoc
     */
    public function generateForVariant(AbstractElementConfigurator $configurator, ExtConfigContext $context, ?string $variantName): void
    {
        /** @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator $configurator */
        $signature = $configurator->getSignature();
        
        $config = $this->config;
        
        $config->typoScript[] = FluidTemplateBuilder::build('plugin', $signature, $configurator);
        $config->typoScript[] = TsBuilder::build($signature, $configurator);
        $config->configureArgs[] = ConfigurePluginBuilder::build(
            $configurator, $context, ExtensionUtility::PLUGIN_TYPE_PLUGIN);
        
        $renderers = BackendPreviewBuilder::buildRendererList($configurator);
        $config->backendPreviewRenderers
            = BackendPreviewBuilder::mergeRendererList($config->backendPreviewRenderers, $renderers);
        $config->backendPreviewHooks
            = BackendPreviewBuilder::addHookToList($config->backendPreviewHooks, 'list', $signature);
        
        $config->dataHooks = array_merge($config->dataHooks, $configurator->getRegisteredDataHooks());
        
        $config->iconArgs[] = IconBuilder::buildIconRegistrationArgs($configurator, $context);
        $config->flexFormArgs[] = $this->buildFlexFormArgs($configurator, $context);
        $config->registrationArgs['plugin'][] = $this->buildRegistrationArgs($configurator, $context);
        $config->tsConfig[] = TsConfigBuilder::buildNewCeWizardConfig(
            $configurator, $context, $signature, 'CType = list' . PHP_EOL . 'list_type = ' . $signature);
        
        $config->variantMap[$signature] = $variantName;
        
    }
    
    /**
     * Generates the arguments for the registration as an extbase plugin
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     *
     * @return array
     * @see \LaborDigital\T3ba\Core\Util\CTypeRegistrationTrait::registerCTypesForElements()
     */
    protected function buildRegistrationArgs(PluginConfigurator $configurator, ExtConfigContext $context): array
    {
        return array_values([
            'extensionName' => NamingUtil::extensionNameFromExtKey($context->getExtKey()),
            'pluginName' => $configurator->getPluginName(),
            'pluginTitle' => $configurator->getTitle(),
            'pluginIconPathAndFilename' => IconBuilder::buildIconIdentifier($configurator, $context),
        ]);
    }
    
    /**
     * Builds the arguments to register a possibly configured flex form for the plugin
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                          $context
     *
     * @return array|null
     */
    protected function buildFlexFormArgs(PluginConfigurator $configurator, ExtConfigContext $context): ?array
    {
        if (! $configurator->hasFlexForm()) {
            return null;
        }
        
        $flexFormFile = $context->getTypoContext()->di()->getService(Dumper::class)
                                ->dumpToFile($configurator->getFlexForm());
        
        return [
            'signature' => $configurator->getSignature(),
            'args' => array_values([
                'piKeyToMatch' => $configurator->getSignature(),
                'value' => 'FILE:' . $flexFormFile,
                'CTypeToMatch' => 'list',
            ]),
        ];
    }
}
