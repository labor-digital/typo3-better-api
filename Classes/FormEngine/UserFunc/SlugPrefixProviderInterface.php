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


namespace LaborDigital\T3ba\FormEngine\UserFunc;


use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSlug;

interface SlugPrefixProviderInterface
{
    /**
     * MUST return the prefix of the slug field, including the base url
     *
     * @param   array    $context  Context information like the current site or the record
     * @param   TcaSlug  $slug     The parent object that calls this provider
     *
     * @return string
     */
    public function getPrefix(array $context, TcaSlug $slug): string;
}
