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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Context\VisibilityAspect;

class BetterVisibilityAspect extends VisibilityAspect implements AspectInterface
{
    use AutomaticAspectGetTrait;
    
    /**
     * @var TypoContext
     */
    protected $context;
    
    /**
     * Inject the typo context instance
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\TypoContext  $context
     */
    public function injectContext(TypoContext $context)
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
     * @param   bool  $includeHiddenPages
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeHiddenPages(bool $includeHiddenPages): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeHiddenPages = $includeHiddenPages;
        
        return $this;
    }
    
    /**
     * @param   bool  $includeHiddenContent
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeHiddenContent(bool $includeHiddenContent): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeHiddenContent = $includeHiddenContent;
        
        return $this;
    }
    
    /**
     * @param   bool  $includeDeletedRecords
     *
     * @return BetterVisibilityAspect
     */
    public function setIncludeDeletedRecords(bool $includeDeletedRecords): BetterVisibilityAspect
    {
        $this->getRootVisibilityAspect()->includeDeletedRecords = $includeDeletedRecords;
        
        return $this;
    }
    
    /**
     * @inheritDoc
     */
    public function includeHiddenPages(): bool
    {
        return $this->getRootVisibilityAspect()->includeHiddenPages();
    }
    
    /**
     * @inheritDoc
     */
    public function includeHiddenContent(): bool
    {
        return $this->getRootVisibilityAspect()->includeHiddenContent();
    }
    
    /**
     * @inheritDoc
     */
    public function includeDeletedRecords(): bool
    {
        return $this->getRootVisibilityAspect()->includeDeletedRecords();
    }
    
    /**
     * Returns the root context's visibility aspect
     *
     * @return \TYPO3\CMS\Core\Context\VisibilityAspect|mixed
     */
    public function getRootVisibilityAspect(): VisibilityAspect
    {
        return $this->context->getRootContext()->getAspect('visibility');
    }
}