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


namespace LaborDigital\T3ba\Tool\Simulation\Adapter;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;

/**
 * Class PageRepositoryAdapter
 *
 * Sadly the page repository is rather suborn when it comes to dynamically changing the access rights.
 * Because of that we have to use this adapter to forcefully reinitialize it when hidden pages should be looked up
 *
 * @package LaborDigital\T3ba\Tool\Simulation\Adapter
 */
class PageRepositoryAdapter extends PageRepository implements NoDiInterface
{
    public static function backupAccessRules(PageRepository $pageRepository): array
    {
        return [$pageRepository->where_groupAccess, $pageRepository->where_hid_del];
    }
    
    public static function restoreAccessRules(PageRepository $pageRepository, array $rules): void
    {
        $pageRepository->where_groupAccess = $rules[0];
        $pageRepository->where_hid_del = $rules[1];
    }
    
    public static function reinitializeWithState(PageRepository $pageRepository, bool $showHidden): array
    {
        $backup = static::backupAccessRules($pageRepository);
        
        $pageRepository->init($showHidden);
        
        return $backup;
    }
}