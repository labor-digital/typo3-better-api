<?php
declare(strict_types=1);
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
 * Last modified: 2020.08.23 at 23:23
 */

namespace LaborDigital\T3BA\Tool\Translation;

use LaborDigital\T3BA\Tool\Tsfe\TsfeService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Configuration\State\ConfigState;
use Neunerlei\Configuration\State\LocallyCachedStatePropertyTrait;
use Neunerlei\EventBus\EventBusInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Translator implements SingletonInterface
{
    use TypoContextAwareTrait;
    use LocallyCachedStatePropertyTrait;

    protected const PARSE_RESULT_ALREADY_TRANS_KEY = 0;
    protected const PARSE_RESULT_NOT_TRANSLATABLE  = 1;
    protected const PARSE_RESULT_OK                = 2;

    /**
     * @var \Neunerlei\EventBus\EventBusInterface
     */
    protected $eventBus;

    /**
     * @var \LaborDigital\T3BA\Tool\Tsfe\TsfeService
     */
    protected $tsfe;

    /**
     * The list of cached namespaces for faster lookups
     * -> This is loaded from the config state at: typo.translation.overrideFiles
     *
     * @var array|null
     */
    protected $namespaces;

    /**
     * The list of cached labels and their overrides
     * -> This is loaded from the config state at: typo.translation.overrideLabels
     *
     * @var array|null
     */
    protected $overrides;

    /**
     * A cached list of translatable and non translatable selectors for faster lookups
     * A key value list using true for translatable, false for non translatable selectors
     *
     * @var array
     */
    protected $translatableSelectorMap = [];

    /**
     * The cached map between a possible selector and it's resolved label for faster lookups
     *
     * @var array
     */
    protected $selectorLabelMap = [];

    /**
     * TranslationService constructor.
     *
     * @param   \Neunerlei\EventBus\EventBusInterface       $eventBus
     * @param   \LaborDigital\T3BA\Tool\Tsfe\TsfeService    $tsfe
     * @param   \Neunerlei\Configuration\State\ConfigState  $configState
     */
    public function __construct(EventBusInterface $eventBus, TsfeService $tsfe, ConfigState $configState)
    {
        $this->eventBus = $eventBus;
        $this->tsfe     = $tsfe;
        $this->registerCachedProperty('namespaces', 'typo.translation.namespaces', $configState);
        $this->registerCachedProperty('overrides', 'typo.translation.overrideLabels', $configState);
        $configState->addWatcher('typo.translation', function () {
            // Clear local caches if something changed in the configuration
            $this->translatableSelectorMap = [];
            $this->selectorLabelMap        = [];
        });
    }

    /**
     * Returns true if the given namespace was registered, false if not
     *
     * @param   string  $namespaceName
     *
     * @return bool
     */
    public function hasNamespace(string $namespaceName): bool
    {
        return isset($this->namespaces[$namespaceName]);
    }

    /**
     * Returns the list of all registered namespaces and their matching file names
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Returns the filename of a given context
     *
     * @param   string  $namespaceName      The key of the context to retrieve the file from
     * @param   bool    $withTripleLPrefix  True to add LLL: before the filename
     *
     * @return string
     */
    public function getNamespaceFile(string $namespaceName, bool $withTripleLPrefix = false): string
    {
        $result = $this->requireNamespace($namespaceName);

        return $withTripleLPrefix ? 'LLL:' . $result : $result;
    }

    /**
     * Checks if a given selector is translatable by any means.
     * Checks if it starts with LLL: or if the part before the first . is a valid context
     *
     * @param   string  $selector  The value to check for the ability to be translated
     *
     * @return bool True if value is translatable, false if not.
     */
    public function isTranslatable(string $selector): bool
    {
        if (isset($this->selectorLabelMap[$selector])) {
            return true;
        }

        $cacheKey = md5($selector);

        return $this->translatableSelectorMap[$cacheKey] ??
               $this->translatableSelectorMap[$cacheKey]
                   = $this->parseSelector($selector) !== static::PARSE_RESULT_NOT_TRANSLATABLE;
    }

    /**
     * Creates the typo3 translation/label key (LLL:filename.xlf:key) from a given
     * translation selector, but returns the given $selector if the selector seems not to be translatable.
     *
     * @param   string  $selector  The selector to translate to a real translation key
     *
     * @return string
     */
    public function getLabelKey(string $selector): string
    {
        if (isset($this->selectorLabelMap[$selector])) {
            if ($this->selectorLabelMap[$selector] === false) {
                return $selector;
            }

            return $this->selectorLabelMap[$selector];
        }

        $pr = $this->parseSelector($selector, $context);

        if ($pr === static::PARSE_RESULT_ALREADY_TRANS_KEY) {
            return $this->selectorLabelMap[$selector] = $this->resolveOverride($selector);
        }

        if ($pr === static::PARSE_RESULT_NOT_TRANSLATABLE) {
            $this->translatableSelectorMap[md5($selector)] = false;
            $this->selectorLabelMap[$selector]             = false;

            return $selector;
        }

        return $this->selectorLabelMap[$selector]
            = $this->resolveOverride($this->getNamespaceFile($context, true) . ':' . $selector);
    }

    /**
     * This method can be used to translate selectors / language labels into their speaking counterpart.
     * It should work in all three contexts (FE, BE and CLI) and also works with both default labels and context labels
     *
     * You can also pass additional values by adding them as function params,
     * which will be replaced via vsprintf, or you can pass an array of values as
     * the second param of this function to replace them in your output string.
     *
     * @param   string  $selector  The selector to translate into
     * @param   mixed   $args      Arguments to replace with placeholders
     *
     * @return string
     */
    public function translate(string $selector, array $args = []): string
    {
        $key = $this->getLabelKey($selector);

        if ($key === $selector && strpos($selector, 'LLL:') !== 0) {
            return $selector;
        }

        if ($this->tsfe->hasTsfe()) {
            $result = (string)$this->tsfe->getTsfe()->sL($key);
        } else {
            $result = (string)$this->getTypoLanguageService()->sl($key);
        }

        return empty($args) ? $result : vsprintf($result, $args);
    }

    /**
     * Returns the instance of typo3's backend translation service or.
     * If the instance currently not exists at $GLOBALS['LANG'] we will forcefully create one
     *
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    public function getTypoLanguageService(): LanguageService
    {
        if ($this->tsfe->hasTsfe()) {
            return TsfeAdapter::getLanguageService($this->tsfe->getTsfe());
        }

        if (! is_object($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
            $lang            = $this->TypoContext()
                                    ->Language()
                                    ->getCurrentFrontendLanguage()
                                    ->getTwoLetterIsoCode();
            $GLOBALS['LANG']->init($lang === 'en' ? 'default' : $lang);
        }

        return $GLOBALS['LANG'];
    }

    /**
     * Returns all available labels in a given translation file.
     *
     * @param   string  $filename  Either the LLL:EXT:...xlf filename to the file or a registered namespaces
     *
     * @return array
     */
    public function getAllKeysInFile(string $filename): array
    {
        if ($this->hasNamespace($filename)) {
            $filename = $this->getNamespaceFile($filename);
        }
        $languageService       = $this->getTypoLanguageService();
        $backupLang            = $languageService->lang;
        $languageService->lang = 'default';
        $labels                = $languageService->includeLLFile($filename, false);
        $labels                = array_keys(Arrays::getPath($labels, ['default'], []));
        $labels                = array_combine($labels, $labels);
        $labels                = array_map(static function ($v) use ($filename) {
            return $filename . ':' . $v;
        }, $labels);
        $languageService->lang = $backupLang;

        return $labels;
    }

    /**
     * Parses the given selector into it's real selector (aka. lookup path) and the context.
     * Will return one of the PARSE_RESULT constants to signalize what to do with the result
     *
     * @param   string  $selector
     * @param   null    $namespace
     *
     * @return int
     */
    protected function parseSelector(string &$selector, &$namespace = null): int
    {
        $selectorTrimmed = trim($selector);

        // Unify path's relative to an extension
        if (stripos($selectorTrimmed, 'lll:') !== 0) {
            if (stripos($selectorTrimmed, 'ext:') === 0) {
                $selector = 'LLL:' . $selectorTrimmed;

                return self::PARSE_RESULT_ALREADY_TRANS_KEY;
            }
        } else {
            return self::PARSE_RESULT_ALREADY_TRANS_KEY;
        }

        // Get context from selector
        $separatorPos = (int)strpos($selectorTrimmed, '.');
        $namespace    = substr($selectorTrimmed, 0, $separatorPos);

        // Check if we have the context
        if (! $this->hasNamespace($namespace)) {
            return static::PARSE_RESULT_NOT_TRANSLATABLE;
        }
        $selector = substr($selectorTrimmed, $separatorPos + 1);

        return static::PARSE_RESULT_OK;
    }

    /**
     * Internal helper which is used to resolve overridden selectors
     *
     * @param   string  $selector  The selector to resolve
     *
     * @return string
     * @throws \LaborDigital\T3BA\Tool\Translation\TranslationException
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
            $parts    = explode(':', $selector);
            $selector = array_shift($parts) .
                        $this->TypoContext()->Path()->realPathToTypoExt(array_shift($parts)) . ':'
                        . implode(':', $parts);
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
     * Internal helper to FORCE that a given namespace exists.
     * If the namespace does NOT EXIST the script will throw an exception
     *
     * @param   string       $namespaceName  The name of the namespace to check for
     * @param   string|null  $selector       Optional value to render the failing selector in the message
     *
     * @return string
     * @throws \LaborDigital\T3BA\Tool\Translation\TranslationException
     */
    protected function requireNamespace(string $namespaceName, ?string $selector = null): string
    {
        if (! isset($this->namespaces[$namespaceName])) {
            $selector = ! empty($selector) ? ' for selector: "' . $selector . '"' : '';
            throw new TranslationException(
                'Your translation requires a missing context: "' . $namespaceName . '"' . $selector
            );
        }

        return $this->namespaces[$namespaceName];
    }
}
