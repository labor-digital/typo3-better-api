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
 * Last modified: 2022.01.31 at 20:31
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Rendering\Renderer;


trait RendererUtilsTrait
{
    
    /**
     * Helper to render a html table
     *
     * @param   array        $rows     Rows wrapped with <tr> tags
     * @param   string|null  $headers  Concatenated columns wrapped with <th> tags
     * @param   string|null  $caption  A caption to render at the beginning of the table
     *
     * @return string
     */
    protected function renderTable(array $rows, ?string $headers = null, ?string $caption = null): string
    {
        if (empty($rows)) {
            return '';
        }
        
        return
            '<table class="table" style="{margin-top:10px;margin-bottom:0}">' .
            (! empty($caption) ? '<caption>' . $caption . '</caption>' : '') .
            (! empty($headers) ? '<thead>' . $headers . '</thead>' : '') .
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