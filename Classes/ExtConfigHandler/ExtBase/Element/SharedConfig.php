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
 * Last modified: 2021.05.10 at 17:53
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use Neunerlei\Configuration\State\ConfigState;

class SharedConfig implements NoDiInterface
{
    /**
     * The list of generated registration method arguments
     *
     * @var array
     */
    public $registrationArgs = [];
    
    /**
     * The list of "configurePlugin" method arguments
     *
     * @var array
     */
    public $configureArgs = [];
    
    /**
     * The list of generated typo script snippets
     *
     * @var array
     */
    public $typoScript = [];
    
    /**
     * The list of generated Ts Config settings for the plugin registration
     *
     * @var array
     */
    public $tsConfig = [];
    
    /**
     * Contains the arguments that have to be used to register the plugin's icon in the icon registry
     *
     * @var array
     */
    public $iconArgs = [];
    
    /**
     * The list of registered backend preview renderers, for both the cTypes and the list_types
     *
     * @var array
     */
    public $backendPreviewHooks = [];
    
    /**
     * The list of all collected data hooks to be executed for the content elements
     *
     * @var array
     */
    public $dataHooks = [];
    
    /**
     * The list of all collected flex form registration arguments for the content elements
     *
     * @var array
     */
    public $flexFormArgs = [];
    
    /**
     * A list of all signatures and their matching variant names
     *
     * @var array
     */
    public $variantMap = [];
    
    /**
     * The generator caches some of the generated configuration that has to be dumped into the state object
     * after all handlers have been processed.
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function dump(ConfigState $state): void
    {
        $state->useNamespace('typo.extBase.element', function () use ($state) {
            $state->setAsJson('args', $this->registrationArgs);
            $state->set('configureArgs', $this->configureArgs);
            $state->setAsJson('iconArgs', $this->iconArgs);
            $state->setAsJson('dataHooks', $this->dataHooks);
            $state->setAsJson('backendPreviewHooks', $this->backendPreviewHooks);
            $state->setAsJson('flexForms', $this->flexFormArgs);
            $state->setAsJson('variants', $this->variantMap);
        });
        
        $state->attachToString('typo.typoScript.pageTsConfig',
            implode(PHP_EOL, $this->tsConfig), true);
        $state->attachToString('typo.typoScript.dynamicTypoScript.extBaseTemplates\.setup',
            implode(PHP_EOL, $this->tsConfig), true);
    }
}
