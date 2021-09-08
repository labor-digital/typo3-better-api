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
 * Last modified: 2021.09.08 at 08:53
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Database\BetterQuery\Util;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\Page\PageService;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/**
 * WARNING: This class is an implementation detail and subject to change in v11 of this extension.
 *
 * @internal
 */
class OverlayResolver implements PublicServiceInterface
{
    /**
     * @var \TYPO3\CMS\Frontend\Page\PageRepository
     */
    protected $pageRepository;
    
    public function __construct(PageService $pageService)
    {
        $this->pageRepository = $pageService->getPageRepository();
    }
    
    /**
     * Resolves translation and version overlays of a single row of a given database table
     *
     * @param   string                  $tableName          The name of the database table
     * @param   array                   $row                The row to be resolved
     * @param   bool                    $useVersionOverlay  True if version overlays should be used
     * @param   QuerySettingsInterface  $settings           The query settings object
     *
     * @return array
     */
    public function resolve(
        string $tableName,
        array $row,
        bool $useVersionOverlay,
        QuerySettingsInterface $settings
    ): array
    {
        $givenRow = $row;
        
        if ($useVersionOverlay) {
            $this->pageRepository->versionOL($tableName, $row, true);
            if (! $row) {
                $row = $givenRow;
            }
        }
        
        if (! $settings->getRespectSysLanguage()) {
            return $row;
        }
        
        $languageUid = $settings->getLanguageUid();
        if ($languageUid < 0) {
            return $row;
        }
        
        // This is basically a copy of the logic in PageRepository->getLanguageOverlay()
        if (! Arrays::hasPath($GLOBALS, ['TCA', $tableName, 'ctrl', 'languageField'])) {
            return $row;
        }
        
        if ($tableName === 'pages') {
            return $this->pageRepository->getPageOverlay($row, $languageUid);
        }
        
        return $this->pageRepository->getRecordOverlay(
                $tableName,
                $row,
                $languageUid,
                is_string($settings->getLanguageOverlayMode()) ? 'hideNonTranslated' : '1'
            ) ?? $row;
    }
}