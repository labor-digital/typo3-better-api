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
 * Last modified: 2020.03.19 at 01:20
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;


use LaborDigital\Typo3BetterApi\BetterApiException;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\PathUtil\Path;
use Throwable;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\SiteMatcher;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\PseudoSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Class SiteAspect
 * @package LaborDigital\Typo3BetterApi\TypoContext\Aspect*
 *
 * @property SiteFinder $SiteFinder
 */
class SiteAspect implements AspectInterface {
	use AutomaticAspectGetTrait;
	
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
	 * Holds the site information if we don't have a request to store it on
	 * @var Site|NullSite|PseudoSite
	 */
	protected $fallbackSiteStorage;
	
	/**
	 * SiteAspect constructor.
	 *
	 * @param \TYPO3\CMS\Core\Site\SiteFinder                      $siteFinder
	 * @param \TYPO3\CMS\Core\Routing\SiteMatcher                  $siteMatcher
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext $context
	 */
	public function __construct(SiteFinder $siteFinder, SiteMatcher $siteMatcher, TypoContext $context) {
		$this->siteFinder = $siteFinder;
		$this->context = $context;
		$this->siteMatcher = $siteMatcher;
	}
	
	/**
	 * @inheritDoc
	 */
	public function get(string $name) {
		return $this->handleGet($name);
	}
	
	/**
	 * Returns the instance of the current site
	 *
	 * @return \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite
	 * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
	 */
	public function getSite() {
		// Check if we can fetch a better site
		$request = $this->context->getRequestAspect()->getRootRequest();
		if (!is_null($request))
			$site = $request->getAttribute("site", NULL);
		else $site = $this->fallbackSiteStorage;
		if (!empty($site)) return $site;
		
		// Try to find the site via pid
		$pid = $this->context->getPidAspect()->getCurrentPid();
		if (!empty($pid)) {
			$site = $this->siteFinder->getSiteByPageId($pid);
			if (!empty($site)) {
				$this->setSite($site);
				return $site;
			}
		}
		
		// Use the single site we have
		$sites = $this->siteFinder->getAllSites();
		if (count($sites) === 1) {
			$this->setSite(reset($sites));
			return reset($sites);
		}
		
		// Try to match the site with the current host
		if (!is_null($request)) {
			try {
				$result = $this->siteMatcher->matchRequest($request->withUri(Path::makeUri(TRUE)));
				$site = $result->getSite();
				$this->setSite($site);
				return $site;
			} catch (Throwable $exception) {
			}
		}
		
		// Check if we have a fallback site
		if (!is_null($this->fallbackSiteStorage))
			return $this->fallbackSiteStorage;
		
		// Nothing found...
		throw new SiteNotFoundException("There is currently no site defined! To use the SiteAspect set a site first!");
	}
	
	/**
	 * Returns true if the site has been set
	 * @return bool
	 */
	public function hasSite(): bool {
		try {
			$this->getSite();
			return TRUE;
		} catch (Throwable $e) {
			return FALSE;
		}
	}
	
	/**
	 * Sets the instance of a site to the given object
	 *
	 * @param \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite $site
	 *
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
	 * @throws \LaborDigital\Typo3BetterApi\BetterApiException
	 */
	public function setSite($site): SiteAspect {
		if ($site === NULL) $site = new NullSite();
		if (!$site instanceof Site && !$site instanceof NullSite && !$site instanceof PseudoSite)
			throw new BetterApiException("The given site object is not a site, a null site or a pseudo site object!");
		$this->fallbackSiteStorage = $site;
		$request = $this->context->getRequestAspect()->getRootRequest();
		if (!is_null($request)) {
			$request = $request->withAttribute("site", $site);
			$this->context->getRequestAspect()->setRootRequest($request);
		}
		return $this;
	}
	
	/**
	 * Sets the site by it's identifier.
	 *
	 * @param string $identifier
	 *
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
	 */
	public function setSiteTo(string $identifier): SiteAspect {
		$this->setSite($this->siteFinder->getSiteByIdentifier($identifier));
		return $this;
	}
	
	/**
	 * Sets the site by a pid.
	 *
	 * @param string|int $pid      Either the numeric PID or a PID selector
	 * @param array|null $rootLine An optional rootLine to traverse
	 *
	 * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
	 */
	public function setSiteToPid($pid, ?array $rootLine = NULL): SiteAspect {
		if (!is_numeric($pid)) $pid = $this->context->getPidAspect()->getPid($pid, 0);
		$this->setSite($this->siteFinder->getSiteByPageId($pid, $rootLine));
		return $this;
	}
	
}