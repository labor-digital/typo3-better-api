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
 * Last modified: 2020.03.18 at 19:37
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\NamingConvention\Naming;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use SplStack;

abstract class AbstractElementConfigurator
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
     * @param   string            $pluginName
     * @param   ExtConfigContext  $context
     */
    public function __construct(string $pluginName, ExtConfigContext $context)
    {
        $this->context = $context;
        
        $this->templateRootPaths = new SplStack();
        $this->templateRootPaths->push("EXT:{$context->getExtKey()}/Resources/Private/Templates/");
        $this->templateRootPaths->push(
            $context->TypoContext->getPathAspect()->getTemplatePath($context->getExtKey(), $pluginName)
        );
        
        $this->partialRootPaths = new SplStack();
        $this->partialRootPaths->push("EXT:{$context->getExtKey()}/Resources/Private/Partials/");
        $this->partialRootPaths->push(
            $context->TypoContext->getPathAspect()->getPartialPath($context->getExtKey(), $pluginName)
        );
        
        $this->layoutRootPaths = new SplStack();
        $this->layoutRootPaths->push("EXT:{$context->getExtKey()}/Resources/Private/Layouts/");
        $this->layoutRootPaths->push(
            $context->TypoContext->getPathAspect()->getLayoutPath($context->getExtKey(), $pluginName)
        );
        
        $this->icon       = 'EXT:' . $context->getExtKey() . '/ext_icon.gif';
        $this->pluginName = Inflector::toCamelCase($pluginName);
        $this->signature  = Naming::pluginSignature($pluginName, $context->getExtKey());
        $controllerClass  = $this->makeControllerClassName($this->pluginName);
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
    public function setIcon(string $icon): AbstractElementConfigurator
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
    public function setTemplateRootPaths(SplStack $templateRootPaths): AbstractElementConfigurator
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
    public function setPartialRootPaths(SplStack $partialRootPaths): AbstractElementConfigurator
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
    public function setLayoutRootPaths(SplStack $layoutRootPaths): AbstractElementConfigurator
    {
        $this->layoutRootPaths = $layoutRootPaths;
        
        return $this;
    }
    
    /**
     * Returns the allowed actions of the module controller class.
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
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
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    public function setActions(array $actions): AbstractElementConfigurator
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
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function actionsProcessor(array $actions): array
    {
        // Make default actions
        if (empty($actions) && class_exists($this->controllerClass)) {
            $actions = [
                $this->pluginName => array_map(function ($v) {
                    return substr($v, 0, -6);
                }, array_filter(get_class_methods($this->controllerClass), function ($v) {
                    if ($v === 'initializeAction') {
                        return false;
                    }
                    
                    return substr($v, -6) === 'Action';
                })),
            ];
        }
        
        // Filter the actions
        if (Arrays::isSequential($actions)) {
            $actions = [$this->pluginName => $actions];
        }
        foreach ($actions as $k => $list) {
            if (is_string($list)) {
                $list = Arrays::makeFromStringList($list);
            }
            if (! is_array($list)) {
                throw new ExtConfigException("Invalid plugin action array for $this->pluginName given!");
            }
            $list        = array_map(function ($v) {
                return preg_replace('~Action$~s', '', $v);
            }, $list);
            $actions[$k] = implode(',', $list);
        }
        
        // Done
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
     * @throws \LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException
     */
    protected function setControllerClass(string $controllerClass)
    {
        if (! class_exists($controllerClass)) {
            throw new ExtConfigException("The controller class $controllerClass does not exist!");
        }
        
        $this->controllerClass = $controllerClass;
        
        // Update the template root path's
        $controllerBaseName = Inflector::toCamelCase(preg_replace('~controller$~si', '',
            Path::classBasename($controllerClass)));
        $this->templateRootPaths->push($this->context->TypoContext->getPathAspect()
                                                                  ->getTemplatePath($this->context->getExtKey(),
                                                                      $controllerBaseName));
        $this->partialRootPaths->push($this->context->TypoContext->getPathAspect()
                                                                 ->getPartialPath($this->context->getExtKey(),
                                                                     $controllerBaseName));
        $this->layoutRootPaths->push($this->context->TypoContext->getPathAspect()
                                                                ->getLayoutPath($this->context->getExtKey(),
                                                                    $controllerBaseName));
    }
}
