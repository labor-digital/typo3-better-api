<?php
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\BackendPreview;

use LaborDigital\T3BA\Core\DependencyInjection\PublicServiceInterface;

interface BackendListLabelRendererInterface extends PublicServiceInterface
{
    /**
     * Should use the given row to render the backend list label.
     * It should return a string, that will be used as list label and appended after the default list label.
     *
     * @param   array  $row      The database row to render the list label from
     * @param   array  $options  Additional options that may have been passed by the TCA
     *
     * @return string
     */
    public function renderBackendListLabel(array $row, array $options): string;
}
