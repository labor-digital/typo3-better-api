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
     * Locally resolved current site first level cache, to avoid a lot of overhead
     *
     * @var \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite
     */
    protected $currentSite;

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
    public function getCurrent()
    {
        // Check if we can fetch a better site
        $site = $this->context->Config()->getRequestAttribute('site');
        if (! empty($site) && ! $site instanceof NullSite) {
            // Make sure to reset the current site if we suddenly get a site
            $this->currentSite = null;

            return $site;
        }

        /**
         * Check if we have a current site cached
         */
        if (! empty($this->currentSite)) {
            return $this->currentSite;
        }

        // Try to find the site via pid
        $this->simulateNoSite = true;
        $pid                  = $this->context->Pid()->getCurrent();
        $this->simulateNoSite = false;
        if (! empty($pid)) {
            $site = $this->siteFinder->getSiteByPageId($pid);
            if ($site !== null) {
                return $this->currentSite = $site;
            }
        }

        // Use the single site we have
        $sites = $this->siteFinder->getAllSites();
        if (count($sites) === 1) {
            return $this->currentSite = reset($sites);
        }

        // Try to match the site with the current host
        $request = $this->context->Request()->getRootRequest();
        if ($request !== null) {
            try {
                $result = $this->siteMatcher->matchRequest($request->withUri(Path::makeUri(true)));

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
     * @return array
     */
    public function getAll(bool $useCache = true): array
    {
        return $this->siteFinder->getAllSites($useCache);
    }

    /**
     * Returns the instance of a specific site based on the given identifier
     *
     * ATTENTION in v10 the $identifier will no longer be an optional parameter!
     *
     * @param   string|null  $identifier  The identifier for the site to find
     *
     * @return \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite
     */
    public function get(?string $identifier = null)
    {
        // @todo remove this in v10
        if ($identifier === null) {
            return $this->getCurrent();
        }

        return $this->siteFinder->getSiteByIdentifier($identifier);
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
        return $this->siteFinder->getSiteByPageId($this->context->Pid()->get($pid));
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
            $this->siteFinder->getSiteByIdentifier($identifier);

            return true;
        } catch (SiteNotFoundException $exception) {
            return false;
        }
    }

    /**
     * Returns true if the site has been set
     *
     * @return bool
     * @deprecated will be removed in v10 use has() or hasCurrent() instead!
     */
    public function exists(): bool
    {
        return $this->hasCurrent();
    }

    /**
     * Sets the instance of a site to the given object
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite  $site
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     * @deprecated will be removed in v10 use the EnvironmentSimulator class instead!
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
     * @deprecated will be removed in v10 use the EnvironmentSimulator class instead!
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
     * @deprecated will be removed in v10 use the EnvironmentSimulator class instead!
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
