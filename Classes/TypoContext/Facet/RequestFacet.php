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

namespace LaborDigital\Typo3BetterApi\TypoContext\Facet;

use Neunerlei\Arrays\Arrays;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequestFacet
 *
 * @package LaborDigital\Typo3BetterApi\TypoContext\Facet
 */
class RequestFacet implements FacetInterface
{
    
    /**
     * Returns the http request object that was passed through the middleware stack.
     * Note that this method returns null if there was no request object found, like in CLI context.
     *
     * @return \Psr\Http\Message\ServerRequestInterface|null
     */
    public function getRootRequest(): ?ServerRequestInterface
    {
        // Try to fetch the request from the globals
        if (! empty($GLOBALS['TYPO3_REQUEST']) && $GLOBALS['TYPO3_REQUEST'] instanceof ServerRequestInterface) {
            return $GLOBALS['TYPO3_REQUEST'];
        }
        if (! empty($GLOBALS['TYPO3_REQUEST_FALLBACK'])
            && $GLOBALS['TYPO3_REQUEST_FALLBACK'] instanceof ServerRequestInterface) {
            return $GLOBALS['TYPO3_REQUEST_FALLBACK'];
        }
        
        return null;
    }
    
    /**
     * Allows you to update the root typo3 server request for the current execution context
     *
     * @param   \Psr\Http\Message\ServerRequestInterface  $request
     *
     * @return \LaborDigital\Typo3BetterApi\TypoContext\Facet\RequestFacet
     */
    public function setRootRequest(ServerRequestInterface $request): RequestFacet
    {
        $GLOBALS['TYPO3_REQUEST']          = $request;
        $GLOBALS['TYPO3_REQUEST_FALLBACK'] = $request;
        
        return $this;
    }
    
    /**
     * Returns the get value based on the given path of typo's "GeneralUtility::_GET()" method
     *
     * @param   string|array|null  $path     The path to the value to retrieve
     * @param   mixed              $default  The value to be returned if the searched value was not found.
     *
     * @return mixed|null The requested value or null
     */
    public function getGet($path = null, $default = null)
    {
        $request = $this->getRootRequest();
        $params  = $_GET;
        if (! empty($request)) {
            $params = $request->getQueryParams();
        }
        
        return is_null($path) ? $params : Arrays::getPath($params, $path, $default);
    }
    
    /**
     * Returns the post value based on the given path of typo's "GeneralUtility::_POST()" method
     *
     * @param   string|array|null  $path     The path to the value to retrieve
     * @param   mixed              $default  The value to be returned if the searched value was not found.
     *
     * @return mixed|null The requested value or null
     */
    public function getPost($path = null, $default = null)
    {
        $request = $this->getRootRequest();
        $params  = $_POST;
        if (! empty($request)) {
            $params = $request->getParsedBody();
        }
        if (! is_array($params)) {
            $params = [];
        }
        
        return is_null($path) ? $params : Arrays::getPath($params, $path, $default);
    }
    
    /**
     * Returns true if typo's "GeneralUtility::_POST()" method returns a value for $path
     *
     * @param   string|array  $path  The array path to check for
     *
     * @return bool
     */
    public function hasPost($path): bool
    {
        $request = $this->getRootRequest();
        $params  = $_POST;
        if (! is_array($params)) {
            $params = [];
        }
        if (! empty($request)) {
            $params = $request->getParsedBody();
        }
        
        return Arrays::hasPath($params, $path);
    }
    
    /**
     * Returns true if typo's "GeneralUtility::_GET()" method returns a value for $path
     *
     * @param   string|array  $path  The array path to check for
     *
     * @return bool
     */
    public function hasGet($path): bool
    {
        $request = $this->getRootRequest();
        $params  = $_GET;
        if (! empty($request)) {
            $params = $request->getQueryParams();
        }
        
        return Arrays::hasPath($params, $path);
    }
    
    /**
     * Returns the currently defined hostname
     *
     * @param   bool  $withProtocol  If set to true the protocol (http(s)://) will be added to the host
     *
     * @return string
     */
    public function getHost(bool $withProtocol = true): string
    {
        if ($withProtocol) {
            return GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');
        }
        
        return GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY');
    }
    
    /**
     * Returns the given referrer/origin of the executed request
     *
     * @return string
     */
    public function getReferrer(): string
    {
        $referrer = $this->getRootRequest()->getHeaderLine('referrer');
        if (empty($referrer)) {
            $referrer = $this->getRootRequest()->getHeaderLine('origin');
        }
        
        return $referrer;
    }
}
