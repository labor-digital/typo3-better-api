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
 * Last modified: 2021.06.25 at 13:51
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\BackendPreview\Hook;


use LaborDigital\T3ba\Core\Di\NoDiInterface;

class BackendPreviewUtils implements NoDiInterface
{
    /**
     * A list of method links into the actual renderer instance
     *
     * @var callable[]
     */
    protected $links;
    
    /**
     * BackendPreviewUtils constructor.
     *
     * @param   array  $links
     */
    public function __construct(array $links)
    {
        $this->links = $links;
    }
    
    /**
     * Renders the preview header the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultHeader(): string
    {
        return call_user_func($this->links[__FUNCTION__]);
    }
    
    /**
     * Renders the preview content/body the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultContent(): string
    {
        return call_user_func($this->links[__FUNCTION__]);
    }
    
    /**
     * Renders the preview footer the same way the normal TYPO3 does
     *
     * @return string
     */
    public function renderDefaultFooter(): string
    {
        return call_user_func($this->links[__FUNCTION__]);
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
        return call_user_func($this->links[__FUNCTION__], $fields);
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
        return call_user_func($this->links[__FUNCTION__], $tableName, $rows, $fields);
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
        return call_user_func($this->links[__FUNCTION__], $linkText);
    }
}
