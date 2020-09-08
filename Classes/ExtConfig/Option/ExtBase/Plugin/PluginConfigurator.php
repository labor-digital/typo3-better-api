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
 * Last modified: 2020.03.21 at 20:50
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin;

use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm;
use LaborDigital\Typo3BetterApi\BackendPreview\BackendListLabelRendererInterface;
use LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererInterface;
use LaborDigital\Typo3BetterApi\DataHandler\DataHandlerActionCollectorTrait;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic\AbstractElementConfigurator;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class PluginConfigurator extends AbstractElementConfigurator
{
    use DataHandlerActionCollectorTrait;
    
    /**
     * The visible name of the plugin. Should be translation! If not set, the humanized extension key and plugin name
     * are used.
     *
     * @var string
     */
    protected $title = '';
    
    /**
     * The visible description of this plugin in the new content element wizard
     *
     * @var string
     */
    protected $description = '';
    
    /**
     * Defines if this plugin is registered as plugin or as content element in extbase
     *
     * @var string
     */
    protected $type = ExtensionUtility::PLUGIN_TYPE_PLUGIN;
    
    /**
     * The id of the new content element wizard tab. "plugins" by default.
     * Setting this value to FALSE (bool) will disable the creation of a wizard entry for this element
     *
     * @var string|bool
     */
    protected $wizardTab = 'plugins';
    
    /**
     * Can be used to define the label of a certain wizard tab.
     * This can be used if you create a new wizard tab by using the $wizardTab option
     *
     * @var string|null
     */
    protected $wizardTabLabel;
    
    /**
     * Defines which actions (they have to be previously defined in "actions") should be handled without caching them.
     * Follows the same definition syntax as $actions.
     *
     * @var array
     */
    protected $noCacheActions = [];
    
    /**
     * Holds the flex form instance we use to configure the flex form for this plugin
     * If this is empty we don't have a flex form for this plugin
     *
     * @var FlexForm|null
     */
    protected $flexForm;
    
    /**
     * The class that is responsible for rendering the backend preview for this plugin
     *
     * @var string|null
     */
    protected $backendPreviewRenderer;
    
    /**
     * True when the backend preview renderer was set -> meaning we should keep the value, even if the controller
     * changes...
     *
     * @var bool
     */
    protected $backendPreviewRendererWasSet = false;
    
    /**
     * The class that is responsible for rendering the backend list label for this plugin
     *
     * @var string|null
     */
    protected $backendListLabelRenderer;
    
    /**
     * True when the backend list label renderer was set -> meaning we should keep the value, even if the controller
     * changes...
     *
     * @var bool
     */
    protected $backendListLabelRendererWasSet = false;
    
    /**
     * Holds additional typoscript configuration for this plugin.
     * Note: This is raw typo script, meaning you have to do plugin.tx_... {} yourselves!
     *
     * @var string
     */
    protected $additionalTypoScript = '';
    
    /**
     * True if we should render a backend preview for this content element
     *
     * @var bool
     */
    protected $backendPreview = true;
    
    /**
     * The section label of this element when it is rendered in the cType select box
     *
     * @var string
     */
    protected $cTypeSection;
    
    /**
     * @inheritDoc
     */
    public function __construct(string $pluginName, ExtConfigContext $context)
    {
        parent::__construct($pluginName, $context);
        $this->title = Inflector::toHuman($context->getExtKey()) . ': ' . Inflector::toHuman($pluginName);
    }
    
    /**
     * Returns true if this plugin has a flex form configuration
     *
     * @return bool
     */
    public function hasFlexFormConfig(): bool
    {
        return ! empty($this->flexForm);
    }
    
    /**
     * Returns the flex form configuration object for this plugin.
     * You have to call this method at least once to register a flex form file for an element
     *
     * @return \LaborDigital\Typo3BetterApi\BackendForms\FlexForms\FlexForm
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    public function getFlexFormConfig(): FlexForm
    {
        // Return existing instance
        if (! empty($this->flexForm)) {
            return $this->flexForm;
        }
        
        // Create new instance
        $this->flexForm = FlexForm::makeStandaloneInstance($this->context, null, 'tt_content');
        
        // Try to load the default definition
        try {
            $defaultDefinitionFile = 'file:' . Inflector::toCamelCase($this->pluginName) . '.xml';
            $this->flexForm->loadDefinition($defaultDefinitionFile);
        } catch (BackendFormException $e) {
            if ($e->getCode() !== 999) {
                throw $e;
            }
            // Ignore if we could not load the file
        }
        
        // Done
        return $this->flexForm;
    }
    
    /**
     * Returns the visible name of the plugin.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
    
    /**
     * Sets the visible name of the plugin. Should be translation! If not set,
     * the humanized extension key and plugin name  are used.
     *
     * @param   string  $title
     *
     * @return PluginConfigurator
     */
    public function setTitle(string $title): PluginConfigurator
    {
        $this->title = $title;
        
        return $this;
    }
    
    /**
     * Returns the visible description of this plugin in the new content element wizard
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Sets the visible description of this plugin in the new content element wizard
     *
     * @param   string  $description
     *
     * @return PluginConfigurator
     */
    public function setDescription(string $description): PluginConfigurator
    {
        $this->description = $description;
        
        return $this;
    }
    
    /**
     * Returns either "plugin" or "contentElement" defining how this plugin will be registered in ext base
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type === ExtensionUtility::PLUGIN_TYPE_PLUGIN ? 'plugin' : 'contentElement';
    }
    
    /**
     * Returns the type but prepared for the extension utility
     *
     * @return string
     */
    public function getExtensionUtilityType(): string
    {
        return $this->type;
    }
    
    /**
     * Can be used to define how this plugin is added to ext base.
     *
     * @param   string  $type  Can be set either to "plugin" or to "contentElement".
     *
     * @return PluginConfigurator
     */
    public function setType(string $type): PluginConfigurator
    {
        $this->type = strtolower($type) === 'plugin' ? ExtensionUtility::PLUGIN_TYPE_PLUGIN
            : ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT;
        
        return $this;
    }
    
    /**
     * Returns the currently set section label of this element when it is rendered in the cType select box.
     * This of course only works if you set the type to "contentElement".
     *
     * @return string
     */
    public function getCTypeSection(): string
    {
        return (string)$this->cTypeSection;
    }
    
    /**
     * Is used to set the section label of this element when it is rendered in the cType select box.
     * If this is not defined, a label is automatically generated using the extension key
     * This of course only works if you set the type to "contentElement".
     *
     * @param   string  $cTypeSection
     *
     * @return PluginConfigurator
     */
    public function setCTypeSection(string $cTypeSection): PluginConfigurator
    {
        $this->cTypeSection = $cTypeSection;
        
        return $this;
    }
    
    /**
     * Returns the list of actions that should not be cached
     *
     * @return array
     */
    public function getNoCacheActions(): array
    {
        if (empty($this->noCacheActions)) {
            return [];
        }
        
        return $this->actionsProcessor($this->noCacheActions);
    }
    
    /**
     * Sets the list of actions (they have to be previously defined in "actions") which should be handled without
     * caching them. Follows the same definition syntax as setActions.
     *
     * @param   array  $noCacheActions
     *
     * @return PluginConfigurator
     */
    public function setNoCacheActions(array $noCacheActions): PluginConfigurator
    {
        $this->noCacheActions = $noCacheActions;
        
        return $this;
    }
    
    /**
     * Returns the id of the new content element wizard tab. "plugins" by default.
     * If false is returned the wizard tab should not be created
     *
     * @return bool|string
     */
    public function getWizardTab()
    {
        return $this->wizardTab;
    }
    
    /**
     * Used to set the id of the new content element wizard tab. "plugins" by default.
     * Setting this value to FALSE (bool) will disable the creation of a wizard entry for this element
     *
     * @param   bool|string  $wizardTab
     *
     * @return PluginConfigurator
     */
    public function setWizardTab($wizardTab)
    {
        $this->wizardTab = $wizardTab;
        
        return $this;
    }
    
    /**
     * Returns the currently set label for this wizard tab or null
     *
     * @return string|null
     */
    public function getWizardTabLabel(): ?string
    {
        return $this->wizardTabLabel;
    }
    
    /**
     * Can be used to define the label of a certain wizard tab.
     * This can be used if you create a new wizard tab by using the $wizardTab option
     *
     * @param   string|null  $wizardTabLabel
     *
     * @return PluginConfigurator
     */
    public function setWizardTabLabel(?string $wizardTabLabel): PluginConfigurator
    {
        $this->wizardTabLabel = $wizardTabLabel;
        
        return $this;
    }
    
    /**
     * Returns true if this controller should render a backend preview, false if not
     *
     * @return bool
     */
    public function renderBackendPreview(): bool
    {
        return $this->backendPreview;
    }
    
    /**
     * If this is set to false, the content element will not render a backend preview
     *
     * @param   bool  $state
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator
     */
    public function setBackendPreview(bool $state): PluginConfigurator
    {
        $this->backendPreview = $state;
        
        return $this;
    }
    
    /**
     * Returns either the configured backend preview renderer class or null, if there is none
     *
     * @return string|null
     */
    public function getBackendPreviewRenderer(): ?string
    {
        return $this->backendPreviewRenderer;
    }
    
    /**
     * Can be used to define the backend preview renderer class.
     * The given class should implement the BackendPreviewRendererInterface, may be the same class as the plugin
     * configuration and/or the plugin controller.
     *
     * NOTE: If either your controller class implements the BackendPreviewRendererInterface
     * it is automatically selected as backend preview renderer.
     *
     * @param   string|null  $backendPreviewRenderer
     *
     * @return PluginConfigurator
     * @see \LaborDigital\Typo3BetterApi\BackendPreview\BackendPreviewRendererInterface
     */
    public function setBackendPreviewRenderer(?string $backendPreviewRenderer): PluginConfigurator
    {
        $this->backendPreviewRendererWasSet = true;
        $this->backendPreviewRenderer       = $backendPreviewRenderer;
        
        return $this;
    }
    
    /**
     * Returns either the configured backend list label renderer class, a list of fields that should be rendered or
     * null if there is nothing configured
     *
     * @return string|array|null
     */
    public function getBackendListLabelRenderer()
    {
        return $this->backendListLabelRenderer;
    }
    
    /**
     * Can be used to define the backend preview renderer class.
     * The given class should implement the BackendListLabelRendererInterface, may be the same class as the plugin
     * configuration and/or the plugin controller. You can also specify an array of column names that should
     * be used as descriptions in your label. In that case the internal renderer will handle the rest.
     *
     * NOTE: If either your controller class implements the BackendListLabelRendererInterface
     * it is automatically selected as backend preview renderer.
     *
     * @param   string|array|null  $backendListLabelRenderer
     *
     * @return PluginConfigurator
     * @see \LaborDigital\Typo3BetterApi\BackendPreview\BackendListLabelRendererInterface
     */
    public function setBackendListLabelRenderer($backendListLabelRenderer): PluginConfigurator
    {
        $this->backendListLabelRendererWasSet = true;
        $this->backendListLabelRenderer       = $backendListLabelRenderer;
        
        return $this;
    }
    
    /**
     * Sets additional typoscript configuration for this plugin.
     * Note: This is raw typo script, meaning you have to do plugin.tx_... {} yourselves!
     *
     * @param   string  $setup
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator
     */
    public function setAdditionalTypoScript(string $setup): PluginConfigurator
    {
        $this->additionalTypoScript = $setup;
        
        return $this;
    }
    
    /**
     * Similar to setAdditionalTypoScript() but keeps the existing typoScript setup and
     * just appends the given value to it.
     *
     * @param   string  $setup
     *
     * @return \LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Plugin\PluginConfigurator
     * @see setAdditionalTypoScript()
     */
    public function addAdditionalTypoScript(string $setup): PluginConfigurator
    {
        $this->additionalTypoScript .= PHP_EOL . '[GLOBAL]' . PHP_EOL;
        $this->additionalTypoScript .= $setup;
        $this->additionalTypoScript .= PHP_EOL . '[GLOBAL]' . PHP_EOL;
        
        return $this;
    }
    
    /**
     * Returns the additional typoscript configuration for this plugin.
     *
     * @return string
     */
    public function getAdditionalTypoScript(): string
    {
        return $this->additionalTypoScript;
    }
    
    /**
     * @inheritDoc
     */
    protected function setControllerClass(string $controllerClass)
    {
        // Update the backend preview renderer if required
        if (! $this->backendPreviewRendererWasSet) {
            if (class_exists($controllerClass)
                && in_array(BackendPreviewRendererInterface::class, class_implements($controllerClass))) {
                $this->backendPreviewRenderer = $controllerClass;
            }
        }
        if (! $this->backendListLabelRendererWasSet) {
            if (class_exists($controllerClass)
                && in_array(BackendListLabelRendererInterface::class, class_implements($controllerClass))) {
                $this->backendListLabelRenderer = $controllerClass;
            }
        }
        // Update the controller class
        parent::setControllerClass($controllerClass);
    }
    
    
    /**
     * @inheritDoc
     */
    protected function getDataHandlerTableName(): string
    {
        return 'tt_content';
    }
    
    /**
     * @inheritDoc
     */
    protected function getDataHandlerFieldConstraints(): array
    {
        if ($this->getType() !== 'plugin') {
            return ['CType' => $this->getSignature()];
        }
        
        return ['CType' => 'list', 'list_type' => $this->getSignature()];
    }
}