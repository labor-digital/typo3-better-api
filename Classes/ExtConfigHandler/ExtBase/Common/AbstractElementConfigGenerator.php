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
 * Last modified: 2021.04.20 at 11:02
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigException;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Element\SharedConfig;
use LaborDigital\T3BA\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator;
use LaborDigital\T3BA\Tool\BackendPreview\Hook\ContentPreviewRenderer;
use LaborDigital\T3BA\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3BA\Tool\Tca\Builder\Type\FlexForm\Io\Dumper;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

abstract class AbstractElementConfigGenerator extends AbstractConfigGenerator
{
    /**
     * @var \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Element\SharedConfig
     */
    protected $config;

    /**
     * MUST return the type of the configured element using the ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT or
     * ExtensionUtility::PLUGIN_TYPE_PLUGIN constants
     *
     * @return string
     */
    abstract protected function getExtensionUtilityType(): string;

    /**
     * MUST build the argument array that is passed used to register this element in the TYPO3 api
     *
     * @param   array                                                                           $list
     * @param   string                                                                          $extensionName
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     */
    abstract protected function setRegistrationArgs(
        array &$list,
        string $extensionName,
        ExtConfigContext $context,
        AbstractElementConfigurator $configurator
    ): void;

    /**
     * Must return the value for tt_content_defValues that is used to register this element in the new
     * content element wizard
     *
     * @param   string  $signature
     *
     * @return string
     */
    abstract protected function getCeWizardValues(string $signature): string;

    /**
     * MUST inject the preview hooks for this element in the given list
     *
     * @param   array   $list
     * @param   string  $signature
     * @param   string  $class
     */
    abstract protected function setPreviewHooks(array &$list, string $signature, string $class): void;

    /**
     * MUST return the "CTypeToMatch" argument of ExtensionManagementUtility::addPiFlexFormValue for this element
     *
     * @param   string  $signature
     *
     * @return string
     */
    abstract protected function getFlexFormCType(string $signature): string;

    /**
     * Injects the shared config object on which the data is stored
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Element\SharedConfig  $config
     */
    public function setConfig(SharedConfig $config): void
    {
        $this->config = $config;
    }

    /**
     * Generates the required configuration to register the plugin defined in the given $configurator
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    public function generate(AbstractElementConfigurator $configurator, ExtConfigContext $context): void
    {
        if (! $this->config instanceof SharedConfig) {
            throw new ExtConfigException('You can\'t execute "generate" before you set a config object');
        }

        $this->generateForSingleVariant($configurator, $context, null);

        foreach ($configurator->getVariants() as $variantName => $variant) {
            $this->generateForSingleVariant($variant, $context, $variantName);
        }
    }

    /**
     * Generates the required configuration for a single plugin variant
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     * @param   string|null                                                                     $variantName
     */
    protected function generateForSingleVariant(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context,
        ?string $variantName
    ): void {
        $this->registerTemplateDefinition('plugin', $configurator, $context);
        $this->registerTypoScript($configurator);
        $this->registerElementInTypoHooks($configurator, $context);
        $this->registerIconDefinition($configurator, $context);
        $this->registerNewCEWizardTsConfig($configurator, $context);
        $this->registerBackendPreviewAndListLabelRenderer($configurator, $context);
        $this->registerFlexFormConfig($configurator, $context);

        $this->dataHooks = array_merge($this->config->dataHooks, $configurator->getRegisteredDataHooks());

        $this->config->variantMap[$configurator->getSignature()] = $variantName;
    }

    /**
     * Registers the required typo script configuration into the ext base templates file
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     */
    protected function registerTypoScript(AbstractElementConfigurator $configurator): void
    {
        $typoScript = $configurator->getAdditionalTypoScript();
        if (! empty($typoScript)) {
            $this->config->typoScript[] = '# Register additional typo script ' . $configurator->getSignature() .
                                          PHP_EOL . $typoScript;
        }
    }

    /**
     * Generates and registers the arguments that are required to register the plugin/content element in the TYPO3 api
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    protected function registerElementInTypoHooks(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        $extensionName = NamingUtil::extensionNameFromExtKey($context->getExtKey());

        /** @see \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin() */
        $this->config->configureArgs[] = array_values([
            'extensionName'                 => $extensionName,
            'pluginName'                    => $configurator->getPluginName(),
            'controllerActions'             => $configurator->getActions(),
            'nonCacheableControllerActions' => $configurator->getNoCacheActions(),
            'pluginType'                    => $this->getExtensionUtilityType(),
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
                'alias'     => ExtensionUtility::resolveControllerAliasFromControllerClassName($controllerClass),
                'actions'   => Arrays::makeFromStringList($actions),
            ];
        }

        $this->setRegistrationArgs($this->config->registrationArgs, $extensionName, $context, $configurator);
    }

    /**
     * Generates a new set of arguments that have to be used to register the plugin's icon in the icon registry
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    protected function registerIconDefinition(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        $iconExtension            = strtolower(pathinfo($configurator->getIcon(), PATHINFO_EXTENSION));
        $this->config->iconArgs[] = array_values([
            'identifier'            => $this->makeIconIdentifier($configurator, $context),
            'iconProviderClassName' => $iconExtension === 'svg' ? SvgIconProvider::class : BitmapIconProvider::class,
            'options'               => ['source' => $configurator->getIcon()],
        ]);
    }

    /**
     * Generates and registers the ts config snippet that is required to register a new content element wizard icon for
     * this plugin
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    protected function registerNewCEWizardTsConfig(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        if ($configurator->getWizardTab() === false) {
            return;
        }

        $header                   = ! empty($configurator->getWizardTabLabel()) ? 'header = '
                                                                                  . $configurator->getWizardTabLabel()
            : '';
        $this->config->tsConfig[] = 'mod.wizards.newContentElement.wizardItems.' . $configurator->getWizardTab() . ' {
			' . $header . '
			elements {
				' . $configurator->getSignature() . ' {
					iconIdentifier = ' . $this->makeIconIdentifier($configurator, $context) . '
					title = ' . $configurator->getTitle() . '
					description = ' . $configurator->getDescription() . '
					tt_content_defValues {
						' . $this->getCeWizardValues($configurator->getSignature()) . '
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
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    protected function registerBackendPreviewAndListLabelRenderer(
        AbstractElementConfigurator $configurator,
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
                $context->getState()->attachToArray($key, [$renderer, $configurator->getFieldConstraints()]);
            }
        }

        // Register backend preview renderer hook in the tca
        $renderer = $configurator->getBackendPreviewRenderer();
        if (! empty($renderer)) {
            if (! $this->config->backendPreviewHooks['types']) {
                $this->config->backendPreviewHooks['types'] = [];
            }

            $this->setPreviewHooks(
                $this->config->backendPreviewHooks['types'],
                $configurator->getSignature(),
                ContentPreviewRenderer::class
            );
        }
    }

    /**
     * Builds and registers the arguments for the flex form definition of content elements and plugins
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     */
    protected function registerFlexFormConfig(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        if (! $configurator instanceof PluginConfigurator || ! $configurator->hasFlexForm()) {
            return;
        }

        $flexFormFile = $context->getParentContext()->di()->getService(Dumper::class)
                                ->dumpToFile($configurator->getFlexForm());

        $this->config->flexFormArgs[] = [
            'signature' => $configurator->getSignature(),
            'args'      => array_values([
                'piKeyToMatch' => $configurator->getSignature(),
                'value'        => 'FILE:' . $flexFormFile,
                'CTypeToMatch' => $this->getFlexFormCType($configurator->getSignature()),
            ]),
        ];
    }

    /**
     * Internal helper to create the icon identifier for this plugin
     *
     * @param   \LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3BA\ExtConfig\ExtConfigContext                                   $context
     *
     * @return string
     */
    protected function makeIconIdentifier(AbstractElementConfigurator $configurator, ExtConfigContext $context): string
    {
        return Inflector::toDashed($context->getExtKey() . '-' . $configurator->getPluginName());
    }

}
