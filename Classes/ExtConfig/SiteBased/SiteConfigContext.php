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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\ExtConfig\SiteBased;


use LaborDigital\T3BA\ExtConfig\ExtConfigContext;
use LaborDigital\T3BA\ExtConfig\ExtConfigService;
use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Site\Entity\Site;

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
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }
    
    /**
     * Returns the site that gets currently configured
     *
     * @return \TYPO3\CMS\Core\Site\Entity\Site
     */
    public function getSite(): Site
    {
        return $this->site;
    }
    
    /**
     * Internal helper to inject the configured site into the context
     *
     * @param   string                            $siteKey
     * @param   \TYPO3\CMS\Core\Site\Entity\Site  $site
     *
     * @internal
     */
    public function initializeSite(string $siteKey, Site $site): void
    {
        $this->siteKey = $siteKey;
        $this->site = $site;
    }
}
