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
 * Last modified: 2021.06.17 at 17:43
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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\T3ba\Tool\TypoContext;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Core\Util\SingletonInstanceTrait;
use LaborDigital\T3ba\Tool\TypoContext\Aspect\BetterLanguageAspect;
use LaborDigital\T3ba\Tool\TypoContext\Aspect\BetterVisibilityAspect;
use LaborDigital\T3ba\Tool\TypoContext\Aspect\BeUserAspect;
use LaborDigital\T3ba\Tool\TypoContext\Aspect\FacetAspect;
use LaborDigital\T3ba\Tool\TypoContext\Aspect\FeUserAspect;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Aspect\PreviewAspect;

class TypoContext implements SingletonInterface, PublicServiceInterface
{
    use ContainerAwareTrait;
    use SingletonInstanceTrait;
    
    /**
     * @var \TYPO3\CMS\Core\Context\Context
     */
    protected $rootContext;
    
    /**
     * A list of available facet classes by their shortname
     *
     * @var array
     */
    protected $facetClasses;
    
    public function __construct(array $facetClasses)
    {
        $this->facetClasses = $facetClasses;
    }
    
    /**
     * Returns the TYPO3 root context
     *
     * @return \TYPO3\CMS\Core\Context\Context
     */
    public function getRootContext(): Context
    {
        return $this->rootContext ?? ($this->rootContext = $this->getContainer()->get(Context::class));
    }
    
    /** ====================================================
     *
     * DEFAULT ASPECTS AND EXTENSIONS
     *
     * ==================================================== */
    
    /**
     * Returns the date aspect which holds time, date and timezone information for the current request
     *
     * @return \TYPO3\CMS\Core\Context\DateTimeAspect|mixed
     */
    public function date(): DateTimeAspect
    {
        return $this->rootContext->getAspect('date');
    }
    
    /**
     * Returns the workspace aspect which holds information about the currently accessed workspace.
     *
     * @return WorkspaceAspect|mixed
     */
    public function workspace(): WorkspaceAspect
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
    public function language(): BetterLanguageAspect
    {
        return $this->getOrMakeAspect('betterLanguage', BetterLanguageAspect::class);
    }
    
    /**
     * Returns information about the visibility of records
     *
     * @return BetterVisibilityAspect
     */
    public function visibility(): BetterVisibilityAspect
    {
        return $this->getOrMakeAspect('betterVisibility', BetterVisibilityAspect::class);
    }
    
    /**
     * Returns the frontend preview aspect
     *
     * @return \TYPO3\CMS\Frontend\Aspect\PreviewAspect|mixed
     */
    public function preview(): PreviewAspect
    {
        return $this->rootContext->getAspect('frontend.preview');
    }
    
    /**
     * Returns the frontend user context aspect
     *
     * @return FeUserAspect
     */
    public function feUser(): FeUserAspect
    {
        return $this->getOrMakeAspect('frontend.betterUser', FeUserAspect::class);
    }
    
    /**
     * Returns the backend user context aspect
     *
     * @return BeUserAspect
     */
    public function beUser(): BeUserAspect
    {
        return $this->getOrMakeAspect('backend.betterUser', BeUserAspect::class);
    }
    
    /**
     * Handles the lookup of dynamic facets based on the given facet classes
     */
    public function __call($name, $arguments)
    {
        if (isset($this->facetClasses[$name])) {
            $context = $this->getRootContext();
            $aspectKey = 'facet.' . $name;
            
            if ($context->hasAspect($aspectKey)) {
                return $context->getAspect($aspectKey)->get('');
            }
            
            $facet = $this->getService($this->facetClasses[$name]);
            $context->setAspect($aspectKey, $this->makeInstance(FacetAspect::class, [$facet]));
            
            return $facet;
        }
        
        return null;
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
        $aspect = $this->getService($aspectClass);
        $context->setAspect($aspectKey, $aspect);
        
        return $aspect;
    }
}
