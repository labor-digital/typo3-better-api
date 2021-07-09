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


namespace LaborDigital\T3ba\Tool\BackendPreview\Hook;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\Rendering\BackendRenderingService;

class BackendPreviewUtils implements NoDiInterface
{
    public const KEY_RENDERING_SERVICE = 0;
    public const KEY_DEFAULT_HEADER = 1;
    public const KEY_DEFAULT_CONTENT = 2;
    public const KEY_DEFAULT_FOOTER = 3;
    public const KEY_LINK_WRAP = 4;
    public const KEY_ROW = 5;
    
    /**
     * @var \Closure
     */
    protected $bridge;
    
    public function __construct(\Closure $bridge)
    {
        $this->bridge = $bridge;
    }
    
    /**
     * Renders the preview header the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultHeader(): string
    {
        return ($this->bridge)(static::KEY_DEFAULT_HEADER);
    }
    
    /**
     * Renders the preview content/body the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultContent(): string
    {
        return ($this->bridge)(static::KEY_DEFAULT_CONTENT);
    }
    
    /**
     * Renders the preview footer the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultFooter(): string
    {
        return ($this->bridge)(static::KEY_DEFAULT_FOOTER);
    }
    
    /**
     * Renders a list of fields as a nice html with both the value and the translated label
     *
     * @param   array  $fields  The list of db fields that should be rendered
     *
     * @return string
     * @see \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService::renderRecordFieldList()
     */
    public function renderFieldList(array $fields): string
    {
        return $this->getRenderingService()->renderRecordFieldList('tt_content', $this->getRow(), $fields);
    }
    
    /**
     * Renders a HTML table of database record rows with their label as headers.
     * Useful for rendering the backend preview of list modules.
     *
     * @param   string|mixed  $tableName  The name of the database table to render the records for
     * @param   array         $rows       The list of rows that are used as data source to render the table with
     * @param   array         $fields     The list of fields that should be rendered for each row
     *
     * @return string
     * @see \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService::renderRecordTable()
     */
    public function renderRecordTable($tableName, array $rows, array $fields): string
    {
        return $this->getRenderingService()->renderRecordTable($tableName, $rows, $fields);
    }
    
    /**
     * Renders a backend preview of inline related content elements. This is useful if you want to render the
     * nested backend preview of IRRE related content elements, in another content element. For example, in a button group element,
     * you can relate multiple button elements, which should be rendered in the backend preview.
     *
     * NOTE: The renderer will remove all link tags inside the rendered previews to avoid issues with the backend
     * edit link tags.
     *
     * @param   string  $inlineField  A column/field name on the tt_content table that is used as inline field.
     *
     * @return string
     */
    public function renderInlineContentPreview(string $inlineField): string
    {
        return $this->getRenderingService()->renderInlineContentPreview($this->getRow(), $inlineField);
    }
    
    /**
     * Wraps the given $linkText with a link to the edit mode of the record
     *
     * @param   string  $linkText
     *
     * @return string
     */
    public function wrapWithEditLink(string $linkText): string
    {
        return ($this->bridge)(static::KEY_LINK_WRAP, $linkText);
    }
    
    /**
     * Returns the row of the item to be previewed
     *
     * @return array
     */
    protected function getRow(): array
    {
        return ($this->bridge)(static::KEY_ROW);
    }
    
    /**
     * Retrieves the instance of the backend rendering service
     *
     * @return \LaborDigital\T3ba\Tool\Rendering\BackendRenderingService
     */
    protected function getRenderingService(): BackendRenderingService
    {
        return ($this->bridge)(static::KEY_RENDERING_SERVICE);
    }
    
}
