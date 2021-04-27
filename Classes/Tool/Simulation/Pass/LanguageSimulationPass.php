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
 * Last modified: 2020.07.16 at 21:06
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Simulation\Pass;


use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use LaborDigital\T3BA\Tool\Translation\Translator;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageSimulationPass implements SimulatorPassInterface
{

    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    private $typoContext;

    /**
     * LanguageSimulationPass constructor.
     *
     * @param   TypoContext  $typoContext
     * @param   Translator   $translator
     */
    public function __construct(TypoContext $typoContext, Translator $translator)
    {
        $this->typoContext = $typoContext;
        $this->translator  = $translator;
    }

    /**
     * @inheritDoc
     */
    public function addOptionDefinition(array $options): array
    {
        $options['language']         = [
            'type'    => ['int', 'string', 'null', SiteLanguage::class],
            'default' => null,
        ];
        $options['fallbackLanguage'] = [
            'type'    => ['int', 'string', 'null', SiteLanguage::class, 'true'],
            'default' => null,
        ];

        return $options;
    }

    /**
     * @inheritDoc
     */
    public function requireSimulation(array $options, array &$storage): bool
    {
        if ($options['language'] === null && $options['fallbackLanguage'] === null) {
            // Check if there is no $GLOBALS['LANG'] -> do something about that
            if (! isset($GLOBALS['LANG'])) {
                return $storage['fallback'] = true;
            }

            return false;
        }

        $languageObject = $this->resolveLanguageObject($options['language'], $options['fallbackLanguage']);

        return $languageObject->getLanguageId() !==
               $this->typoContext->language()->getCurrentFrontendLanguage()->getLanguageId();
    }

    /**
     * @inheritDoc
     */
    public function setup(array $options, array &$storage): void
    {
        // Check if we have to provide the language service
        if ($storage['fallback']) {
            $storage['service'] = $GLOBALS['LANG'];
            $GLOBALS['LANG']    = $this->translator->getTypoLanguageService();

            return;
        }

        // Create backup
        $storage['request'] = $this->typoContext->config()->getRequestAttribute('language');
        $storage['service'] = $GLOBALS['LANG'];
        $storage['aspect']  = $this->typoContext->getRootContext()->getAspect('language');
        $storage['locale']  = setlocale(LC_ALL, 0);

        // Update the language
        $languageObject = $this->resolveLanguageObject($options['language'], $options['fallbackLanguage']);
        Locales::setSystemLocaleFromSiteLanguage($languageObject);
        $this->typoContext->config()->setRequestAttribute('language', $languageObject);
        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($languageObject);
        $this->typoContext->getRootContext()->setAspect('language', $languageAspect);
        unset($GLOBALS['LANG']);
        $GLOBALS['LANG'] = $this->translator->getTypoLanguageService();
    }

    /**
     * @inheritDoc
     */
    public function rollBack(array $storage): void
    {
        $GLOBALS['LANG'] = $storage['service'];
        if ($storage['fallback']) {
            return;
        }

        $this->typoContext->getRootContext()->setAspect('language', $storage['aspect']);
        $this->typoContext->config()->setRequestAttribute('language', $storage['request']);
        $localeLang = $storage['request'] ?? new SiteLanguage(1, $storage['locale'], new Uri(), []);
        Locales::setSystemLocaleFromSiteLanguage($localeLang);
    }

    /**
     * Internal helper to resolve the language by a multitude of different formats
     *
     * @param   int|string|SiteLanguage            $language          The language to set the frontend to.
     *                                                                Either as sys_language_uid value or as language
     *                                                                object
     *
     * @param   int|string|SiteLanguage|true|null  $fallbackLanguage  The language which should be used when the
     *                                                                $language was not found for this site. If true is
     *                                                                given, the default language will be used
     *
     * @return mixed|\TYPO3\CMS\Core\Site\Entity\SiteLanguage
     */
    protected function resolveLanguageObject($language, $fallbackLanguage = null)
    {
        if (! is_object($language)) {
            $languages = $this->typoContext->site()->getCurrent()->getLanguages();
            foreach ($languages as $lang) {
                if (
                    (is_numeric($language) && $lang->getLanguageId() === (int)$language)
                    || strtolower($lang->getTwoLetterIsoCode()) === $language) {
                    $language = $lang;
                    break;
                }
            }
        }
        if (! $language instanceof SiteLanguage) {
            if ($fallbackLanguage !== null) {
                if ($fallbackLanguage === true) {
                    $fallbackLanguage = $this->typoContext->site()->getCurrent()->getDefaultLanguage();
                }

                return $this->resolveLanguageObject($fallbackLanguage);
            }
            throw new InvalidArgumentException('Could not determine the site language for the given language value!');
        }

        return $language;
    }

}
