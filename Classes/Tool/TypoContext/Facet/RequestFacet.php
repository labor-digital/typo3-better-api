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
 * Last modified: 2021.04.29 at 22:17
 */

/** @noinspection TypoSafeNamingInspection */
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
 * Last modified: 2020.03.19 at 01:19
 */

namespace LaborDigital\T3BA\Tool\TypoContext\Facet;

use LaborDigital\T3BA\Tool\TypoContext\TypoContextAwareTrait;
use Neunerlei\Arrays\Arrays;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RequestFacet
 *
 * @package LaborDigital\T3BA\Tool\TypoContext\Facet
 */
class RequestFacet implements FacetInterface
{
    use TypoContextAwareTrait;
    
    /**
     * A cache to store resolved hosts on
     *
     * @var array
     */
    protected $hostCache = [];
    
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
     * @return RequestFacet
     */
    public function setRootRequest(ServerRequestInterface $request): RequestFacet
    {
        $GLOBALS['TYPO3_REQUEST'] = $request;
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
        $params = $_GET;
        $request = $this->getRootRequest();
        if ($request !== null) {
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
        $params = $_POST;
        $request = $this->getRootRequest();
        if ($request !== null) {
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
        $params = $_POST;
        $request = $this->getRootRequest();
        if (! is_array($params)) {
            $params = [];
        }
        if ($request !== null) {
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
        $params = $_GET;
        $request = $this->getRootRequest();
        if ($request !== null) {
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
        $typoContext = $this->getTypoContext();
        $pid = $typoContext->pid()->getCurrent();
        
        if (! isset($this->hostCache[$pid])) {
            try {
                $site = $typoContext->site()->getForPid($pid);
                $this->hostCache[$pid] = [
                    $site->getBase()->getHost(),
                    $site->getBase()->getScheme() . '://' . $site->getBase()->getHost(),
                ];
            } catch (SiteNotFoundException $exception) {
                $this->hostCache[$pid] = [
                    GeneralUtility::getIndpEnv('TYPO3_HOST_ONLY'),
                    GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'),
                ];
            }
        }
        
        return $this->hostCache[$pid][(int)$withProtocol];
    }
    
    /**
     * Returns the given referer/origin of the executed request
     *
     * @return string
     */
    public function getReferer(): string
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? null;
        
        $request = $this->getRootRequest();
        if ($request !== null) {
            $referer = $request->getHeaderLine('referer');
            if (empty($referer)) {
                $referer = $request->getHeaderLine('origin');
            }
        }
        
        return $referer;
    }
}
