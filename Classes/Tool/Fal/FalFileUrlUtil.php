<?php
/*
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
 * Last modified: 2020.09.08 at 19:49
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Fal;


use LaborDigital\T3BA\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FalFileUrlUtil
{
    /**
     * @var \LaborDigital\T3BA\Tool\TypoContext\TypoContext
     */
    protected static $context;

    /**
     * Makes sure that the fal file url always has the host name prepended to it
     *
     * @param   string  $relativeUrl
     *
     * @return string
     */
    public static function makeAbsoluteUrl(string $relativeUrl): string
    {
        // Fallback if the relative url is already an absolute url
        /** @noinspection BypassedUrlValidationInspection */
        if (filter_var($relativeUrl, FILTER_VALIDATE_URL)) {
            return $relativeUrl;
        }

        return static::getHost() . '/' . ltrim($relativeUrl, '/');
    }

    /**
     * Returns the host of the current page
     *
     * @return string
     */
    protected static function getHost(): string
    {
        $context = static::$context ??
                   static::$context = GeneralUtility::makeInstance(TypoContext::class);

        return $context->request()->getHost();
    }
}
