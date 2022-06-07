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
 * Last modified: 2022.06.07 at 21:25
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\Tool\Fal\Util;


use LaborDigital\T3ba\Core\Di\NoDiInterface;
use Neunerlei\PathUtil\Path;

class FalFilePathUtil implements NoDiInterface
{
    /**
     * Helper to receive a fal path and parses it to avoid common errors.
     * The resulting array has three keys, "storage" containing the storage id (1 if none was given), "path",
     * the array of path elements given and "identifier" as the combined, prepared string to pass to the resource
     * factory.
     *
     * The method will automatically strip superfluous "fileadmin" parts when the storage id is 1.
     *
     * @param   string  $falPath  Something like /myFolder/mySubFolder, 1:/myFolder, 2
     *
     * @return array
     */
    public static function getFalPathArray(string $falPath): array
    {
        $falPath = trim(trim($falPath, '\\/ '));
        $falPath = Path::unifySlashes($falPath, '/');
        $parts = explode(':', $falPath);
        
        if (count($parts) === 1 && is_numeric($parts[0])) {
            $parts[] = '';
        }
        
        $storageId = count($parts) > 1 && is_numeric($parts[0]) ? (int)array_shift($parts) : 1;
        $remainingPathParts = array_values(array_filter(explode('/', implode(':', $parts))));
        
        if (empty($remainingPathParts)) {
            $remainingPathParts[] = '';
        }
        
        // Make sure to remove fileadmin from path when using the storage with id 1
        if ($storageId === 1 && $remainingPathParts[0] === 'fileadmin') {
            array_shift($remainingPathParts);
        }
        
        // Done
        return [
            'storage' => $storageId,
            'path' => $remainingPathParts,
            'identifier' => $storageId . ':/' . implode('/', $remainingPathParts),
        ];
    }
}