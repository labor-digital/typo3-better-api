<?php
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
 * Last modified: 2020.03.21 at 20:54
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedStackGeneratorInterface;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\AbstractConfigGenerator;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\ElementConfig;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class PluginConfigGenerator extends AbstractConfigGenerator implements CachedStackGeneratorInterface
{
    
    /**
     * @inheritDoc
     */
    public function generate(array $stack, ExtConfigContext $context, array $additionalData, $option)
    {
        // Prepare temporary storage
        $tmp = new class
        {
            public $typoScript                = [];
            public $tsConfig                  = [];
            public $configurePluginArgs       = [];
            public $registerPluginArgs        = [];
            public $addPiFlexFormArgs         = [];
            public $flexFormPlugins           = [];
            public $iconDefinitionArgs        = [];
            public $backendPreviewRenderers   = [];
            public $backendListLabelRenderers = [];
            public $dataHandlerActionHandlers = [];
            public $cTypeEntries              = [];
        };
        
        // Loop through the stack
        foreach ($stack as $pluginName => $data) {
            $context->runWithFirstCachedValueDataScope($data, function () use ($data, $context, $tmp, $pluginName) {
                // Create the configurator
                /** @var \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator $configurator */
                $configurator = $context->getInstanceOf(PluginConfigurator::class, [$pluginName, $context]);
                
                // Loop through the stack
                $context->runWithCachedValueDataScope($data,
                    function (string $configClass) use ($context, $configurator, $pluginName) {
                        if (! in_array(PluginConfigurationInterface::class, class_implements($configClass))) {
                            throw new ExtConfigException("Invalid configuration class: $configClass for plugin: $pluginName. It has to implement the correct interface: "
                                                         . PluginConfigurationInterface::class);
                        }
                        call_user_func([$configClass, 'configurePlugin'], $configurator, $context);
                    });
                
                // Build the parts
                $tmp->typoScript[] = $this->makeTemplateDefinition($configurator->getType(), $configurator);
                $tmp->typoScript[] = $configurator->getAdditionalTypoScript();
                if ($configurator->getType() === 'plugin') {
                    $tmp->registerPluginArgs[] = $this->makeRegisterPluginArgs($configurator, $context);
                } else {
                    $tmp->cTypeEntries[] = $this->makeRegisterCTypeElement($configurator, $context);
                }
                $tmp->configurePluginArgs[] = $this->makeConfigurePluginArgs($configurator, $context);
                $tmp->addPiFlexFormArgs[]   = $this->makeAddPiFlexFormArgs($configurator, $tmp);
                $tmp->iconDefinitionArgs[]  = $this->makeIconDefinitionArgs($configurator, $context);
                $tmp->tsConfig[]            = $this->makeTsConfig($configurator, $context);
                if ($configurator->renderBackendPreview()) {
                    $tmp->backendPreviewRenderers[] = $this->makeRegisterBackendPreviewRendererArgs($configurator);
                }
                $tmp->backendListLabelRenderers[] = $this->makeRegisterBackendLabelListRendererArgs($configurator);
                $tmp->dataHandlerActionHandlers   = Arrays::attach($tmp->dataHandlerActionHandlers,
                    $configurator->__getDataHandlerActionHandlers());
            });
        }
        
        // Create a new config object
        $config                            = $context->getInstanceOf(ElementConfig::class);
        $config->typoScript                = implode(PHP_EOL . PHP_EOL, $tmp->typoScript);
        $config->tsConfig                  = implode(PHP_EOL . PHP_EOL, $tmp->tsConfig);
        $config->addPiFlexFormArgs         = array_filter($tmp->addPiFlexFormArgs);
        $config->flexFormPlugins           = array_filter($tmp->flexFormPlugins);
        $config->registerPluginArgs        = $tmp->registerPluginArgs;
        $config->configurePluginArgs       = $tmp->configurePluginArgs;
        $config->iconDefinitionArgs        = $tmp->iconDefinitionArgs;
        $config->backendPreviewRenderers   = array_filter($tmp->backendPreviewRenderers);
        $config->backendListLabelRenderers = array_filter($tmp->backendListLabelRenderers);
        $config->backendActionHandlers     = $tmp->dataHandlerActionHandlers;
        $config->cTypeEntries              = $tmp->cTypeEntries;
        unset($tmp);
        
        // Done
        return $config;
    }
    
    /**
     * Returns the arguments that have to be passed to ExtensionUtility::registerPlugin() to
     * register a plugin in the extbase context
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return array
     * @see  ExtensionUtility::registerPlugin()
     */
    protected function makeRegisterPluginArgs(PluginConfigurator $configurator, ExtConfigContext $context): array
    {
        return array_values([
            'extensionName'             => $context->getExtKey(),
            'pluginName'                => $configurator->getPluginName(),
            'pluginTitle'               => $configurator->getTitle(),
            'pluginIconPathAndFilename' => $this->makeIconIdentifier($configurator, $context),
        ]);
    }
    
    /**
     * Is used instead of makeRegisterPluginArgs() when a content element should be registered instead of a plugin
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return array
     */
    protected function makeRegisterCTypeElement(PluginConfigurator $configurator, ExtConfigContext $context): array
    {
        $sectionLabel = $configurator->getCTypeSection();
        if (empty($sectionLabel)) {
            $sectionLabel = Inflector::toHuman($context->getExtKey());
        }
        
        return [
            $sectionLabel,
            $configurator->getTitle(),
            $configurator->getSignature(),
            $configurator->getIcon(),
        ];
    }
    
    /**
     * Returns the arguments that have to be passed to ExtensionUtility::configurePlugin()
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return array
     * @see ExtensionUtility::configurePlugin()
     */
    protected function makeConfigurePluginArgs(PluginConfigurator $configurator, ExtConfigContext $context): array
    {
        return array_values([
            'extensionName'                 => $context->getExtKeyWithVendor(),
            'pluginName'                    => $configurator->getPluginName(),
            'controllerActions'             => $configurator->getActions(),
            'nonCacheableControllerActions' => $configurator->getNoCacheActions(),
            'pluginType'                    => $configurator->getExtensionUtilityType(),
        ]);
    }
    
    /**
     * Returns the arguments that have to be passed to ExtensionManagementUtility::addPiFlexFormValue()
     * to register a flex form for the current plugin.
     *
     * If null is returned the plugin does not require a flex form
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   object                                                                           $tmp
     *
     * @return array|null
     * @see \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue()
     */
    protected function makeAddPiFlexFormArgs(PluginConfigurator $configurator, object $tmp): ?array
    {
        if (! $configurator->hasFlexFormConfig()) {
            return null;
        }
        
        // Add to list of plugins with flex form configuration
        $tmp->flexFormPlugins[] = $configurator->getSignature();
        
        // Compile the form
        $flexFormFile = $configurator->getFlexFormConfig()->__build()->getFileName();
        $flexFormFile = 'FILE:' . $flexFormFile;
        
        // Make the output
        return array_values([
            'piKeyToMatch' => $configurator->getSignature(),
            'value'        => $flexFormFile,
            'CTypeToMatch' => $configurator->getType() === 'plugin' ? 'list' : $configurator->getSignature(),
        ]);
    }
    
    /**
     * Returns the arguments that have to be used to register the plugin's icon in the icon registry
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return array
     * @see IconRegistry::registerIcon();
     */
    protected function makeIconDefinitionArgs(PluginConfigurator $configurator, ExtConfigContext $context): array
    {
        $iconExtension = strtolower(pathinfo($configurator->getIcon(), PATHINFO_EXTENSION));
        
        return array_values([
            'identifier'            => $this->makeIconIdentifier($configurator, $context),
            'iconProviderClassName' => $iconExtension === 'svg' ? SvgIconProvider::class : BitmapIconProvider::class,
            'options'               => ['source' => $configurator->getIcon()],
        ]);
    }
    
    /**
     * Builds the ts config script that is required to register a new content element wizard icon for this plugin
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return string
     */
    protected function makeTsConfig(PluginConfigurator $configurator, ExtConfigContext $context): string
    {
        if ($configurator->getWizardTab() === false) {
            return '';
        }
        
        $header = ! empty($configurator->getWizardTabLabel()) ? 'header = ' . $configurator->getWizardTabLabel() : '';
        
        // Select the default field values based on the type we should add
        if ($configurator->getType() === 'plugin') {
            $defaultValues = "CType = list
						list_type = {$configurator->getSignature()}";
        } else {
            $defaultValues = "CType = {$configurator->getSignature()}";
        }
        
        return "
		mod.wizards.newContentElement.wizardItems.{$configurator->getWizardTab()} {
			$header
			elements {
				{$configurator->getSignature()} {
					iconIdentifier = {$this->makeIconIdentifier($configurator, $context)}
					title = {$configurator->getTitle()}
					description = {$configurator->getDescription()}
					tt_content_defValues {
						$defaultValues
					}
				}
			}
			show := addToList({$configurator->getSignature()})
		}
		";
    }
    
    /**
     * Builds the arguments that have to be passed to BackendPreviewService::registerBackendPreviewRenderer to register
     * the backend preview renderer for this plugin. Null is returned if there is no preview renderer registered
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     *
     * @return array|null
     */
    protected function makeRegisterBackendPreviewRendererArgs(PluginConfigurator $configurator): ?array
    {
        if (empty($configurator->getBackendPreviewRenderer())) {
            return null;
        }
        
        // Configure the constraints
        if ($configurator->getType() === 'plugin') {
            $constraints = ['CType' => 'list', 'list_type' => $configurator->getSignature()];
        } else {
            $constraints = ['CType' => $configurator->getSignature()];
        }
        
        return array_values([
            'rendererClass'    => $configurator->getBackendPreviewRenderer(),
            'fieldConstraints' => $constraints,
        ]);
    }
    
    /**
     * Builds the arguments that have to be passed to BackendPreviewService::registerBackendListLabelRenderer to
     * register the backend list label renderer for this plugin. Null is returned if there is no renderer registered
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     *
     * @return array|null
     */
    protected function makeRegisterBackendLabelListRendererArgs(PluginConfigurator $configurator): ?array
    {
        if (empty($configurator->getBackendListLabelRenderer())) {
            return null;
        }
        
        // Configure the constraints
        if ($configurator->getType() === 'plugin') {
            $constraints = ['CType' => 'list', 'list_type' => $configurator->getSignature()];
        } else {
            $constraints = ['CType' => $configurator->getSignature()];
        }
        
        return array_values([
            'rendererClassOrColumns' => $configurator->getBackendListLabelRenderer(),
            'fieldConstraints'       => $constraints,
        ]);
    }
    
    /**
     * Internal helper to create the icon identifier for this plugin
     *
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext                          $context
     *
     * @return string
     */
    protected function makeIconIdentifier(PluginConfigurator $configurator, ExtConfigContext $context): string
    {
        return Inflector::toDashed($context->getExtKey() . '-' . $configurator->getPluginName());
    }
}
