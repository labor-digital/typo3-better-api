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


use InvalidArgumentException;
use LaborDigital\T3ba\Core\Di\PublicServiceInterface;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\ContentTypeUtil;
use LaborDigital\T3ba\Tool\Tca\ContentType\Domain\ContentRepository;
use LaborDigital\T3ba\Tool\Tca\TcaUtil;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class RecordTableRenderer implements PublicServiceInterface
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
     * Renders a table of multiple rows based on their fields
     *
     * @param   string|mixed  $tableName
     * @param   array         $rows
     * @param   array         $fields
     *
     * @return string
     * @see \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService::renderRecordTable()
     */
    public function render($tableName, array $rows, array $fields): string
    {
        if (empty($rows)) {
            return '';
        }
        
        $tableName = NamingUtil::resolveTableName($tableName);
        
        // Validate rows to be array, and automatically convert ext base entities into rows
        foreach ($rows as $k => &$row) {
            if (! is_array($row)) {
                if ($row instanceof AbstractEntity) {
                    $row = TypoContext::getInstance()->di()->cs()->db
                        ->getQuery($tableName)->withWhere(['uid' => $row->getUid()])->getFirst();
                    
                    if (! is_array($row)) {
                        throw new InvalidArgumentException(
                            'Can\'t render a row (' . $k . '), because the data could not be resolved');
                    }
                    
                } else {
                    throw new InvalidArgumentException(
                        'Can\'t render a row (' . $k . '), because it is not an array');
                }
            }
        }
        
        return TcaUtil::runWithResolvedTypeTca(reset($rows), $tableName, function () use ($tableName, $rows, $fields) {
            $renderedRows = [];
            
            foreach ($rows as $row) {
                if ($tableName === 'tt_content') {
                    $renderedRows[] = $this->renderTtContent($row, $fields);
                }
                
                $renderedRows[] = $this->renderInternal($tableName, $row, $fields);
            }
            
            return $this->renderTable($tableName, $renderedRows, $this->renderHeaders($tableName, $fields));
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
        $columns = [];
        
        foreach ($fields as $field) {
            if (empty($row[$field]) && $row[$field] !== 0) {
                $columns[] = $this->renderColumn(null);
                continue;
            }
            
            $content = $this->fieldRenderer->render($tableName, $field, $row);
            $columns[] = $this->renderColumn($content);
            
        }
        
        return $this->renderRow($columns);
    }
    
    /**
     * Renders a single value column
     *
     * @param   string|null  $content
     * @param   string       $tag
     *
     * @return string
     */
    protected function renderColumn(?string $content, string $tag = 'td'): string
    {
        return '<' . $tag . '>' . ($content ?? '&nbsp;') . '</' . $tag . '>';
    }
    
    /**
     * Renders a row of n columns
     *
     * @param   array   $columns
     * @param   string  $tag
     *
     * @return string
     */
    protected function renderRow(array $columns, string $tag = 'tr'): string
    {
        return '<' . $tag . '>' . implode($columns) . '</' . $tag . '>';
    }
    
    /**
     * Renders the header column of field labels
     *
     * @param   string  $tableName
     * @param   array   $fields
     *
     * @return string
     */
    protected function renderHeaders(string $tableName, array $fields): string
    {
        $columns = [];
        
        foreach ($fields as $field) {
            $label = $this->fieldRenderer->renderLabel($tableName, $field);
            $columns[] = $this->renderColumn($this->htmlEncode($label), 'th');
        }
        
        return $this->renderRow($columns);
    }
    
    /**
     * Combines all elements of the table together in a html markup
     *
     * @param   string  $tableName
     * @param   array   $rows
     * @param   string  $headers
     *
     * @return string
     */
    protected function renderTable(string $tableName, array $rows, string $headers): string
    {
        return
            '<table class="table" style="{margin-top:10px;margin-bottom:0}">' .
            '<caption>' . $this->fieldRenderer->renderTableTitle($tableName) . '</caption>' .
            '<thead>' . $headers . '</thead>' .
            '<tbody>' . implode($rows) . '</tbody>' .
            '</table>';
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