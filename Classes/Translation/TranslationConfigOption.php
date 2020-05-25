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

namespace LaborDigital\Typo3BetterApi\Translation;

use LaborDigital\Typo3BetterApi\ExtConfig\Option\AbstractExtConfigOption;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Class TranslationConfigOption
 *
 * Can be used to configure the translator instance
 *
 * @package LaborDigital\Typo3BetterApi\Translation
 */
class TranslationConfigOption extends AbstractExtConfigOption implements SingletonInterface
{
    /**
     * @var \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    protected $translator;
    
    /**
     * TranslationConfigOption constructor.
     *
     * @param \LaborDigital\Typo3BetterApi\Translation\TranslationService $translator
     */
    public function __construct(TranslationService $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * Returns the translator instance to configure
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    public function getTranslator(): TranslationService
    {
        return $this->translator;
    }
    
    /**
     * Registers a new translation context.
     *
     * @param string $context  The shortname you want to use
     * @param string $filename The full filename which should begin with EXT:ext_key/....
     *                         If there is no path but only a filename is given, the default path will automatically
     *                         be prepended. So: locallang_custom.xlf becomes
     *                         EXT:{{extKey}}/Resources/Private/Language/locallang_custom.xlf
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationConfigOption
     * @see TranslationService::addContext()
     */
    public function registerContext(string $context, string $filename = 'EXT:{{extKey}}/Resources/Private/Language/locallang.xlf'): TranslationConfigOption
    {
        if (basename($filename) === $filename) {
            $filename = 'EXT:{{extKey}}/Resources/Private/Language/' . $filename;
        }
        $this->translator->addContext($this->replaceMarkers($context), $this->replaceMarkers($filename));
        return $this;
    }
    
    /**
     * This method is used to register a complete language file override.
     * Should be used in your ext_localconf.php
     * @see https://docs.typo3.org/typo3cms/CoreApiReference/7.6/Internationalization/Translation/Index.html#custom-translations
     *
     * @param string $original The path to the original file you want to override
     * @param string $override The path to the file you want to override the original with
     * @param string $lang     The language to override the file in
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationConfigOption
     */
    public function registerOverrideFile(string $original, string $override, string $lang = 'en'): TranslationConfigOption
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride'][$lang][$this->replaceMarkers($original)]
        [md5($override)] = $this->replaceMarkers($override);
        return $this;
    }
    
    /**
     * Registers a label override for a single translation label.
     * Both labels should either be something like: EXT:ext/Resources/Private/Language/locallang_db.xlf:key
     * or if you are using translation contexts something like context.key
     *
     * @param string $original The label to override
     * @param string $override The label to override $original with
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationConfigOption
     */
    public function registerOverride(string $original, string $override): TranslationConfigOption
    {
        $this->translator->registerOverride($this->replaceMarkers($original), $this->replaceMarkers($override));
        return $this;
    }
    
    /**
     * The same as registerOverride() but can register multiple overrides at once using an array of
     * $original => $override...
     *
     * @param array $overrides
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationConfigOption
     */
    public function registerMultipleOverrides(array $overrides): TranslationConfigOption
    {
        foreach ($overrides as $k => $v) {
            $this->registerOverride($k, $v);
        }
        return $this;
    }
}
