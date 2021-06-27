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


namespace LaborDigital\T3ba\ExtConfigHandler\TypoScript;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigConfigurator;
use Neunerlei\Inflection\Inflector;

class TypoScriptConfigurator extends AbstractExtConfigConfigurator implements NoDiInterface
{
    
    /**
     * The list of registered static directories
     *
     * @var array
     */
    protected $staticDirectories = [];
    
    /**
     * The dynamic typo script definitions to add for each key
     *
     * @var array
     */
    protected $dynamicTypoScript = [];
    
    /**
     * The collected user ts config
     *
     * @var string
     */
    protected $userTsConfig = '';
    
    /**
     * The collected page ts config
     *
     * @var string
     */
    protected $pageTsConfig = '';
    
    /**
     * The list of ts config files that should be selectable in a page TCA
     *
     * @var array
     */
    protected $selectablePageTsFiles = [];
    
    /**
     * Adds the static extension typoScript to the selection list.
     *
     * NOTE: this method expects a directory path, where a "setup.txt" and/or "constants.txt" is found!
     * It will not work if you specify a absolute file path! Use registerImport() for that!
     *
     * @param   string  $path        The typoScript configuration directory. But can
     *                               be any path inside the given extension. The path is relative to EXT:$_EXTKEY/
     * @param   string  $title       An optional title to be displayed in the backend
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerStaticTsDirectory(
        string $path = 'Configuration/TypoScript/',
        string $title = ''
    ): self
    {
        if (stripos($path, 'ext:') === 0) {
            $path = preg_replace('~(ext):/?.*?/~si', '', $path);
        }
        
        $this->staticDirectories[] = [
            $this->context->getExtKey(),
            $this->context->replaceMarkers($path),
            $this->context->replaceMarkers($title),
        ];
        
        return $this;
    }
    
    /**
     * Adds a new snippet of dynamic typo script to the registry.
     * Dynamic typoScript can be included into virtually any typoScript or tsConfig file using
     * the @import statement.
     *
     * For example: You add a snippet with key: "myKey" and the content "config.test = 123"
     *
     * In your real typo script file you can now include the dynamic content with @import "dynamic:myKey"
     * and with that your configuration will be loaded from the dynamic storage instead.
     *
     * NOTE: If a key already exists your content will be appended to it.
     *
     * @param   string  $key      A unique definition key to add the dynamic content with
     * @param   string  $content  The typoScript configuration to add for the key
     *
     * @return $this
     * @see \LaborDigital\T3ba\Tool\TypoScript\DynamicTypoScriptRegistry
     */
    public function registerDynamicContent(string $key, string $content): self
    {
        if (! isset($this->dynamicTypoScript[$key])) {
            $this->dynamicTypoScript[$key] = '';
        }
        
        $this->dynamicTypoScript[$key] .= '
[GLOBAL]
#############################################
# DYNAMIC TS OF: ' . $this->context->getExtKey() . '
#############################################
' . $this->context->replaceMarkers($content) . '
#############################################
[GLOBAL]
';
        
        return $this;
    }
    
    
    /**
     * Allows you to add a generic typoScript setup code.
     *
     * IMPORTANT: In order for your code to show up in your configuration you have to include the 'T3BA - Generic
     * TypoScript' static directory into your template definition
     *
     * @param   string  $setup  The typoScript you want to add to your template
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerSetup(string $setup): self
    {
        return $this->registerDynamicContent('generic.setup', $setup);
    }
    
    /**
     * Allows you to add a generic typoScript constant code.
     *
     * IMPORTANT: In order for your code to show up in your configuration you have to include the 'T3BA - Generic
     * TypoScript' static directory into your template definition
     *
     * @param   string  $constants  The typoScript you want to add to your template
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerConstants(string $constants): self
    {
        return $this->registerDynamicContent('generic.constants', $constants);
    }
    
    /**
     * Adds the a @include tag to the generic setup
     *
     * IMPORTANT: In order for your code to show up in your configuration you have to include the 'T3BA - Generic
     * TypoScript' static directory into your template definition
     *
     * @param   string  $path  The path to the file you want to include in your setup.
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/TypoScriptSyntax/Syntax/Includes.html#includes
     */
    public function registerImport(string $path): self
    {
        return $this->registerDynamicContent('generic.setup', '@import "' . $path . '"');
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addUserTSConfig.
     * Let's you add userTsConfig to the configuration tree
     *
     * @param   string  $config  The page ts config to append
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerUserTsConfig(string $config): self
    {
        $this->userTsConfig .= '
[GLOBAL]
#############################################
# DYNAMIC TS CONFIG OF: ' . $this->context->getExtKey() . '
#############################################
' . $this->context->replaceMarkers($config) . '
#############################################
[GLOBAL]
';
        
        return $this;
    }
    
    /**
     * Registers a static file as user ts config
     *
     * @param   string  $path  The path of the file to include.
     *                         The path should start with EXT:$_EXTKEY/...
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/TypoScriptSyntax/Syntax/Includes.html#includes
     */
    public function registerUserTsConfigImport(string $path = 'EXT:{{extKey}}/Configuration/TsConfig/User/'): self
    {
        return $this->registerUserTsConfig('@import "' . $path . '"');
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addPageTSConfig.
     * Let's you add pageTsConfig to the configuration tree
     *
     * @param   string  $config  The page ts config to append
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerPageTsConfig(string $config): self
    {
        $this->pageTsConfig .= '
[GLOBAL]
#############################################
# DYNAMIC TS CONFIG OF: ' . $this->context->getExtKey() . '
#############################################
' . $this->context->replaceMarkers($config) . '
#############################################
[GLOBAL]
';
        
        return $this;
    }
    
    /**
     * Registers a static file as page ts config
     *
     * @param   string  $path  The path of the file to include.
     *                         The path should start with EXT:$_EXTKEY/...
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/TypoScriptSyntax/Syntax/Includes.html#includes
     */
    public function registerPageTsConfigImport(string $path = 'EXT:{{extKey}}/Configuration/TsConfig/Page/'): self
    {
        return $this->registerPageTsConfig('@import "' . $path . '"');
    }
    
    /**
     * Registers a file which can be selected in the "TyposScript Configuration" section of page records in the page
     * backend. A registered file is not globally included but only on the pages it was selected.
     *
     * @param   string       $path   The path of the file to include.
     *                               The path is relative to EXT:$_EXTKEY/
     * @param   string|null  $title  Can be used to define a visible label for the file in the backend.
     *                               If omitted one is auto-generated for you
     *
     * @return \LaborDigital\T3ba\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerSelectablePageTsConfigFile(string $path, ?string $title = null): self
    {
        if (stripos($path, 'ext:') === 0) {
            $path = preg_replace('~(ext):/?.*?/~si', '', $path);
        }
        
        $this->selectablePageTsFiles[$path] = [
            $this->context->getExtKey(),
            trim($path, '\\/'),
            $title ?? (Inflector::toHuman($this->context->getExtKey()) . ' - ' . Inflector::toHuman(basename($path))),
        ];
        
        return $this;
    }
}
