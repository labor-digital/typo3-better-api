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
 * Last modified: 2020.03.19 at 11:25
 */

namespace LaborDigital\Typo3BetterApi\Translation;

use LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters\TsfeAdapter;
use LaborDigital\Typo3BetterApi\Event\Events\ExtConfigLoadedEvent;
use LaborDigital\Typo3BetterApi\Tsfe\TsfeService;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\EventBusInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class TranslationService implements SingletonInterface
{
    protected const PARSE_RESULT_ALREADY_TRANSKEY = 0;
    protected const PARSE_RESULT_NOT_TRANSLATABLE = 1;
    protected const PARSE_RESULT_OK               = 2;
    
    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\Tsfe\TsfeService
     */
    protected $tsfe;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $typoContext;
    
    /**
     * The list of registered contexts
     * @var array
     */
    protected $contexts = [];
    
    /**
     * A list of labels and their overrides
     * @var array
     */
    protected $overrides = [];
    
    /**
     * TranslationService constructor.
     *
     * @param \Neunerlei\EventBus\EventBusInterface                $eventBus
     * @param \LaborDigital\Typo3BetterApi\Tsfe\TsfeService        $tsfe
     * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext $typoContext
     */
    public function __construct(EventBusInterface $eventBus, TsfeService $tsfe, TypoContext $typoContext)
    {
        $this->eventBus = $eventBus;
        $this->tsfe = $tsfe;
        $this->typoContext = $typoContext;
    }
    
    /**
     * Registers a new translation context.
     *
     * A context is always a replacement for typo's way of defining the whole filepath.
     * Use this to convert your LLL:EXT:my_ext/Resources/Private/Language/locallang.xlf to a simple: my_ext, myExt or
     * whatever you like.
     *
     * @param string $context  The shortname you want to use
     * @param string $filename The full filename which should begin with EXT:ext_key/....
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    public function addContext(string $context, string $filename): TranslationService
    {
        
        // Prepare filename
        $filename = trim($filename);
        if (stripos($filename, 'lll:') === 0) {
            $filename = substr($filename, 0, 5);
        }
        $filename = $this->typoContext->getPathAspect()->realPathToTypoExt($filename);
        
        // Store context
        $this->contexts[$context] = $filename;
        
        // Done
        return $this;
    }
    
    /**
     * Returns the list of registered contexts as key (context) -> value (filename) pairs
     * @return array
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }
    
    /**
     * Sets a list of contexts which should be specified as key (context) -> value (filename) pairs
     *
     * @param array $contexts
     *
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    public function setContexts(array $contexts): TranslationService
    {
        $this->contexts = [];
        foreach ($contexts as $k => $v) {
            $this->addContext($k, $v);
        }
        return $this;
    }
    
    /**
     * Returns true if the given context was registered, false if not
     *
     * @param string $context
     *
     * @return bool
     */
    public function hasContext(string $context): bool
    {
        return isset($this->contexts[$context]);
    }
    
    /**
     * Returns the filename of a given context
     *
     * @param string $context           The key of the context to retrieve the file from
     * @param bool   $withTripleLPrefix True to add LLL: before the filename
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    public function getContextFile(string $context, bool $withTripleLPrefix = false)
    {
        $this->requireContext($context);
        $result = $this->contexts[$context];
        return $withTripleLPrefix ? 'LLL:' . $result : $result;
    }
    
    /**
     * Creates the typo3 translation key (LLL:filename.xlf:key) from a given
     * translation selector
     *
     * @param string $selector The selector to translate to a real translation key
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    public function getTranslationKey(string $selector): string
    {
        $pr = $this->parseSelector($selector, $context);
        if ($pr === static::PARSE_RESULT_ALREADY_TRANSKEY) {
            return $this->resolveOverride($selector);
        }
        if ($pr === static::PARSE_RESULT_NOT_TRANSLATABLE) {
            $this->requireContext($context, $selector);
        }
        return $this->resolveOverride($this->getContextFile($context, true) . ':' . $selector);
    }
    
    /**
     * Creates the typo3 translation key (LLL:filename.xlf:key) from a given
     * translation selector, but returns the given $selector if the selector seems not to be translatable.
     *
     * @param string $selector
     *
     * @return string
     */
    public function getTranslationKeyMaybe(string $selector): string
    {
        try {
            return $this->getTranslationKey($selector);
        } catch (TranslationException $exception) {
            return $selector;
        }
    }
    
    /**
     * Checks if a given selector is translatable by any means.
     * Checks if it starts with LLL: or if the part before the first . is a valid context
     *
     * @param string $selector The value to check for the ability to be translated
     *
     * @return bool True if value is translatable, false if not.
     */
    public function isTranslatable(string $selector): bool
    {
        return $this->parseSelector($selector) !== static::PARSE_RESULT_NOT_TRANSLATABLE;
    }
    
    /**
     * This method can be used to translate selectors / language labels into their speaking counterpart.
     * It should work in all three contexts (FE, BE and CLI) and also works with both default labels and context labels
     *
     * You can also pass additional values by adding them as function params,
     * which will be replaced via vsprintf, or you can pass an array of values as
     * the second param of this function to replace them in your output string.
     *
     * @param string $selector The selector to translate into
     * @param mixed  $args     Arguments to replace with placeholders
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    public function translate(string $selector, array $args = []): string
    {
        $key = $this->getTranslationKey($selector);
        if ($this->tsfe->hasTsfe()) {
            $result = $this->tsfe->getTsfe()->sL($key);
        } else {
            $result = $this->getTypoLanguageService()->sl($key);
        }
        if (!is_string($result)) {
            $result = '';
        }
        if (!empty($args)) {
            $result = vsprintf($result, $args);
        }
        return $result;
    }
    
    /**
     * The same as translate() but will return the $selector, if it does not look like it is translatable
     *
     * @param string $selector The selector to translate into
     * @param mixed  $args     Arguments to replace with placeholders
     *
     * @return string
     */
    public function translateMaybe(string $selector, array $args = []): string
    {
        try {
            $result = $this->translate($selector, $args);
        } catch (TranslationException $exception) {
            $result = $selector;
        }
        if (empty($result) && $this->isTranslatable($selector)) {
            return '';
        }
        return $result;
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
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     */
    public function registerOverrideFile(string $original, string $override, string $lang = 'en'): TranslationService
    {
        $this->eventBus->addListener(ExtConfigLoadedEvent::class, function () use ($lang, $original, $override) {
            // Hook into the base "api"
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['locallangXMLOverride'][$lang][$original][md5($override)] = $override;
        });
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
     * @return \LaborDigital\Typo3BetterApi\Translation\TranslationService
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    public function registerOverride(string $original, string $override): TranslationService
    {
        $original = $this->getTranslationKey($original);
        $override = $this->getTranslationKeyMaybe($override);
        $this->overrides[$original] = $override;
        return $this;
    }
    
    /**
     * Returns the instance of typo3's backend translation service or.
     * If the instance currently not exists at $GLOBALS['LANG'] we will forcefully create one
     *
     * @return \TYPO3\CMS\Lang\LanguageService|\TYPO3\CMS\Core\Localization\LanguageService
     */
    public function getTypoLanguageService(): LanguageService
    {
        if ($this->tsfe->hasTsfe()) {
            return TsfeAdapter::getLanguageService($this->tsfe->getTsfe());
        }
        
        if (!is_object($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $lang = $this->typoContext->getLanguageAspect()->getCurrentFrontendLanguage()->getTwoLetterIsoCode();
            $GLOBALS['LANG']->init($lang === 'en' ? 'default' : $lang);
        }
        return $GLOBALS['LANG'];
    }
    
    /**
     * Returns all available labels in a given translation file.
     *
     * @param string $filename Either the LLL:EXT:...xlf filename to the file or a registered context
     *
     * @return array
     */
    public function getAllKeysInFile(string $filename): array
    {
        if ($this->hasContext($filename)) {
            $filename = $this->getContextFile($filename);
        }
        $languageService = $this->getTypoLanguageService();
        $backupLang = $languageService->lang;
        $languageService->lang = 'default';
        $labels = $languageService->includeLLFile($filename, false);
        $labels = array_keys(Arrays::getPath($labels, ['default'], []));
        $labels = array_combine($labels, $labels);
        $labels = array_map(function ($v) use ($filename) {
            return $filename . ':' . $v;
        }, $labels);
        $languageService->lang = $backupLang;
        return $labels;
    }
    
    /**
     * Parses the given selector into it's real selector (aka. lookup path) and the context.
     * Will return one of the PARSE_RESULT constants to signalize what to do with the result
     *
     * @param string $selector
     * @param null   $context
     *
     * @return int
     */
    protected function parseSelector(string &$selector, &$context = null): int
    {
        $selectorTrimmed = trim($selector);
        
        // Unify path's relative to an extension
        if (stripos($selectorTrimmed, 'lll:') !== 0) {
            if (stripos($selectorTrimmed, 'ext:') === 0) {
                $selector = 'LLL:' . $selectorTrimmed;
                return self::PARSE_RESULT_ALREADY_TRANSKEY;
            }
        } else {
            return self::PARSE_RESULT_ALREADY_TRANSKEY;
        }
        
        // Get context from selector
        $separatorPos = (int)stripos($selectorTrimmed, '.');
        $context = substr($selectorTrimmed, 0, $separatorPos);
        
        // Check if we have the context
        if (!$this->hasContext($context)) {
            return static::PARSE_RESULT_NOT_TRANSLATABLE;
        }
        $selector = substr($selectorTrimmed, $separatorPos + 1);
        return static::PARSE_RESULT_OK;
    }
    
    /**
     * Internal helper which is used to resolve overridden selectors
     *
     * @param string $selector The selector to resolve
     *
     * @return string
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    protected function resolveOverride(string $selector): string
    {
        if (empty($this->overrides)) {
            return $selector;
        }
        
        // Unify path's relative to an extension
        if (stripos($selector, 'lll:') !== 0) {
            $selector = 'LLL:' . $selector;
        }
        if (stripos($selector, 'lll:ext:') !== 0) {
            $parts = explode(':', $selector);
            $selector = array_shift($parts) . $this->typoContext->getPathAspect()->realPathToTypoExt(array_shift($parts)) . ':' . implode(':', $parts);
        }
        
        // Resolve the overrides
        $c = 0;
        while (isset($this->overrides[$selector])) {
            $selector = $this->overrides[$selector];
            if ($c++ > 10) {
                throw new TranslationException('More than 10 subsequent overrides are not supported. Maybe a circular override?');
            }
        }
        
        // Done
        return $selector;
    }
    
    /**
     * Internal helper to FORCE that a given context exists.
     * If context does NOT EXIST the script will throw an exception
     *
     * @param string      $context  The key of the context to check for
     * @param string|null $selector Optional value to render the failing selector in the message
     *
     * @throws \LaborDigital\Typo3BetterApi\Translation\TranslationException
     */
    protected function requireContext(string $context, ?string $selector = null)
    {
        if (!$this->hasContext($context)) {
            $selector = !empty($selector) ? ' for selector: "' . $selector . '"' : '';
            throw new TranslationException(
                'Your translation requires a missing context: "' . $context . '"' . $selector
            );
        }
    }
}
