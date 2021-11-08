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

namespace LaborDigital\T3ba\Tool\BackendPreview;

use LaborDigital\T3ba\Core\Di\PublicServiceInterface;

interface BackendListLabelRendererInterface extends PublicServiceInterface
{
    /**
     * Should use the given row to render the backend list label.
     * It should return a string, that will be used as list label and appended after the default list label.
     *
     * @param   array   $row        The database row to render the list label from
     * @param   array   $options    Additional options that may have been passed by the TCA
     * @param   string  $tableName  The name of the table the renderer is executed for.
     *                              To avoid breaking changes until the next major release this parameter
     *                              should be set using string $tableName = ''
     *
     * @return string
     * @todo in v11 add an official third parameter "tableName"
     */
    public function renderBackendListLabel(array $row, array $options): string;
}
