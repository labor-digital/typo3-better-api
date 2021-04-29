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
 * Last modified: 2020.08.27 at 22:43
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\Translation;


use LaborDigital\T3BA\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;

class TranslationConfigurator extends AbstractExtConfigConfigurator
{
    
    /**
     * The list of registered translation contexts
     *
     * @var array
     */
    protected $namespaces = [];
    
    /**
     * The list of registered override files
     *
     * @var array
     */
    protected $overrideFiles = [];
    
    /**
     * The list of single label overrides
     *
     * @var array
     */
    protected $overrideLabels = [];
    
    /**
     * Registers a new translation namespace. A namespace is basically a shortcut for the long LLL:EXT:...xlf:
     * part of your translation label.
     *
     * @param   string  $namespace  The shortname you want to use. -> Your namespace
     * @param   string  $filename   The full filename which should begin with EXT:ext_key/....
     *                              If there is no path but only a filename is given, the default path will
     *                              automatically be prepended. So: locallang_custom.xlf becomes
     *                              EXT:{{extKey}}/Resources/Private/Language/locallang_custom.xlf
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Translation\TranslationConfigurator
     * @see TranslationService::addContext()
     */
    public function registerNamespace(
        string $namespace,
        string $filename = 'EXT:{{extKey}}/Resources/Private/Language/locallang.xlf'
    ): self
    {
        if (basename($filename) === $filename) {
            $filename = 'EXT:{{extKey}}/Resources/Private/Language/' . $filename;
        }
        
        $this->namespaces[$this->context->replaceMarkers($namespace)] = $this->context->replaceMarkers($filename);
        
        return $this;
    }
    
    /**
     * This method is used to register a complete language file override.
     *
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/7.6/Internationalization/Translation/Index.html#custom-translations
     *
     * @param   string  $original  The path to the original file you want to override
     * @param   string  $override  The path to the file you want to override the original with
     * @param   string  $lang      The language to override the file in
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Translation\TranslationConfigurator
     */
    public function registerOverrideFile(
        string $original,
        string $override,
        string $lang = 'en'
    ): self
    {
        $this->overrideFiles[$lang]
        [$this->context->replaceMarkers($original)]
        [md5($this->context->replaceMarkers($override))]
            = $this->context->replaceMarkers($override);
        
        return $this;
    }
    
    /**
     * Registers a label override for a single translation label.
     * Both labels should either be something like: EXT:ext/Resources/Private/Language/locallang_db.xlf:key
     * or if you are using translation namespaces something like namespace.key
     *
     * @param   string  $original  The label to override
     * @param   string  $override  The label to override $original with
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Translation\TranslationConfigurator
     */
    public function registerOverride(string $original, string $override): self
    {
        $this->overrideLabels[$this->context->replaceMarkers($original)]
            = $this->context->replaceMarkers($override);
        
        return $this;
    }
    
    /**
     * The same as registerOverride() but can register multiple overrides at once using an array of
     * $original => $override...
     *
     * @param   array  $overrides
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\Translation\TranslationConfigurator
     * @see registerOverride()
     */
    public function registerMultipleOverrides(array $overrides): self
    {
        foreach ($overrides as $k => $v) {
            $this->registerOverride($k, $v);
        }
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function finish(ConfigState $state): void
    {
        $state->mergeIntoArray('typo.globals.TYPO3_CONF_VARS.SYS.locallangXMLOverride', $this->overrideFiles);
        $this->overrideFiles = null;
        
        parent::finish($state);
    }
    
    
}
