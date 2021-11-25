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
 * Last modified: 2020.05.12 at 11:41
 */

namespace LaborDigital\T3ba\TypoContext;

use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\TypoContext\Util\CacheLessSiteConfigurationAdapter;
use Neunerlei\PathUtil\Path;
use Throwable;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Repository of information about the current TYPO3 site
 */
class SiteFacet implements FacetInterface
{
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * @var \TYPO3\CMS\Core\Site\SiteFinder
     */
    protected $siteFinder;
    
    /**
     * @var \TYPO3\CMS\Core\Routing\SiteMatcher
     */
    protected $siteMatcher;
    
    /**
     * Locally resolved current site first level cache, to avoid a lot of overhead
     *
     * @var \TYPO3\CMS\Core\Site\Entity\SiteInterface
     */
    protected $currentSite;
    
    /**
     * If the locally resolved $currentSite was resolved through a pid,
     * this property contains the pid in order to automatically reload the site if the current pid changed.
     *
     * @var int|null
     */
    protected $currentPid;
    
    /**
     * True while the site is being found to avoid infinite loops
     *
     * @var bool
     */
    protected $simulateNoSite = false;
    
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'site';
    }
    
    /**
     * Returns the instance of the current site
     *
     * @return \TYPO3\CMS\Core\Site\Entity\SiteInterface
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function getCurrent(): SiteInterface
    {
        // Check if we can fetch a site
        $site = $this->context->config()->getRequestAttribute('site');
        if (! empty($site) && ! $site instanceof NullSite) {
            // Make sure to reset the current site if we suddenly get a site
            $this->currentSite = null;
            $this->currentPid = null;
            
            return $site;
        }
        
        // Resolve the current pid
        $this->simulateNoSite = true;
        $pid = $this->context->pid()->getCurrent();
        $this->simulateNoSite = false;
        
        /**
         * Check if we have a current site cached
         */
        if (! empty($this->currentSite) &&
            (empty($pid) || empty($this->currentPid) || $pid === $this->currentPid)) {
            return $this->currentSite;
        }
        
        if (! empty($pid)) {
            $site = $this->getSiteFinder()->getSiteByPageId($pid);
            if ($site !== null) {
                $this->currentPid = $pid;
                
                return $this->currentSite = $site;
            }
        }
        
        // Use the single site we have
        $sites = $this->getSiteFinder()->getAllSites();
        if (count($sites) === 1) {
            return $this->currentSite = reset($sites);
        }
        
        // Try to match the site with the current host
        $request = $this->context->request()->getRootRequest();
        if (! is_null($request)) {
            try {
                $result = $this->getSiteMatcher()->matchRequest($request->withUri(Path::makeUri(true)));
                
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                return $this->currentSite = $result->getSite();
            } catch (Throwable $exception) {
            }
        }
        
        // Nothing found...
        throw new SiteNotFoundException('There is currently no site defined!');
    }
    
    /**
     * Returns true if we currently have a site set in the context, false if not
     *
     * @return bool
     */
    public function hasCurrent(): bool
    {
        if ($this->simulateNoSite) {
            return false;
        }
        
        try {
            $this->getCurrent();
            
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
    
    /**
     * Returns all sites that are registered in the system
     *
     * @param   bool  $useCache  False to disable all caching of the sites
     *
     * @return Site[]
     */
    public function getAll(bool $useCache = true): array
    {
        return $this->getSiteFinder()->getAllSites($useCache);
    }
    
    /**
     * Returns the instance of a specific site based on the given identifier
     *
     * @param   string  $identifier  The identifier for the site to find
     *
     * @return SiteInterface
     */
    public function get(string $identifier): SiteInterface
    {
        return $this->getSiteFinder()->getSiteByIdentifier($identifier);
    }
    
    /**
     * Returns the instance of a specific site based on the given page id
     *
     * @param   string|int  $pid  The page id to find the site for
     *
     * @return \TYPO3\CMS\Core\Site\Entity\Site
     */
    public function getForPid($pid): Site
    {
        // If we have no site, don't try to resolve one based on the pid -> This might lead to endless loops
        if (! $this->hasCurrent()) {
            $this->simulateNoSite = true;
        }
        
        try {
            return $this->getSiteFinder()->getSiteByPageId($this->context->pid()->get($pid));
        } finally {
            $this->simulateNoSite = false;
        }
    }
    
    /**
     * Returns true if the site with the given identifier exists, false if not
     *
     * @param   string  $identifier  The identifier for the site to find
     *
     * @return bool
     */
    public function has(string $identifier): bool
    {
        try {
            $this->getSiteFinder()->getSiteByIdentifier($identifier);
            
            return true;
        } catch (SiteNotFoundException $exception) {
            return false;
        }
    }
    
    /**
     * Internal helper to lazily create the instance of the site finder if required
     *
     * @return \TYPO3\CMS\Core\Site\SiteFinder
     */
    protected function getSiteFinder(): SiteFinder
    {
        $canUseCaching = $this->context->di()->getContainer()->get('boot.state')->done;
        
        if (isset($this->siteFinder)) {
            // As soon as we can use the normal adapter, we will recreate it, otherwise we use our cacheLess adapter instead
            if (! $this->siteFinder instanceof CacheLessSiteConfigurationAdapter || ! $canUseCaching) {
                return $this->siteFinder;
            }
        }
        
        if ($canUseCaching) {
            return $this->siteFinder = $this->context->di()->makeInstance(SiteFinder::class);
        }
        
        unset($this->siteMatcher);
        
        return $this->siteFinder
            = $this->context->di()->makeInstance(SiteFinder::class, [
            CacheLessSiteConfigurationAdapter::makeInstance(),
        ]);
    }
    
    /**
     * Internal helper to lazily create the instance of the site matcher if required
     *
     * @return \TYPO3\CMS\Core\Routing\SiteMatcher
     */
    protected function getSiteMatcher(): SiteMatcher
    {
        if (isset($this->siteMatcher)) {
            return $this->siteMatcher;
        }
        
        return $this->siteMatcher = $this->context->di()->makeInstance(SiteMatcher::class, [$this->getSiteFinder()]);
    }
}
