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
 * Last modified: 2021.06.25 at 22:00
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;

class FieldListRenderer implements PublicServiceInterface
{
    /**
     * @var \LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository
     */
    protected $contentRepository;
    
    /**
     * @var FieldRenderer
     */
    protected $fieldRenderer;
    
    public function __construct(ContentRepository $contentRepository, FieldRenderer $fieldRenderer)
    {
        $this->contentRepository = $contentRepository;
        $this->fieldRenderer = $fieldRenderer;
    }
    
    /**
     * Renders a list of field values as a HTML table
     *
     * @param   string|mixed  $tableName  The name of the table to render the fields for
     * @param   array         $row        The row to use as data source for the fields to render
     * @param   array         $fields     The list of fields that should be rendered
     *
     * @return string
     */
    public function render($tableName, array $row, array $fields): string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        return TcaUtil::runWithResolvedTypeTca($row, $tableName, function () use ($tableName, $row, $fields) {
            if ($tableName === 'tt_content') {
                return $this->renderTtContent($row, $fields);
            }
            
            return $this->renderInternal($tableName, $row, $fields);
        });
    }
    
    /**
     * We handle the tt_content table a bit different, in that we also resolve the extension columns
     *
     * @param   array  $row
     * @param   array  $fields
     *
     * @return string
     */
    protected function renderTtContent(array $row, array $fields): string
    {
        $row = $this->contentRepository->getExtendedRow($row);
        
        return ContentTypeUtil::runWithRemappedTca($row, function () use ($row, $fields) {
            return $this->renderInternal('tt_content', $row, $fields);
        });
    }
    
    /**
     * Actual renderer of the fields of a specific table
     *
     * @param   string  $tableName
     * @param   array   $row
     * @param   array   $fields
     *
     * @return string
     */
    protected function renderInternal(string $tableName, array $row, array $fields): string
    {
        $rows = [];
        foreach ($fields as $field) {
            if (empty($row[$field]) && $row[$field] !== 0) {
                continue;
            }
            
            $content = $this->fieldRenderer->render($tableName, $field, $row);
            
            if ($content === null) {
                continue;
            }
            
            $rows[] = $this->renderRow(
                $this->fieldRenderer->renderLabel($tableName, $field),
                $content
            );
        }
        
        return $this->renderTable(array_filter($rows));
    }
    
    /**
     * Generates the HTML of a single field row
     *
     * @param   string  $label
     * @param   string  $content
     *
     * @return string
     */
    protected function renderRow(string $label, string $content): string
    {
        return '<td><strong>' . $this->htmlEncode($label) . ': </strong></td><td>' . $content . '</td>';
    }
    
    /**
     * Generates the outer HTML of the field table, by concatenating the given rows
     *
     * @param   array  $rows
     *
     * @return string
     */
    protected function renderTable(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }
        
        return
            '<table class="table" style="{margin-top:10px;margin-bottom:0}">' .
            '<tr>' . implode('</tr><tr>', $rows) . '</tr></table>';
    }
    
    /**
     * Helper to encode html special characters
     *
     * @param $value
     *
     * @return string
     */
    protected function htmlEncode($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5);
    }
}
