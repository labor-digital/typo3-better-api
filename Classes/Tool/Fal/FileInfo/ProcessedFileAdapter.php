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

namespace LaborDigital\T3BA\Tool\Fal\FileInfo;

use TYPO3\CMS\Core\Resource\ProcessedFile;

class ProcessedFileAdapter extends ProcessedFile
{
    
    /**
     * Internal helper to inject additional properties into processed files
     *
     * @param   \TYPO3\CMS\Core\Resource\ProcessedFile  $file
     * @param   string                                  $key
     * @param                                           $value
     */
    public static function injectProperty(ProcessedFile $file, string $key, $value): void
    {
        $file->properties[$key] = $value;
    }
    
    /**
     * Extracts a single property from a processed file ignoring the original file completely
     *
     * @param   \TYPO3\CMS\Core\Resource\ProcessedFile  $file
     * @param   string                                  $key
     *
     * @return mixed
     */
    public static function getRawProperty(ProcessedFile $file, string $key)
    {
        return $file->properties[$key];
    }
}
