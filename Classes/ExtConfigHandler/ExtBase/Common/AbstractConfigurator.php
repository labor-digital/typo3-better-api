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

namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common;

use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigException;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use SplStack;

abstract class AbstractConfigurator
{
    /**
     * The context to create the plugin with
     *
     * @var ExtConfigContext
     */
    protected $context;
    
    /**
     * The typoScript signature of this element
     *
     * @var string
     */
    protected $signature;
    
    /**
     * The class name of the element's controller
     *
     * @var string
     */
    protected $controllerClass;
    
    /**
     * The ext base plugin name for this element
     *
     * @var string
     */
    protected $pluginName;
    
    /**
     * Optional path like EXT:extkey... to a icon for this element.
     * If not given the ext_icon.gif in the root directory will be used.
     *
     * @var string
     */
    protected $icon;
    
    /**
     * The list of all registered template root paths for this element
     *
     * @var \SplStack
     */
    protected $templateRootPaths;
    
    /**
     * The list of all registered partial root paths for this element
     *
     * @var \SplStack
     */
    protected $partialRootPaths;
    
    /**
     * The list of all registered layout root paths for this element
     *
     * @var \SplStack
     */
    protected $layoutRootPaths;
    
    /**
     * Defines the allowed actions of the module controller class.
     * If this is empty all public methods that end with "Action" will be used as action methods.
     *
     * @var array
     */
    protected $actions = [];
    
    /**
     * AbstractExtBaseElementConfigurator constructor.
     *
     * @param   string            $signature
     * @param   string            $pluginName
     * @param   ExtConfigContext  $context
     */
    public function __construct(string $signature, string $pluginName, ExtConfigContext $context)
    {
        $this->context = $context;
        $extKey = $context->getExtKey();
        $pathAspect = $context->getTypoContext()->path();
        
        $this->templateRootPaths = new SplStack();
        $this->templateRootPaths->push($pathAspect->getTemplatePath($extKey));
        $this->templateRootPaths->push($pathAspect->getTemplatePath($extKey, $pluginName));
        
        $this->partialRootPaths = new SplStack();
        $this->partialRootPaths->push($pathAspect->getPartialPath($extKey));
        $this->partialRootPaths->push($pathAspect->getPartialPath($extKey, $pluginName));
        
        $this->layoutRootPaths = new SplStack();
        $this->layoutRootPaths->push($pathAspect->getLayoutPath($extKey));
        $this->layoutRootPaths->push($pathAspect->getLayoutPath($extKey, $pluginName));
        
        $this->icon = $pathAspect->getExtensionIconPath($extKey);
        $this->pluginName = $pluginName;
        $this->signature = $signature;
        $controllerClass = $this->makeControllerClassName($this->pluginName);
        if (class_exists($controllerClass)) {
            $this->setControllerClass($controllerClass);
        }
    }
    
    /**
     * Returns the typoScript signature of this element
     *
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }
    
    /**
     * Returns the ext base plugin name for this element
     *
     * @return string
     */
    public function getPluginName(): string
    {
        return $this->pluginName;
    }
    
    /**
     * Returns the class name of the element's controller
     *
     * @return string
     */
    public function getControllerClass(): string
    {
        return $this->controllerClass;
    }
    
    /**
     * Returns the icon of this element
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }
    
    /**
     * Sets the path like EXT:extkey... to a icon for this element.
     * If not given the ext_icon.gif in the root directory will be used.
     *
     * @param   string  $icon
     *
     * @return $this
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $this->context->replaceMarkers($icon);
        
        return $this;
    }
    
    /**
     * Returns the list of all registered template root paths for this element
     *
     * @return \SplStack
     */
    public function getTemplateRootPaths(): SplStack
    {
        return $this->templateRootPaths;
    }
    
    /**
     * Sets the list of all registered template root paths for this element
     *
     * @param   \SplStack  $templateRootPaths
     *
     * @return $this
     */
    public function setTemplateRootPaths(SplStack $templateRootPaths): self
    {
        $this->templateRootPaths = $templateRootPaths;
        
        return $this;
    }
    
    /**
     * Returns the list of all registered partial root paths for this element
     *
     * @return \SplStack
     */
    public function getPartialRootPaths(): SplStack
    {
        return $this->partialRootPaths;
    }
    
    /**
     * Sets the list of all registered partial root paths for this element
     *
     * @param   \SplStack  $partialRootPaths
     *
     * @return $this
     */
    public function setPartialRootPaths(SplStack $partialRootPaths): self
    {
        $this->partialRootPaths = $partialRootPaths;
        
        return $this;
    }
    
    /**
     * Returns the list of all registered layout root paths for this element
     *
     * @return \SplStack
     */
    public function getLayoutRootPaths(): SplStack
    {
        return $this->layoutRootPaths;
    }
    
    /**
     * Sets the list of all registered layout root paths for this element
     *
     * @param   \SplStack  $layoutRootPaths
     *
     * @return $this
     */
    public function setLayoutRootPaths(SplStack $layoutRootPaths): self
    {
        $this->layoutRootPaths = $layoutRootPaths;
        
        return $this;
    }
    
    /**
     * Returns the allowed actions of the module controller class.
     *
     * @return array
     */
    public function getActions(): array
    {
        return $this->actionsProcessor($this->actions);
    }
    
    /**
     * Sets the allowed actions of the module controller class.
     * If this is empty all public methods that end with "Action" will be used as action methods.
     *
     * Should be something like ["PluginName" => "action,action2"] or ["action", "action2"]
     *
     * @param   array  $actions
     *
     * @return $this
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    public function setActions(array $actions): self
    {
        $this->actions = $actions;
        
        // Update the controller class
        $actions = $this->getActions();
        reset($actions);
        $defaultControllerKey = key($actions);
        if (empty($defaultControllerKey)) {
            throw new ExtConfigException('Invalid actions array given!');
        }
        $newControllerClass = $this->makeControllerClassName($defaultControllerKey);
        if ($newControllerClass !== $this->controllerClass) {
            $this->setControllerClass($newControllerClass);
        }
        
        // Done
        return $this;
    }
    
    /**
     * Internal helper which is used to format the actions array correctly before outputting it
     *
     * @param   array  $actions
     *
     * @return array
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    protected function actionsProcessor(array $actions): array
    {
        if (empty($actions) && $this->controllerClass !== null && class_exists($this->controllerClass)) {
            $actions = array_values(
                array_filter(
                    get_class_methods($this->controllerClass),
                    static function ($v) {
                        return substr($v, -6) === 'Action' && stripos($v, 'initialize') !== 0;
                    }
                )
            );
        }
        
        if (! Arrays::isAssociative($actions)) {
            $actions = [$this->controllerClass => $actions];
        }
        
        foreach ($actions as $k => $list) {
            if (is_string($list)) {
                $list = Arrays::makeFromStringList($list);
            }
            
            if (! is_array($list)) {
                throw new ExtConfigException("Invalid plugin action array for $this->pluginName given!");
            }
            
            $actions[$k] = implode(',',
                array_map(static function ($v) {
                    return preg_replace('~Action$~', '', $v);
                }, $list)
            );
        }
        
        return $actions;
    }
    
    /**
     * Generates the controller class name for the given base name
     *
     * @param   string  $baseName
     *
     * @return string
     */
    protected function makeControllerClassName(string $baseName): string
    {
        if (class_exists($baseName)) {
            return $baseName;
        }
        
        return implode('\\', array_filter([
            ucfirst($this->context->getVendor()),
            Inflector::toCamelCase($this->context->getExtKey()),
            'Controller',
            Inflector::toCamelCase($baseName) . 'Controller',
        ]));
    }
    
    /**
     * Internal helper to set the controller class when the action list changes.
     * Can be extended to serve as semi-event emitter...
     *
     * @param   string  $controllerClass
     *
     * @throws \LaborDigital\T3ba\ExtConfig\ExtConfigException
     */
    protected function setControllerClass(string $controllerClass): void
    {
        if (! class_exists($controllerClass)) {
            throw new ExtConfigException("The controller class $controllerClass does not exist!");
        }
        
        $this->controllerClass = $controllerClass;
        
        // Update the template root paths
        $pathAspect = $this->context->getTypoContext()->path();
        $controllerBaseName = Inflector::toCamelCase(
            preg_replace('~controller$~i', '', Path::classBasename($controllerClass))
        );
        $this->templateRootPaths->push($pathAspect->getTemplatePath($this->context->getExtKey(), $controllerBaseName));
        $this->partialRootPaths->push($pathAspect->getPartialPath($this->context->getExtKey(), $controllerBaseName));
        $this->layoutRootPaths->push($pathAspect->getLayoutPath($this->context->getExtKey(), $controllerBaseName));
    }
}
