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

namespace LaborDigital\T3BA\Tool\TypoContext;

use LaborDigital\T3BA\Core\DependencyInjection\ContainerAwareTrait;
use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;
use LaborDigital\T3BA\Core\Util\SingletonInstanceTrait;
use LaborDigital\T3BA\Tool\TypoContext\Aspect\BetterLanguageAspect;
use LaborDigital\T3BA\Tool\TypoContext\Aspect\BetterVisibilityAspect;
use LaborDigital\T3BA\Tool\TypoContext\Aspect\BeUserAspect;
use LaborDigital\T3BA\Tool\TypoContext\Aspect\FacetAspect;
use LaborDigital\T3BA\Tool\TypoContext\Aspect\FeUserAspect;
use LaborDigital\T3BA\Tool\TypoContext\Facet\ConfigFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\DependencyInjectionFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\EnvFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\FacetInterface;
use LaborDigital\T3BA\Tool\TypoContext\Facet\PathFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\PidFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\RequestFacet;
use LaborDigital\T3BA\Tool\TypoContext\Facet\SiteFacet;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\SingletonInterface;

class TypoContext implements SingletonInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use SingletonInstanceTrait;

    /**
     * @var \TYPO3\CMS\Core\Context\Context
     */
    protected $rootContext;

    /**
     * Returns the TYPO3 root context
     *
     * @return \TYPO3\CMS\Core\Context\Context
     */
    public function getRootContext(): Context
    {
        return $this->rootContext ?? ($this->rootContext = $this->Container()->get(Context::class));
    }

    /** ====================================================
     *
     * DEFAULT ASPECTS AND EXTENSIONS
     *
     * ==================================================== */

    /**
     * Returns the workspace aspect which holds information about the currently accessed workspace.
     *
     * @return WorkspaceAspect|mixed
     */
    public function Workspace(): WorkspaceAspect
    {
        return $this->rootContext->getAspect('workspace');
    }

    /**
     * Returns the language aspect of this request.
     * Note: This is not the core language aspect, but a better language aspect,
     * which holds additional methods to retrieve language information.
     *
     * @return BetterLanguageAspect
     */
    public function Language(): BetterLanguageAspect
    {
        return $this->getOrMakeAspect('betterLanguage', BetterLanguageAspect::class);
    }

    /**
     * Returns information about the visibility of records
     *
     * @return BetterVisibilityAspect
     */
    public function Visibility(): BetterVisibilityAspect
    {
        return $this->getOrMakeAspect('betterVisibility', BetterVisibilityAspect::class);
    }

    /**
     * Returns the frontend user context aspect
     *
     * @return FeUserAspect
     */
    public function FeUser(): FeUserAspect
    {
        return $this->getOrMakeAspect('frontend.betterUser', FeUserAspect::class);
    }

    /**
     * Returns the backend user context aspect
     *
     * @return BeUserAspect
     */
    public function BeUser(): BeUserAspect
    {
        return $this->getOrMakeAspect('backend.betterUser', BeUserAspect::class);
    }

    /** ====================================================
     *
     * FACETS
     *
     * ==================================================== */

    /**
     * Repository of path information and path resolving functions
     *
     * @return PathFacet
     */
    public function Path(): PathFacet
    {
        return $this->getOrMakeFacet('path', PathFacet::class);
    }

    /**
     * Repository of information about the environment
     *
     * @return EnvFacet
     */
    public function Env(): EnvFacet
    {
        return $this->getOrMakeFacet('env', EnvFacet::class);
    }

    /**
     * Repository of information about the current typo3 site
     *
     * @return SiteFacet
     */
    public function Site(): SiteFacet
    {
        return $this->getOrMakeFacet('site', SiteFacet::class);
    }

    /**
     * Repository of information about the current HTTP request
     *
     * @return RequestFacet
     */
    public function Request(): RequestFacet
    {
        return $this->getOrMakeFacet('request', RequestFacet::class);
    }

    /**
     * Repository of information about registered PIDs and the local page id
     *
     * @return PidFacet
     */
    public function Pid(): PidFacet
    {
        return $this->getOrMakeFacet('pid', PidFacet::class);
    }

    /**
     * Repository for the different, global configuration options in TYPO3
     *
     * @return ConfigFacet
     */
    public function Config(): ConfigFacet
    {
        return $this->getOrMakeFacet('globalConfig', ConfigFacet::class);
    }

    /**
     * Repository to all dependency injection capabilities of typo3
     *
     * @return \LaborDigital\T3BA\Tool\TypoContext\Facet\DependencyInjectionFacet
     */
    public function Di(): DependencyInjectionFacet
    {
        return $this->getOrMakeFacet('di', DependencyInjectionFacet::class);
    }

    /**
     * Internal helper to request an aspect from the context, or if it is a custom aspect
     * create a new instance which is then provided to the context's storage.
     *
     * @param   string       $aspectKey    The key of the aspect to store / load from the context
     * @param   string|null  $aspectClass  An optional class to be instantiated.
     *
     * @return \TYPO3\CMS\Core\Context\AspectInterface|mixed
     */
    protected function getOrMakeAspect(string $aspectKey, ?string $aspectClass = null): AspectInterface
    {
        $context = $this->getRootContext();
        if ($context->hasAspect($aspectKey)) {
            return $context->getAspect($aspectKey);
        }
        if (empty($aspectClass)) {
            return $context->getAspect($aspectKey);
        }
        $aspect = $this->getInstanceOf($aspectClass);
        $context->setAspect($aspectKey, $aspect);

        return $aspect;
    }

    /**
     * Facets are basically the same as an aspect but without the stupid get() method.
     * To store a facet on our root context we have to warp them in a pseudo-aspect called a FacetAspect
     *
     * @param   string  $facetKey    A unique name for this facet
     * @param   string  $facetClass  The name of the facet class to instantiate if it does not exist yet
     *
     * @return FacetInterface|mixed
     * @see getOrMakeAspect()
     * @see FacetInterface
     */
    protected function getOrMakeFacet(string $facetKey, string $facetClass): FacetInterface
    {
        $context   = $this->getRootContext();
        $aspectKey = "facet.$facetKey";
        if ($context->hasAspect($aspectKey)) {
            return $context->getAspect($aspectKey)->get('');
        }
        $facet  = $this->getInstanceOf($facetClass);
        $aspect = $this->getWithoutDi(FacetAspect::class, [$facet]);
        $context->setAspect($aspectKey, $aspect);

        return $facet;
    }
}
