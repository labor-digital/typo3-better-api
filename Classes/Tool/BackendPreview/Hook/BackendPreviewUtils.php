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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\BackendPreview\Hook;


class BackendPreviewUtils
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
     * @param   array        $fields     The list of db fields that should be rendered
     * @param   string|null  $tableName  The name of the table to render the fields for
     *
     * @return string
     * @see \LaborDigital\T3BA\Tool\BackendPreview\Renderer\FieldListRenderer::render()
     */
    public function renderFieldList(array $fields, ?string $tableName = null): string
    {
        return call_user_func($this->links[__FUNCTION__], $fields, $tableName);
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
