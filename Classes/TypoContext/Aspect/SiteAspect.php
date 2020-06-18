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

use LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\PseudoSite;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * Class SiteAspect
 *
 * @package    LaborDigital\Typo3BetterApi\TypoContext\Aspect*
 *
 * @property SiteFinder $SiteFinder
 * @deprecated will be removed in v10 -> Use SiteFacet instead
 */
class SiteAspect implements AspectInterface
{
    use AutomaticAspectGetTrait;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet
     */
    protected $facet;
    
    /**
     * SiteAspect constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\Facet\SiteFacet  $facet
     */
    public function __construct(SiteFacet $facet)
    {
        $this->facet = $facet;
    }
    
    /**
     * @inheritDoc
     */
    public function get(string $name)
    {
        if ($name === 'FACET') {
            return $this->facet;
        }
        
        return $this->handleGet($name);
    }
    
    /**
     * Returns the instance of the current site
     *
     * @return \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite
     * @throws \TYPO3\CMS\Core\Exception\SiteNotFoundException
     * @deprecated will be removed in v10 -> Use SiteFacet instead
     */
    public function getSite()
    {
        return $this->facet->get();
    }
    
    /**
     * Returns true if the site has been set
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use SiteFacet instead
     */
    public function hasSite(): bool
    {
        return $this->facet->exists();
    }
    
    /**
     * Sets the instance of a site to the given object
     *
     * @param   \TYPO3\CMS\Core\Site\Entity\Site|NullSite|PseudoSite  $site
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     * @deprecated will be removed in v10 -> Use SiteFacet instead
     */
    public function setSite($site): SiteAspect
    {
        $this->facet->set($site);
        
        return $this;
    }
    
    /**
     * Sets the site by it's identifier.
     *
     * @param   string  $identifier
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
     * @deprecated will be removed in v10 -> Use SiteFacet instead
     */
    public function setSiteTo(string $identifier): SiteAspect
    {
        $this->facet->setTo($identifier);
        
        return $this;
    }
    
    /**
     * Sets the site by a pid.
     *
     * @param   string|int  $pid       Either the numeric PID or a PID selector
     * @param   array|null  $rootLine  An optional rootLine to traverse
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\SiteAspect
     * @deprecated will be removed in v10 -> Use SiteFacet instead
     */
    public function setSiteToPid($pid, ?array $rootLine = null): SiteAspect
    {
        $this->facet->setToPid($pid, $rootLine);
        
        return $this;
    }
}
