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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\TypoScript;

use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class TypoScriptConfigOption
 *
 * Can be used to add typoScript and tsconfig to the setup
 *
 * @package LaborDigital\Typo3BetterApi\TypoScript
 */
class TypoScriptConfigOption extends AbstractExtConfigOption implements SingletonInterface
{
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService
     */
    protected $typoScript;
    
    public function __construct(TypoScriptService $typoScript)
    {
        $this->typoScript = $typoScript;
    }
    
    /**
     * Adds the static extension typoScript to the selection list.
     *
     * Note: this method expects a directory path where a "setup.txt" and/or "constants.txt" is found!
     * It will not work if you specify a absolute file path! Use register registerFileInSetup for that!
     *
     * @param string $path      default('Configuration/TypoScript/') The default typoScript configuration. But can be
     *                          any path inside the given extension
     * @param string $title     An optional title to be displayed in the backend
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerStaticTsDirectory(string $path = 'Configuration/TypoScript/', string $title = ''): TypoScriptConfigOption
    {
        $this->typoScript->addStaticTsDirectory($this->context->getExtKey(), $this->replaceMarkers($path), $this->replaceMarkers($title));
        return $this;
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addPageTSConfig.
     * Let's you add pageTsConfig to the configuration tree
     *
     * @param string $config The page ts config to append
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerPageTsConfig(string $config): TypoScriptConfigOption
    {
        $this->typoScript->addPageTsConfig($this->replaceMarkers($config));
        return $this;
    }
    
    /**
     * Registers a static file as page ts
     *
     * @param string      $path              The path of the file to include.
     *                                       The path should start with EXT:$_EXTKEY/...
     * @param bool        $selectablePerPage If this is set to true the file is not directly included
     *                                       but is instead registered to be selected in the "TyposScript
     *                                       Configuration" section of page records in the page backend
     * @param string|null $title             If $selectablePerPage is true this attribute can be used to define a
     *                                       visible label for the file in the backend. If omitted one is
     *                                       auto-generated
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerPageTsConfigFile(string $path, bool $selectablePerPage = false, ?string $title = null): TypoScriptConfigOption
    {
        $path = $this->replaceMarkers($path);
        if ($selectablePerPage) {
            $this->typoScript->addSelectablePageTsConfigFile($this->context->getExtKey(), $path, $title);
        } else {
            $this->typoScript->addPageTsConfig("<INCLUDE_TYPOSCRIPT: source=\"FILE:$path\">");
        }
        return $this;
    }
    
    /**
     * Registers all static files in a directory as global page ts config
     *
     * @param string $path      The path of the file to include.
     *                          The path should start with EXT:$_EXTKEY/...
     * @param string $extension The file extension to load
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerPageTsConfigDirectory(string $path = 'EXT:{{extKey}}/Configuration/TsConfig/Page/', string $extension = 'typoscript'): TypoScriptConfigOption
    {
        $path = $this->replaceMarkers($path);
        $this->typoScript->addPageTsConfig("<INCLUDE_TYPOSCRIPT: source=\"DIR:$path\" extension=\"$extension\">");
        return $this;
    }
    
    /**
     * Shortcut, reminder and bridge to ExtensionManagementUtility::addUserTSConfig.
     * Let's you add userTsConfig to the configuration tree
     *
     * @param string $config The page ts config to append
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerUserTsConfig(string $config): TypoScriptConfigOption
    {
        $this->typoScript->addUserTsConfig($this->replaceMarkers($config));
        return $this;
    }
    
    /**
     * Registers a static file as user ts
     *
     * @param string $path The path of the file to include.
     *                     The path should start with EXT:$_EXTKEY/...
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerUserTsConfigFile(string $path): TypoScriptConfigOption
    {
        $path = $this->replaceMarkers($path);
        $this->typoScript->addUserTsConfig("<INCLUDE_TYPOSCRIPT: source=\"FILE:$path\">");
        return $this;
    }
    
    /**
     * Registers all static files in a directory as global user ts config
     *
     * @param string $path      The path of the file to include.
     *                          The path should start with EXT:$_EXTKEY/...
     * @param string $extension The file extension to load
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     */
    public function registerUserTsConfigDirectory(string $path, string $extension = 'typoscript'): TypoScriptConfigOption
    {
        $path = $this->replaceMarkers($path);
        $this->typoScript->addUserTsConfig("<INCLUDE_TYPOSCRIPT: source=\"DIR:$path\" extension=\"$extension\">");
        return $this;
    }
    
    /**
     * Advanced option to add typoScript to the template
     *
     * @param string $setup   The typoScript you want to add to your template
     * @param array  $options Additional config options
     *                        - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                        - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                        addConstant() calls refer to the same title, their content will be merged into a single
     *                        file
     *                        - constants (string): Can be used to pass along additional constants for your script
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     *
     * @see \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService::addSetup()
     */
    public function registerSetup(string $setup, array $options = []): TypoScriptConfigOption
    {
        if (empty($options['extension'])) {
            $options['extension'] = $this->context->getExtKey();
        }
        $this->typoScript->addSetup($this->replaceMarkers($setup), $this->replaceMarkers($options));
        return $this;
    }
    
    /**
     * Adds the a <INCLUDE_TYPOSCRIPT> tag to the template
     *
     * @param string $path    The path to the file you want to include in your setup.
     * @param array  $options Can be used to pass additional options
     *                        - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                        - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                        addConstant() calls refer to the same title, their content will be merged into a single
     *                        file
     *                        - constants (string): Can be used to pass along additional constants for your script
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     *
     * @see \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService::addFileToSetup()
     */
    public function registerFileInSetup(string $path, array $options = []): TypoScriptConfigOption
    {
        if (empty($options['extension'])) {
            $options['extension'] = $this->context->getExtKey();
        }
        $this->typoScript->addFileToSetup($this->replaceMarkers($path), $this->replaceMarkers($options));
        return $this;
    }
    
    /**
     * Adds typoScript constants to the template
     *
     * @param string $constants The typoScript you want to add to your template
     * @param array  $options   Additional options
     *                          - extension (string): Descriptive only. Shows beside the title in the backend UI
     *                          - title (string): A unique title for your template. NOTE: If multiple addToSetup() or
     *                          addConstant() calls refer to the same title, their content will be merged into a single
     *                          file
     *
     * @return \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptConfigOption
     *
     * @see \LaborDigital\Typo3BetterApi\TypoScript\TypoScriptService::addConstants()
     */
    public function registerConstants(string $constants, array $options = []): TypoScriptConfigOption
    {
        if (empty($options['extension'])) {
            $options['extension'] = $this->context->getExtKey();
        }
        $this->typoScript->addConstants($this->replaceMarkers($constants), $this->replaceMarkers($options));
        return $this;
    }
}
