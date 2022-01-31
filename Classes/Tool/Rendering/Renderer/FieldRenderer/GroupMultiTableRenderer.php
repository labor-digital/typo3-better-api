<?php
/*
 * Copyright 2022 LABOR.digital
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
 * Last modified: 2022.01.31 at 20:12
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer;


use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\Rendering\Renderer\RendererUtilsTrait;
use LaborDigital\T3ba\Tool\Translation\Translator;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;

class GroupMultiTableRenderer implements SingletonInterface
{
    protected const EMPTY_LABEL = 'LLL:EXT:core/Resources/Private/Language/locallang_common.xlf:notAvailableAbbreviation';
    
    use RendererUtilsTrait;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Database\DbService
     */
    protected DbService $dbService;
    
    /**
     * @var \LaborDigital\T3ba\Tool\Translation\Translator
     */
    protected Translator $translator;
    
    public function __construct(DbService $dbService, Translator $translator)
    {
        $this->dbService = $dbService;
        $this->translator = $translator;
    }
    
    /**
     * Renders a preview for a "group" field where multiple tables are allowed but no foreign_table has been set.
     *
     * @param   string  $tableName  The name of the database table
     * @param   string  $fieldName  The column/field name in the table to render
     * @param   array   $row        The raw database row to extract the value from
     *
     * @return string
     */
    public function render(string $tableName, string $fieldName, array $row): string
    {
        if (empty($row['uid'])) {
            return '';
        }
        
        $related = $this->dbService->getQuery($tableName)
                                   ->withWhere(['uid' => $row['uid']])
                                   ->getRelated([$fieldName]);
        
        if (! is_array($related[$fieldName][$row['uid']] ?? null)) {
            return $this->translator->translateBe(static::EMPTY_LABEL);
        }
        
        $out = [];
        
        foreach ($related[$fieldName][$row['uid']] as $_row) {
            /** @var \LaborDigital\T3ba\Tool\Database\BetterQuery\Standalone\RelatedRecordRow $_row */
            $out[] = BackendUtility::getRecordTitle($_row->getTableName(), $_row->getRow());
        }
        
        if (empty($out)) {
            return $this->translator->translateBe(static::EMPTY_LABEL);
        }
        
        return implode('; ', $out);
    }
}