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
 * Last modified: 2020.08.25 at 10:07
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfigHandler\TypoScript;


use LaborDigital\T3BA\ExtConfig\AbstractExtConfigConfigurator;
use Neunerlei\Configuration\State\ConfigState;

class TypoScriptConfigurator extends AbstractExtConfigConfigurator
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
     * Adds the static extension typoScript to the selection list.
     *
     * Note: this method expects a directory path where a "setup.txt" and/or "constants.txt" is found!
     * It will not work if you specify a absolute file path! Use register registerFileInSetup for that!
     *
     * @param   string  $path   default('Configuration/TypoScript/') The default typoScript configuration. But can be
     *                          any path inside the given extension
     * @param   string  $title  An optional title to be displayed in the backend
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     */
    public function registerStaticTsDirectory(
        string $path = 'Configuration/TypoScript/',
        string $title = ''
    ): self {
        // Fix incorrect path's that start with EXT:something...
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
     * @see \LaborDigital\T3BA\Tool\TypoScript\DynamicTypoScriptRegistry
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
     * @return \LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator
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
     * @return \LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator
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
     * @return \LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/TypoScriptSyntax/Syntax/Includes.html#includes
     */
    public function registerImport(string $path): self
    {
        return $this->registerDynamicContent(
            'generic.setup', '@import "' . $this->context->replaceMarkers($path) . '"');
    }

    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addUserTSConfig.
     * Let's you add userTsConfig to the configuration tree
     *
     * @param   string  $config  The page ts config to append
     *
     * @return \LaborDigital\T3BA\ExtConfigHandler\TypoScript\TypoScriptConfigurator
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
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/TypoScriptSyntax/Syntax/Includes.html#includes
     */
    public function registerUserTsConfigImport(string $path): self
    {
        return $this->registerUserTsConfig('@import "' . $path . '"');
    }

    /**
     * Internal helper to store the configuration on the config state
     *
     * @param   \Neunerlei\Configuration\State\ConfigState  $state
     */
    public function finish(ConfigState $state): void
    {
        $state->set('staticDirectories', $this->staticDirectories);
        $state->set('dynamic', $this->dynamicTypoScript);
    }
}
