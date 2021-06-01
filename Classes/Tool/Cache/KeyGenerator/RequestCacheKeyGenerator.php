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
 * Last modified: 2021.06.01 at 11:36
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Cache\KeyGenerator;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Psr\Http\Message\ServerRequestInterface;

class RequestCacheKeyGenerator implements CacheKeyGeneratorInterface, NoDiInterface
{
    /**
     * The list of request headers that should be taken into account when the cache key is generated
     *
     * @var array
     */
    public static $trackedHeaders = [];
    
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;
    
    /**
     * The list of request headers that should be taken into account (this combines the ones under static::$trackedHeaders, and the given $tracked headers in the constructor)
     *
     * @var array
     */
    protected $_trackedHeaders;
    
    /**
     * The list of query parameters that should be excluded when the cache key is generated
     *
     * @var array
     */
    protected $_excludedQueryParams;
    
    /**
     * RequestCacheKeyGenerator constructor.
     *
     * @param   \Psr\Http\Message\ServerRequestInterface  $request
     */
    public function __construct(ServerRequestInterface $request, ?array $trackedHeaders = null, ?array $excludedQueryParams = null)
    {
        $this->request = $request;
        $this->_trackedHeaders = array_merge(static::$trackedHeaders, $trackedHeaders ?? []);
        $this->_excludedQueryParams = $excludedQueryParams ?? [];
    }
    
    /**
     * @inheritDoc
     */
    public function makeCacheKey(): string
    {
        $typoContext = TypoContext::getInstance();
        $request = $this->request;
        
        $params = Arrays::flatten($request->getQueryParams());
        ksort($params);
        
        foreach ($this->_excludedQueryParams as $excludedQueryParam) {
            unset($params[$excludedQueryParam]);
        }
        
        $headers = [];
        foreach ($this->_trackedHeaders as $trackedHeader) {
            $headers[$trackedHeader] = $request->getHeaderLine($trackedHeader);
        }
        
        return md5(implode('-', [
            \GuzzleHttp\json_encode($params),
            \GuzzleHttp\json_encode($headers),
            $request->getMethod(),
            $request->getUri()->getPath(),
            $typoContext->language()->getCurrentFrontendLanguage()->getTwoLetterIsoCode(),
            $typoContext->pid()->getCurrent(),
            $typoContext->site()->getCurrent()->getIdentifier(),
        ]));
    }
    
}
