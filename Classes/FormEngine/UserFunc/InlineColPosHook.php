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
 * Last modified: 2021.07.09 at 09:45
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Di\NoDiInterface;
use TYPO3\CMS\Backend\RecordList\RecordListGetTableHookInterface;

class InlineColPosHook implements NoDiInterface, RecordListGetTableHookInterface
{
    use ContainerAwareTrait;
    
    /**
     * Injects the -88 col pos for inline content elements in the given item array
     *
     * @param   array  $params
     */
    public function itemsProcFunc(array &$params): void
    {
        $params['items'] = [
            [
                $this->cs()->translator->translateBe('t3ba.t.tt_content.colPos.inlineContent'),
                '-88',
                null,
            ],
        ];
    }
    
    /**
     * Checks if a content record is "used" as an inline element
     *
     * @param   array  $params
     *
     * @return bool
     */
    public function isContentUsed(array $params): bool
    {
        if ($params['used']) {
            return true;
        }
        
        $record = $params['record'];
        
        return ((int)$record['colPos']) === -88 && ! empty($record['t3ba_inline']);
    }
    
    /**
     * @inheritDoc
     */
    public function getDBlistQuery($table, $pageId, &$additionalWhereClause, &$selectedFieldsList, &$parentObject)
    {
        if ($table !== 'tt_content') {
            return;
        }
        
        $additionalWhereClause .= ' AND `colPos`<>-88';
        
        if (isset($GLOBALS['TCA']['tt_content']['columns']['t3ba_inline'])) {
            $additionalWhereClause .= ' AND `t3ba_inline` = ""';
        }
    }
    
    
}