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
 * Last modified: 2021.07.13 at 18:57
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Frontend\TablePreview;

use LaborDigital\Typo3BetterApi\Middleware\TablePreviewResolverMiddleware;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\SingletonInterface;

class PreviewHandler implements SingletonInterface
{
    public const PREVIEW_QUERY_KEY = TablePreviewResolverMiddleware::PREVIEW_QUERY_KEY;

    /**
     * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
     */
    protected $typoContext;

    public function __construct(TypoContext $typoContext)
    {
        $this->typoContext = $typoContext;
    }

    /**
     * Checks if the given request contains the preview query key and updates
     * the visibility context to match the request
     *
     * @param   \Psr\Http\Message\ServerRequestInterface  $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function handleRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        $query = $request->getQueryParams()[static::PREVIEW_QUERY_KEY];
        [$tables, $hash] = explode('::', $query);

        if (empty($hash)) {
            return $request;
        }

        $tables = array_unique(Arrays::makeFromStringList((string)$tables));
        if (empty($tables)) {
            return $request;
        }

        if ($this->getUrlHash($tables) !== $hash) {
            return $request;
        }

        $this->typoContext->Visibility()->setIncludeHiddenOfTables($tables);

        return $request->withQueryParams(array_merge($request->getQueryParams(), ['no_cache' => '1']));
    }

    /**
     * Automatically attaches the list of allowed tables to the given link
     *
     * @param   string  $link    A valid link as a string to attach the table preview get parameter to
     * @param   array   $tables  The list of allowed tables
     *
     * @return string
     */
    public function appendUrlValueToLink(string $link, array $tables): string
    {
        $glue = str_contains($link, '?') ? '&' : '?';

        return $link . $glue . static::PREVIEW_QUERY_KEY . '=' . urlencode($this->getUrlValue($tables));
    }

    /**
     * Returns the url value that must be set in order to preview the given tables
     *
     * @param   array  $tables
     *
     * @return string
     */
    public function getUrlValue(array $tables): string
    {
        $tables = array_unique($tables);
        $hash   = $this->getUrlHash($tables);

        return implode(',', array_map('trim', $tables)) . '::' . $hash;
    }

    /**
     * Generates a security hash to validate the list of hidden tables against.
     *
     * @param   array  $tables  The list of tables that are allowed by the url.
     *
     * @return string
     */
    public function getUrlHash(array $tables): string
    {
        if (! $this->typoContext->BeUser()->isLoggedIn()) {
            return '';
        }

        $key = $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] ?? '';
        sort($tables);

        return md5(sha1(implode(':', array_unique($tables)) . ':' . $key . ':' . static::PREVIEW_QUERY_KEY));
    }
}
