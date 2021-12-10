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
 * Last modified: 2021.12.10 at 11:56
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\TypoContext\Util;


use GuzzleHttp\Psr7\Query;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use LaborDigital\T3ba\TypoContext\RequestFacet;
use LaborDigital\T3ba\TypoContext\SiteFacet;
use Neunerlei\PathUtil\Path;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class CurrentPidFinder implements NoDiInterface
{
    /**
     * Tries to find the current page uid. The method will automatically try to fall back
     * to the site-root page if it could not find the current page. If neither a page nor a site root pid
     * could be found NULL will be returned.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $context
     *
     * @return int
     */
    public static function findPid(TypoContext $context): ?int
    {
        $requestFacet = $context->request();
        
        return static::findTsfePid($GLOBALS['TSFE'] ?? null)
               ?? static::findInRequest($requestFacet)
                  ?? static::findInBackend($context->env()->isBackend(), $requestFacet)
                     ?? static::findSiteRootPidFallback($context->site());
    }
    
    /**
     * Tries to find the PID based on the given TSFE instance.
     *
     * @param   TypoScriptFrontendController|mixed  $tsfe
     *
     * @return int|null
     */
    protected static function findTsfePid($tsfe): ?int
    {
        if (! $tsfe instanceof TypoScriptFrontendController) {
            return null;
        }
        
        if (is_array($tsfe->originalShortcutPage) && is_numeric($tsfe->originalShortcutPage['uid'])) {
            return (int)$tsfe->originalShortcutPage['uid'];
        }
        
        if (is_numeric($tsfe->id)) {
            return (int)$tsfe->id;
        }
        
        if (is_array($tsfe->page) && is_numeric($tsfe->page['id'])) {
            return (int)$tsfe->page['id'];
        }
        
        return null;
    }
    
    /**
     * Tries to find the PID in the "id" parameter of the current request;
     *
     * @param   \LaborDigital\T3ba\TypoContext\RequestFacet  $requestFacet
     *
     * @return int|null
     */
    protected static function findInRequest(RequestFacet $requestFacet): ?int
    {
        return $requestFacet->hasGet('id') ? (int)$requestFacet->getGet('id') : null;
    }
    
    /**
     * Tries to find the pid when the request is currently executed in a "backend" context
     *
     * @param   bool                                         $isBackend
     * @param   \LaborDigital\T3ba\TypoContext\RequestFacet  $requestFacet
     *
     * @return int|null
     */
    protected static function findInBackend(bool $isBackend, RequestFacet $requestFacet): ?int
    {
        if (! $isBackend) {
            return null;
        }
        
        return static::findInBePopups($requestFacet)
               ?? static::findInBeRequestSuperGlobal()
                  ?? static::findInBeReturnUrl($requestFacet)
                     ?? static::findInBeInlineAjaxContext($requestFacet);
    }
    
    /**
     * Tries to find the pid in the parameter array of popups "linkBrowser" and the like
     *
     * @param   \LaborDigital\T3ba\TypoContext\RequestFacet  $requestFacet
     *
     * @return int|null
     */
    protected static function findInBePopups(RequestFacet $requestFacet): ?int
    {
        return $requestFacet->hasGet('P.pid') ? (int)$requestFacet->getGet('P.pid') : null;
    }
    
    /**
     * Tries to find the "id" parameter on the $_REQUEST super-global (For CLI calls)
     *
     * @return int|null
     */
    protected static function findInBeRequestSuperGlobal(): ?int
    {
        return isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ? (int)$_REQUEST['id'] : null;
    }
    
    /**
     * Tries to find the "id" parameter in the "returnUrl" parameter of the current request.
     * This is used when editing a record in the backend
     *
     * @param   \LaborDigital\T3ba\TypoContext\RequestFacet  $requestFacet
     *
     * @return int|null
     */
    protected static function findInBeReturnUrl(RequestFacet $requestFacet): ?int
    {
        return $requestFacet->hasGet('returnUrl')
            ? static::extractIdFromReturnUrl($requestFacet->getGet('returnUrl'))
            : null;
    }
    
    /**
     * Tries to resolve the pid in the ajax post data on FormEngine inline ajax requests
     *
     * @param   \LaborDigital\T3ba\TypoContext\RequestFacet  $requestFacet
     *
     * @return int|null
     */
    protected static function findInBeInlineAjaxContext(RequestFacet $requestFacet): ?int
    {
        if (! $requestFacet->hasPost('ajax.context')) {
            return null;
        }
        
        try {
            $context = SerializerUtil::unserializeJson($requestFacet->getPost('ajax.context'));
            if (! is_string($context['config'] ?? null) || ! is_string($context['hmac'] ?? null)) {
                return null;
            }
            
            if (! hash_equals(GeneralUtility::hmac($context['config'], 'InlineContext'), $context['hmac'])) {
                return null;
            }
            
            $config = SerializerUtil::unserializeJson($context['config']);
            if (! is_string($config['originalReturnUrl'] ?? null)) {
                return null;
            }
            
            return static::extractIdFromReturnUrl($config['originalReturnUrl']);
        } catch (\Throwable $e) {
        }
        
        return null;
    }
    
    /**
     * Tries to find a fallback pid using the root page id in the current site
     *
     * @param   \LaborDigital\T3ba\TypoContext\SiteFacet  $siteFacet
     *
     * @return int|null
     */
    protected static function findSiteRootPidFallback(SiteFacet $siteFacet): ?int
    {
        try {
            if ($siteFacet->hasCurrent()) {
                return $siteFacet->getCurrent()->getRootPageId();
            }
        } catch (\Throwable $exception) {
        }
        
        return null;
    }
    
    /**
     * Parses the given "returnUrl" and tries to find either the "id" parameter or other id containing parameters
     *
     * @param   string  $returnUrl
     *
     * @return int|null
     */
    protected static function extractIdFromReturnUrl(string $returnUrl): ?int
    {
        $query = Query::parse(
            Path::makeUri(
                'https://www.foo.bar' . $returnUrl
            )->getQuery()
        );
        
        // ID is present
        if (isset($query['id'])) {
            return (int)$query['id'];
        }
        
        // Editing a "page" record
        $editKey = array_search('edit', $query, true);
        if (is_string($editKey) && str_starts_with($editKey, 'edit[pages][')) {
            $id = substr($editKey, strlen('edit[pages]['), -1);
            if (is_numeric($id)) {
                return (int)$id;
            }
        }
        
        return null;
    }
}