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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic;

class ElementConfig
{
    
    /**
     * The typoScript configuration required for the registered elements
     *
     * @var string
     */
    public $typoScript = '';
    
    /**
     * The ts config string required for the registered elements
     *
     * @var string
     */
    public $tsConfig = '';
    
    /**
     * The list of arguments that have to be applied on the "ExtensionUtility::registerModule" method
     * to register our backend modules
     *
     * @var array
     */
    public $registerModuleArgs = [];
    
    /**
     * The list of arguments that have to be applied to the ExtensionUtility::registerPlugin method
     *
     * @var array
     */
    public $registerPluginArgs = [];
    
    /**
     * The list of arguments that have to be passed to the "ExtensionUtility::configurePlugin" method
     *
     * @var array
     */
    public $configurePluginArgs = [];
    
    /**
     * The arguments to apply to ExtensionManagementUtility::addPiFlexFormValue()
     *
     * @var array
     */
    public $addPiFlexFormArgs = [];
    
    /**
     * The list of icon definitions to register
     *
     * @var array
     */
    public $iconDefinitionArgs = [];
    
    /**
     * The list of plugins that have a flex form field in them
     *
     * @var array
     */
    public $flexFormPlugins = [];
    
    /**
     * The list of backend preview renderer registration arguments
     *
     * @var array
     */
    public $backendPreviewRenderers = [];
    
    /**
     * The list of backend list label renderer registration arguments
     *
     * @var array
     */
    public $backendListLabelRenderers = [];
    
    /**
     * The configured backend action handlers to register in the backend action service
     *
     * @var array
     */
    public $dataHandlerActionHandlers = [];
    
    /**
     * The list of cType entries that we should inject for our extBase content elements
     *
     * @var array
     */
    public $cTypeEntries = [];
}
