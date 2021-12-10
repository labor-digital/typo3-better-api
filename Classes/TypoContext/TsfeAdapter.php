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
 * Last modified: 2021.06.27 at 16:27
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\TypoContext;


use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @deprecated this class is no longer used, and will be removed in v11
 */
class TsfeAdapter extends TypoScriptFrontendController
{
    /**
     * Helper to extract the most logical id from the tsfe instance
     *
     * @param   \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController  $tsfe
     *
     * @return int|null
     */
    public static function getCurrentId(TypoScriptFrontendController $tsfe): ?int
    {
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
}