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


namespace LaborDigital\T3ba\ExtConfig\SiteBased;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfig\ExtConfigService;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;

class SiteConfigContext extends ExtConfigContext
{
    /**
     * The key of the site that currently gets configured
     *
     * @var string
     */
    protected $siteKey;
    
    /**
     * The site that gets currently configured
     *
     * @var \TYPO3\CMS\Core\Site\Entity\Site
     */
    protected $site;
    
    /**
     * @inheritDoc
     */
    public function __construct(ExtConfigService $extConfigService, TypoContext $typoContext)
    {
        parent::__construct($extConfigService);
        $this->typoContext = $typoContext;
    }
    
    /**
     * Returns the key of the site that currently gets configured
     *
     * @return string
     * @deprecated will be removed in v11 use getSiteIdentifier() instead
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }
    
    /**
     * Returns the identifier of the site that currently gets configured
     *
     * @return string
     */
    public function getSiteIdentifier(): string
    {
        return $this->site->getIdentifier();
    }
    
    /**
     * Returns the site that gets currently configured
     *
     * @return \TYPO3\CMS\Core\Site\Entity\SiteInterface
     */
    public function getSite(): SiteInterface
    {
        return $this->site;
    }
    
    /**
     * Internal helper to inject the configured site into the context
     *
     * @param   string                                     $siteKey
     * @param   \TYPO3\CMS\Core\Site\Entity\SiteInterface  $site
     *
     * @internal
     * @todo in v11 remove the $siteKey property, because we can inherit the identifier through the site
     */
    public function initializeSite(string $siteKey, SiteInterface $site): void
    {
        $this->siteKey = $siteKey;
        $this->site = $site;
    }
}
