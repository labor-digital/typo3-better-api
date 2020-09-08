<?php
declare(strict_types=1);
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

namespace LaborDigital\T3BA\Tool\TypoContext\Aspect;

use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class BetterLanguageAspect extends LanguageAspect implements PublicServiceInterface
{
    use AutomaticAspectGetTrait;

    /**
     * @var TypoContext
     */
    protected $context;

    /**
     * Inject the typo context instance
     *
     * @param   \LaborDigital\T3BA\Tool\TypoContext\TypoContext  $context
     */
    public function injectContext(TypoContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name)
    {
        return $this->handleGet($name);
    }

    /**
     * Returns the instance of the current frontend object
     *
     * @return \TYPO3\CMS\Core\Site\Entity\SiteLanguage
     */
    public function getCurrentFrontendLanguage(): SiteLanguage
    {
        return $this->context->Site()->getCurrent()->getLanguageById($this->getRootLanguageAspect()->getId());
    }

    /**
     * Returns the list of all languages the frontend may display
     *
     * @return SiteLanguage[]
     */
    public function getAllFrontendLanguages(): array
    {
        return $this->context->Site()->getCurrent()->getLanguages();
    }

    /**
     * Returns the two char ISO code that defines the backend language
     *
     * @return string
     */
    public function getCurrentBackendLanguage(): string
    {
        if ($this->context->BeUser()->hasUser()
            && isset($this->context->BeUser()->getUser()->uc['lang'])) {
            // Read language (empty means "en")
            $isoCode = $this->context->BeUser()->getUser()->uc['lang'];
            $isoCode = empty($isoCode) ? 'en' : $isoCode;
        } else {
            $isoCode = 'en';
        }

        return $isoCode;
    }

    /**
     * @inheritDoc
     */
    public function getOverlayType(): string
    {
        return $this->getRootLanguageAspect()->getOverlayType();
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->getRootLanguageAspect()->getId();
    }

    /**
     * @inheritDoc
     */
    public function getContentId(): int
    {
        return $this->getRootLanguageAspect()->getContentId();
    }


    public function getFallbackChain(): array
    {
        return $this->getRootLanguageAspect()->getFallbackChain();
    }

    /**
     * @inheritDoc
     */
    public function doOverlays(): bool
    {
        return $this->getRootLanguageAspect()->doOverlays();
    }

    /**
     * @inheritDoc
     */
    public function getLegacyLanguageMode(): string
    {
        return $this->getRootLanguageAspect()->getLegacyLanguageMode();
    }

    /**
     * @inheritDoc
     */
    public function getLegacyOverlayType(): string
    {
        return $this->getRootLanguageAspect()->getLegacyOverlayType();
    }


    /**
     * Returns the root context's language aspect
     *
     * @return \TYPO3\CMS\Core\Context\LanguageAspect|mixed
     */
    public function getRootLanguageAspect(): LanguageAspect
    {
        return $this->context->getRootContext()->getAspect('language');
    }
}