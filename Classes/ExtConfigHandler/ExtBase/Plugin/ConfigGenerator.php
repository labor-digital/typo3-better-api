<?php
/*
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
 * Last modified: 2020.10.19 at 14:38
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractConfigGenerator;
use LaborDigital\T3BA\Tool\BackendPreview\Hook\ContentPreviewRenderer;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Dumper;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

class ConfigGenerator extends AbstractConfigGenerator
{
    /**
     * The list of generated registration method arguments
     *
     * @var array
     */
    protected $registrationArgs = [];

    /**
     * The list of "configurePlugin" method arguments
     *
     * @var array
     */
    protected $configureArgs = [];

    /**
     * The list of generated typo script snippets
     *
     * @var array
     */
    protected $typoScript = [];

    /**
     * The list of generated Ts Config settings for the plugin registration
     *
     * @var array
     */
    protected $tsConfig = [];

    /**
     * Contains the arguments that have to be used to register the plugin's icon in the icon registry
     *
     * @var array
     */
    protected $iconArgs = [];

    /**
     * The list of registered backend preview renderers, for both the cTypes and the list_types
     *
     * @var array
     */
    protected $backendPreviewHooks = [];

    /**
     * The list of all collected data hooks to be executed for the content elements
     *
     * @var array
     */
    protected $dataHooks = [];

    /**
     * The list of all collected flex form registration arguments for the content elements
     *
     * @var array
     */
    protected $flexFormArgs = [];

    /**
     * Generates the required configuration to register the plugin defined in the given $configurator
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    public function generate(PluginConfigurator $configurator, ExtConfigContext $context): void
    {
        $this->registerTemplateDefinition('plugin', $configurator, $context);
        $this->registerTypoScript($configurator);
        $this->registerElementInTypoHooks($configurator, $context);
        $this->registerIconDefinition($configurator, $context);
        $this->registerNewCEWizardTsConfig($configurator, $context);
        $this->registerBackendPreviewAndListLabelRenderer($configurator, $context);
        $this->registerFlexFormConfig($configurator, $context);
        $this->dataHooks = array_merge($this->dataHooks, $configurator->getRegisteredDataHooks());
    }

    /**
     * The generator caches some of the generated configuration that has to be dumped into the state object
     * after all handlers have been processed.
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function dump(ConfigState $state): void
    {
        $state->useNamespace('typo.extBase.plugin', function () use ($state) {
            $this->setAsJson($state, 'args', $this->registrationArgs);
            $this->setAsJson($state, 'configureArgs', $this->configureArgs);
            $this->setAsJson($state, 'iconArgs', $this->iconArgs);
            $this->setAsJson($state, 'dataHooks', $this->dataHooks);
            $this->setAsJson($state, 'backendPreviewHooks', $this->backendPreviewHooks);
            $this->setAsJson($state, 'flexForms', $this->flexFormArgs);
        });

        $this->attachToStringValue($state, 'typo.typoScript.pageTsConfig', implode(PHP_EOL, $this->tsConfig));
        $this->attachToStringValue($state, 'typo.typoScript.dynamicTypoScript.extBaseTemplates\.setup',
            implode(PHP_EOL, $this->typoScript));
    }

    /**
     * Registers the required typo script configuration into the ext base templates file
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     */
    protected function registerTypoScript(PluginConfigurator $configurator): void
    {
        $typoScript = $configurator->getAdditionalTypoScript();
        if (! empty($typoScript)) {
            $this->typoScript[] = '# Register additional typo script ' . $configurator->getSignature() .
                                  PHP_EOL . $typoScript;
        }
    }

    /**
     * Generates and registers the arguments that are required to register the plugin/content element in the TYPO3 api
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    protected function registerElementInTypoHooks(PluginConfigurator $configurator, ExtConfigContext $context): void
    {
        // Configure Plugin
        /** @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin() */
        $this->configureArgs[] = array_values([
            'extensionName'                 => $context->getExtKey(),
            'pluginName'                    => $configurator->getPluginName(),
            'controllerActions'             => $configurator->getActions(),
            'nonCacheableControllerActions' => $configurator->getNoCacheActions(),
            'pluginType'                    => $configurator->getExtensionUtilityType(),
        ]);

        if ($configurator->getType() === 'plugin') {
            // Register a plugin
            /** @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin() */
            $this->registrationArgs['registerPlugin'][] = array_values([
                'extensionName'             => $context->getExtKey(),
                'pluginName'                => $configurator->getPluginName(),
                'pluginTitle'               => $configurator->getTitle(),
                'pluginIconPathAndFilename' => $this->makeIconIdentifier($configurator, $context),
            ]);
        } else {
            // Register a c type
            $this->registrationArgs['registerCType'][] = array_values([
                'sectionLabel' => empty($configurator->getCTypeSection()) ?
                    Inflector::toHuman($context->getExtKey()) : $configurator->getCTypeSection(),
                'title'        => $configurator->getTitle(),
                'signature'    => $configurator->getSignature(),
                'icon'         => $configurator->getIcon(),
            ]);
        }
    }

    /**
     * Generates a new set of arguments that have to be used to register the plugin's icon in the icon registry
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    protected function registerIconDefinition(PluginConfigurator $configurator, ExtConfigContext $context): void
    {
        $iconExtension    = strtolower(pathinfo($configurator->getIcon(), PATHINFO_EXTENSION));
        $this->iconArgs[] = array_values([
            'identifier'            => $this->makeIconIdentifier($configurator, $context),
            'iconProviderClassName' => $iconExtension === 'svg' ? SvgIconProvider::class : BitmapIconProvider::class,
            'options'               => ['source' => $configurator->getIcon()],
        ]);
    }

    /**
     * Generates and registers the ts config snippet that is required to register a new content element wizard icon for
     * this plugin
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    protected function registerNewCEWizardTsConfig(PluginConfigurator $configurator, ExtConfigContext $context): void
    {
        if ($configurator->getWizardTab() === false) {
            return;
        }

        $header = ! empty($configurator->getWizardTabLabel()) ? 'header = ' . $configurator->getWizardTabLabel() : '';

        // Select the default field values based on the type we should add
        if ($configurator->getType() === 'plugin') {
            $defaultValues = 'CType = list' . PHP_EOL . 'list_type = ' . $configurator->getSignature();
        } else {
            $defaultValues = 'CType = ' . $configurator->getSignature();
        }

        $this->tsConfig[] = 'mod.wizards.newContentElement.wizardItems.' . $configurator->getWizardTab() . ' {
			' . $header . '
			elements {
				' . $configurator->getSignature() . ' {
					iconIdentifier = ' . $this->makeIconIdentifier($configurator, $context) . '
					title = ' . $configurator->getTitle() . '
					description = ' . $configurator->getDescription() . '
					tt_content_defValues {
						' . $defaultValues . '
					}
				}
			}
			show := addToList(' . $configurator->getSignature() . ')
		}';
    }

    /**
     * Registers both the backend preview renderer and the backend list label renderer definitions in the
     * configuration state
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    protected function registerBackendPreviewAndListLabelRenderer(
        PluginConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        // Register renderer configuration
        foreach (
            [
                't3ba.backendPreview.previewRenderers'   => 'getBackendPreviewRenderer',
                't3ba.backendPreview.listLabelRenderers' => 'getBackendListLabelRenderer',
            ] as $key => $method
        ) {
            $renderer = $configurator->$method();
            if (! empty($renderer)) {
                if ($configurator->getType() === 'plugin') {
                    $constraints = ['CType' => 'list', 'list_type' => $configurator->getSignature()];
                } else {
                    $constraints = ['CType' => $configurator->getSignature()];
                }

                $this->attachToArrayValue(
                    $context->getState(),
                    $key,
                    [$renderer, $constraints]
                );
            }
        }

        // Register backend preview renderer hook in the tca
        $renderer = $configurator->getBackendPreviewRenderer();
        if (! empty($renderer)) {
            if ($configurator->getType() === 'plugin') {
                $this->backendPreviewHooks['types']['list']['previewRenderer'][$configurator->getSignature()]
                    = ContentPreviewRenderer::class;
            } else {
                $this->backendPreviewHooks['types'][$configurator->getSignature()]['previewRenderer']
                    = ContentPreviewRenderer::class;
            }
        }
    }

    /**
     * Builds and registers the arguments for the flex form definition of content elements and plugins
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     */
    protected function registerFlexFormConfig(
        PluginConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        if (! $configurator->hasFlexForm()) {
            return;
        }

        $dumper       = $context->getTypoContext()->di()->getService(Dumper::class);
        $flexFormFile = $dumper->dumpToFile($configurator->getFlexForm());

        $this->flexFormArgs[] = [
            'signature' => $configurator->getSignature(),
            'args'      => array_values([
                'piKeyToMatch' => $configurator->getSignature(),
                'value'        => 'FILE:' . $flexFormFile,
                'CTypeToMatch' => $configurator->getType() === 'plugin' ? 'list' : $configurator->getSignature(),
            ]),
        ];

    }

    /**
     * Internal helper to create the icon identifier for this plugin
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                          $context
     *
     * @return string
     */
    protected function makeIconIdentifier(PluginConfigurator $configurator, ExtConfigContext $context): string
    {
        return Inflector::toDashed($context->getExtKey() . '-' . $configurator->getPluginName());
    }

}
