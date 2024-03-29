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
/**
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
 * Last modified: 2020.03.20 at 14:07
 */

namespace LaborDigital\T3ba\Tool\Rendering;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\Rendering\Renderer\DatabaseRecordListRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldListRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\FieldRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\InlineContentPreviewRenderer;
use LaborDigital\T3ba\Tool\Rendering\Renderer\RecordTableRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\SingletonInterface;

class BackendRenderingService implements SingletonInterface
{
    use ContainerAwareTrait;
    
    /**
     * This method can be used to render a database record list in the backend.
     * The process is normally quite painful but with this interface it should become fairly easy.
     *
     * @param   string|mixed  $tableName  The table of which you want to render a database table
     * @param   array         $fields     An array of columns that should be read from the database
     * @param   array         $options    Additional options to configure the output
     *                                    - limit int (20): The max number of items to display
     *                                    - where string: A MYSQL query string beginning at "SELECT ... WHERE " <- your string
     *                                    starts here
     *                                    - pid int ($CURRENT_PID): The page id to limit the items to.
     *                                    - callback callable: This can be used to change or extend the default
     *                                    settings of the list renderer. The callback receives the preconfigured
     *                                    instance as parameter right before the list is rendered.
     *
     * @return string
     * @see \TYPO3\CMS\Recordlist\RecordList\DatabaseRecordList
     */
    public function renderDatabaseRecordList($tableName, array $fields, array $options = []): string
    {
        return $this->getService(DatabaseRecordListRenderer::class)->render($tableName, $fields, $options);
    }
    
    /**
     * Renders a list of selected fields for a database row on a single table as HTML.
     * Useful for a fast preview of a single record
     *
     * @param   string|mixed  $tableName  The name of the table to render the fields for
     * @param   array         $row        The row to use as data source for the fields to render
     * @param   array         $fields     The list of fields that should be rendered
     *
     * @return string
     */
    public function renderRecordFieldList($tableName, array $row, array $fields): string
    {
        return $this->getService(FieldListRenderer::class)->render($tableName, $row, $fields);
    }
    
    /**
     * Works quite similar to renderRecordFieldList() but is designed to render a table of multiple records,
     * instead of just the data of a single record.
     *
     * @param   string|mixed  $tableName  The name of the database table to render the records for
     * @param   array         $rows       The list of rows that are used as data source to render the table with
     * @param   array         $fields     The list of fields that should be rendered for each row
     *
     * @return string
     */
    public function renderRecordTable($tableName, array $rows, array $fields): string
    {
        return $this->getService(RecordTableRenderer::class)->render($tableName, $rows, $fields);
    }
    
    /**
     * Returns the "title"-value in record, $row, from table, $table
     * The field(s) from which the value is taken is determined by the "ctrl"-entries 'label', 'label_alt' and 'label_alt_force'
     *
     * @param   string|mixed  $tableName  The name of the database table to render the title for
     * @param   array         $row        The row to use as data source for the title to render
     *
     * @return string
     */
    public function renderRecordTitle($tableName, array $row): string
    {
        return BackendUtility::getRecordTitle(
            NamingUtil::resolveTableName($tableName),
            $row
        );
    }
    
    /**
     * Renders a backend preview of inline related content elements. This is useful if you want to render the
     * nested backend preview of IRRE related content elements, in another content element. For example, in a button group element,
     * you can relate multiple button elements, which should be rendered in the backend preview.
     *
     * NOTE: The renderer will remove all link tags inside the rendered previews to avoid issues with the backend
     * edit link tags.
     *
     * @param   array   $parentRow    The tt_content database row which has the child elements that should be rendered
     * @param   string  $inlineField  A column/field name on the tt_content table that is used as inline field.
     *
     * @return string
     */
    public function renderInlineContentPreview(array $parentRow, string $inlineField): string
    {
        return $this->getService(InlineContentPreviewRenderer::class)->render($parentRow, $inlineField);
    }
    
    /**
     * Renders the title of the table translated for the current user
     *
     * @param   string  $tableName
     *
     * @return string
     * @see FieldRenderer::renderTableTitle()
     */
    public function renderTableTitle(string $tableName): string
    {
        return $this->getService(FieldRenderer::class)->renderTableTitle($tableName);
    }
    
    /**
     * Renders the translated label string given to a field of a table
     *
     * @param   string  $tableName  The name of the database table
     * @param   string  $fieldName  The column/field name in the table to render
     *
     * @return string
     * @see FieldRenderer::renderLabel()
     */
    public function renderFieldLabel(string $tableName, string $fieldName): string
    {
        return $this->getService(FieldRenderer::class)->renderLabel($tableName, $fieldName);
    }
    
    /**
     * Renders the value of a single field in a given row of a database table.
     *
     * @param   string  $tableName  The name of the database table
     * @param   string  $fieldName  The column/field name in the table to render
     * @param   array   $row        The raw database row to extract the value from
     * @param   bool    $textOnly   By default, the rendered value may contain HTML markup, if you set this
     *                              flag to true those cases will be replaced with a textual representation
     *
     * @return string|null
     * @see FieldRenderer::renderFieldValue()
     */
    public function renderFieldValue(string $tableName, string $fieldName, array $row, bool $textOnly = false): ?string
    {
        return $this->getService(FieldRenderer::class)->render($tableName, $fieldName, $row, $textOnly);
    }
}
