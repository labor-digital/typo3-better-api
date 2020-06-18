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
 * Last modified: 2020.05.12 at 11:41
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Facet;

use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\PathUtil\Path;
use Throwable;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\PseudoSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Class SiteFacet
 *
 * @package LaborDigital\Typo3BetterApi\TypoContext\Facet
 */
class SiteFacet implements FacetInterface
{
    
    /**
     * @var \TYPO3\CMS\Core\Site\SiteFinder
     */
    protected $siteFinder;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $context;
    
    /**
     * @var \TYPO3\CMS\Core\Routing\SiteMatcher
     */
    protected $siteMatcher;
    
    /**
     * True while the site is being found to avoid infinite loops
     *
     * @var bool
     */
    protected $simulateNoSite = false;
    
    
    /**
     * SiteAspect constructor.
     *
     * @param   \TYPO3\CMS\Core\Site\SiteFinder                       $siteFinder
     * @param   \TYPO3\CMS\Core\Routing\SiteMatcher                   $siteMatcher
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function __construct(SiteFinder $siteFinder, SiteMatcher $siteMatcher, TypoContext $context)
    {
        $this->siteFinder  = $siteFinder;
        $this->context     = $context;
        $this->siteMatcher = $siteMatcher;
    }
    
    /**
     * Returns the instance of the current site
     *
     * @return \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     */
    public function get()
    {
        // Check if we can fetch a better site
        $site = $this->context->Config()->getRequestAttribute('site');
        if (! empty($site)) {
            return $site;
        }
        
        // Try to find the site via pid
        $this->simulateNoSite = true;
        $pid                  = $this->context->Pid()->getCurrent();
        $this->simulateNoSite = false;
        if (! empty($pid)) {
            $site = $this->siteFinder->getSiteByPageId($pid);
            if (! empty($site)) {
                $this->set($site);
                
                return $site;
            }
        }
        
        // Use the single site we have
        $sites = $this->siteFinder->getAllSites();
        if (count($sites) === 1) {
            $this->set(reset($sites));
            
            return reset($sites);
        }
        
        // Try to match the site with the current host
        $request = $this->context->Request()->getRootRequest();
        if (! is_null($request)) {
            try {
                $result = $this->siteMatcher->matchRequest($request->withUri(Path::makeUri(true)));
                $site   = $result->getSite();
                $this->set($site);
                
                return $site;
            } catch (Throwable $exception) {
            }
        }
        
        // Nothing found...
        throw new SiteNotFoundException('There is currently no site defined! To use the SiteAspect set a site first!');
    }
    
    /**
     * Returns true if the site has been set
     *
     * @return bool
     */
    public function exists(): bool
    {
        if ($this->simulateNoSite) {
            return false;
        }
        try {
            $this->get();
            
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
    
    /**
     * Sets the instance of a site to the given object
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite  $site
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    public function set($site): SiteFacet
    {
        if ($site === null) {
            $site = new NullSite();
        }
        if (! $site instanceof Site && ! $site instanceof NullSite && ! $site instanceof PseudoSite) {
            throw new BetterApiException('The given site object is not a site, a null site or a pseudo site object!');
        }
        $this->context->Config()->setRequestAttribute('site', $site);
        
        return $this;
    }
    
    /**
     * Sets the site by it's identifier.
     *
     * @param   string  $identifier
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet
     */
    public function setTo(string $identifier): SiteFacet
    {
        $this->set($this->siteFinder->getSiteByIdentifier($identifier));
        
        return $this;
    }
    
    /**
     * Sets the site by a pid.
     *
     * @param   string|int  $pid       Either the numeric PID or a PID selector
     * @param   array|null  $rootLine  An optional rootLine to traverse
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet
     */
    public function setToPid($pid, ?array $rootLine = null): SiteFacet
    {
        if (! is_numeric($pid)) {
            $pid = $this->context->Pid()->get($pid, 0);
        }
        $this->set($this->siteFinder->getSiteByPageId($pid, $rootLine));
        
        return $this;
    }
}
