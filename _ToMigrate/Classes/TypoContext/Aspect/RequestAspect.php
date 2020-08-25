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
 * Last modified: 2020.03.19 at 01:19
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;

use LaborDigital\Typo3BetterApi\TypoContext\Facet\RequestFacet;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\AspectInterface;

/**
 * Class RequestAspect
 *
 * @package    LaborDigital\Typo3BetterApi\TypoContext\Aspect
 *
 * @deprecated will be removed in v10 -> Use RequestFacet instead
 */
class RequestAspect implements AspectInterface
{
    use AutomaticAspectGetTrait;
    
    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\Facet\RequestFacet
     */
    protected $facet;
    
    /**
     * RequestAspect constructor.
     *
     * @param   \LaborDigital\Typo3BetterApi\TypoContext\Facet\RequestFacet  $facet
     */
    public function __construct(RequestFacet $facet)
    {
        $this->facet = $facet;
    }
    
    /**
     * Returns the http request object that was passed through the middleware stack.
     * Note that this method returns null if there was no request object found, like in CLI context.
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function getRootRequest(): ?ServerRequestInterface
    {
        return $this->facet->getRootRequest();
    }
    
    /**
     * Allows you to update the root typo3 server request for the current execution context
     *
     * @param   \Psr\Http\Message\ServerRequestInterface  $request
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Aspect\RequestAspect
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function setRootRequest(ServerRequestInterface $request): RequestAspect
    {
        $this->facet->setRootRequest($request);
        
        return $this;
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
     * Returns the get value based on the given path of typo's "GeneralUtility::_GET()" method
     *
     * @param   string|array|null  $path     The path to the value to retrieve
     * @param   mixed              $default  The value to be returned if the searched value was not found.
     *
     * @return mixed|null The requested value or null
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function getGet($path = null, $default = null)
    {
        return $this->facet->getGet($path, $default);
    }
    
    /**
     * Returns the post value based on the given path of typo's "GeneralUtility::_POST()" method
     *
     * @param   string|array|null  $path     The path to the value to retrieve
     * @param   mixed              $default  The value to be returned if the searched value was not found.
     *
     * @return mixed|null The requested value or null
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function getPost($path = null, $default = null)
    {
        return $this->facet->getPost($path, $default);
    }
    
    /**
     * Returns true if typo's "GeneralUtility::_POST()" method returns a value for $path
     *
     * @param   string|array  $path  The array path to check for
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function hasPost($path): bool
    {
        return $this->facet->hasPost($path);
    }
    
    /**
     * Returns true if typo's "GeneralUtility::_GET()" method returns a value for $path
     *
     * @param   string|array  $path  The array path to check for
     *
     * @return bool
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function hasGet($path): bool
    {
        return $this->facet->hasGet($path);
    }
    
    /**
     * Returns the currently defined hostname
     *
     * @param   bool  $withProtocol  If set to true the protocol (http(s)://) will be added to the host
     *
     * @return string
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function getHost(bool $withProtocol = true): string
    {
        return $this->facet->getHost($withProtocol);
    }
    
    /**
     * Returns the given referrer/origin of the executed request
     *
     * @return string
     * @deprecated will be removed in v10 -> Use RequestFacet instead
     */
    public function getReferrer(): string
    {
        return $this->facet->getReferrer();
    }
}
