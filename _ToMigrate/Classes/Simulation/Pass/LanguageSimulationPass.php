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


namespace LaborDigital\Typo3BetterApi\Simulation\Pass;


use InvalidArgumentException;
use LaborDigital\Typo3BetterApi\Container\CommonDependencyTrait;
use TYPO3\CMS\Core\Context\LanguageAspectFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageSimulationPass implements SimulatorPassInterface
{
    use CommonDependencyTrait;
    
    protected $requestLangBackup;
    protected $langServiceBackup;
    protected $aspectBackup;
    
    /**
     * True if $GLOBALS['LANG'] is empty and we provide a fallback for it
     *
     * @var bool
     */
    protected $provideLangFallback = false;
    
    /**
     * @inheritDoc
     */
    public function __construct() { }
    
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
    public function requireSimulation(array $options): bool
    {
        if ($options['language'] === null && $options['fallbackLanguage'] === null) {
            // Check if there is no $GLOBALS['LANG'] -> do something about that
            if (! isset($GLOBALS['LANG'])) {
                return $this->provideLangFallback = true;
            }
            
            return false;
        }
        
        $languageObject = $this->resolveLanguageObject($options['language'], $options['fallbackLanguage']);
        
        return $languageObject->getLanguageId() !==
               $this->TypoContext()->Language()->getCurrentFrontendLanguage()->getLanguageId();
    }
    
    /**
     * @inheritDoc
     */
    public function setup(array $options): void
    {
        // Check if we have to provide the language service
        if ($this->provideLangFallback) {
            $this->langServiceBackup = $GLOBALS['LANG'];
            $GLOBALS['LANG']         = $this->Translation()->getTypoLanguageService();
            
            return;
        }
        
        // Create backup
        $this->requestLangBackup = $this->TypoContext()->Config()->getRequestAttribute('language');
        $this->langServiceBackup = $GLOBALS['LANG'];
        $this->aspectBackup      = $this->TypoContext()->getRootContext()->getAspect('language');
        
        // Update the language
        $languageObject = $this->resolveLanguageObject($options['language'], $options['fallbackLanguage']);
        $this->TypoContext()->Config()->setRequestAttribute('language', $languageObject);
        $languageAspect = LanguageAspectFactory::createFromSiteLanguage($languageObject);
        $this->TypoContext()->getRootContext()->setAspect('language', $languageAspect);
        unset($GLOBALS['LANG']);
        $GLOBALS['LANG'] = $this->Translation()->getTypoLanguageService();
    }
    
    /**
     * @inheritDoc
     */
    public function rollBack(): void
    {
        $GLOBALS['LANG'] = $this->langServiceBackup;
        if ($this->provideLangFallback) {
            return;
        }
        
        $this->TypoContext()->getRootContext()->setAspect('language', $this->aspectBackup);
        $this->TypoContext()->Config()->setRequestAttribute('language', $this->requestLangBackup);
    }
    
    /**
     * Internal helper to resolve the language by a multitude of different formats
     *
     * @param   int|string|SiteLanguage       $language          The language to set the frontend to.
     *                                                           Either as sys_language_uid value or as language object
     *
     * @param   int|string|SiteLanguage|true  $fallbackLanguage  The language which should be used when the $language
     *                                                           was not found for this site. If true is given, the
     *                                                           default language will be used
     *
     * @return mixed|\TYPO3\CMS\Core\Site\Entity\SiteLanguage
     */
    protected function resolveLanguageObject($language, $fallbackLanguage = null)
    {
        if (! is_object($language)) {
            $languages = $this->TypoContext()->Site()->getCurrent()->getLanguages();
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
                    $fallbackLanguage = $this->TypoContext()->Site()->getCurrent()->getDefaultLanguage();
                }
                
                return $this->resolveLanguageObject($fallbackLanguage);
            }
            throw new InvalidArgumentException('Could not determine the site language for the given language value!');
        }
        
        return $language;
    }
    
}
