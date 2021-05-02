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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use LaborDigital\T3ba\Tool\BackendPreview\BackendListLabelRendererInterface;
use LaborDigital\T3ba\Tool\BackendPreview\BackendPreviewRendererInterface;
use LaborDigital\T3ba\Tool\DataHook\DataHookCollectorTrait;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\Inflection\Inflector;

abstract class AbstractElementConfigurator extends AbstractConfigurator
{
    use DataHookCollectorTrait;
    
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
     * Holds additional typo script configuration for this plugin.
     * Note: This is raw typo script, meaning you have to do plugin.tx_... {} yourselves!
     *
     * @var string
     */
    protected $additionalTypoScript = '';
    
    /**
     * The list of registered variants to create for this plugin
     *
     * @var self[]
     */
    protected $variants = [];
    
    /**
     * True if this configurator defines a variant.
     *
     * @var bool
     */
    protected $isVariant;
    
    /**
     * @inheritDoc
     */
    public function __construct(
        string $signature,
        string $pluginName,
        ExtConfigContext $context,
        bool $isVariant = false
    )
    {
        parent::__construct($signature, $pluginName, $context);
        
        $this->title = Inflector::toHuman($context->getExtKey()) . ': ' . Inflector::toHuman($pluginName);
        $this->isVariant = $isVariant;
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
     * @return $this
     */
    public function setTitle(string $title): self
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
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        
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
     * @return $this
     */
    public function setNoCacheActions(array $noCacheActions): self
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
     * @return $this
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
     * @return $this
     */
    public function setWizardTabLabel(?string $wizardTabLabel): self
    {
        $this->wizardTabLabel = $wizardTabLabel;
        
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
     * @return $this
     */
    public function setBackendPreviewRenderer(?string $backendPreviewRenderer): self
    {
        $this->backendPreviewRendererWasSet = true;
        $this->backendPreviewRenderer = $backendPreviewRenderer;
        
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
     * @return $this
     */
    public function setBackendListLabelRenderer($backendListLabelRenderer): self
    {
        $this->backendListLabelRendererWasSet = true;
        $this->backendListLabelRenderer = $backendListLabelRenderer;
        
        return $this;
    }
    
    /**
     * Sets additional typoScript configuration for this plugin.
     * Note: This is raw typo script, meaning you have to do plugin.tx_... {} yourselves!
     *
     * @param   string  $setup
     *
     * @return $this
     */
    public function setAdditionalTypoScript(string $setup): self
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
     * @return $this
     * @see setAdditionalTypoScript()
     */
    public function addAdditionalTypoScript(string $setup): self
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
     * Either returns a new, or existing variant instance for this element.
     * A variant will generate the same element with different options.
     *
     * This comes in handy, because switchable controller actions have been removed
     *
     * @param   string  $name  A unique name/key for the variant to generate
     *
     * @return $this|\LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function getVariant(string $name)
    {
        if ($this->isVariant) {
            throw new ExtConfigException('A variant can\'t currently have variants itself!');
        }
        
        if (isset($this->variants[$name])) {
            return $this->variants[$name];
        }
        
        $lcName = NamingUtil::flattenExtKey($name);
        
        $v = new static(
            $this->getSignature() . $lcName,
            $this->getPluginName() . Inflector::toCamelCase($name),
            $this->context,
            true
        );
        
        $v->setControllerClass($this->controllerClass);
        
        if (method_exists($this->controllerClass, $name . 'Action')) {
            $v->setActions([$name]);
        }
        
        return $this->variants[$name] = $v;
    }
    
    /**
     * Returns the list of all registered variants for this
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Plugin\PluginConfigurator[]
     */
    public function getVariants(): array
    {
        return $this->variants;
    }
    
    /**
     * Returns the field constraints on which this element will be bound to
     *
     * @return array
     */
    public function getFieldConstraints(): array
    {
        return $this->getDataHookTableFieldConstraints();
    }
    
    /**
     * @inheritDoc
     */
    protected function setControllerClass(string $controllerClass): void
    {
        if (! $this->backendPreviewRendererWasSet && class_exists($controllerClass)
            && in_array(BackendPreviewRendererInterface::class, class_implements($controllerClass), true)) {
            $this->backendPreviewRenderer = $controllerClass;
        }
        
        if (! $this->backendListLabelRendererWasSet && class_exists($controllerClass)
            && in_array(BackendListLabelRendererInterface::class, class_implements($controllerClass), true)) {
            $this->backendListLabelRenderer = $controllerClass;
        }
        
        // Update the controller class
        parent::setControllerClass($controllerClass);
    }
    
}
