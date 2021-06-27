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
 * Last modified: 2020.03.20 at 18:03
 */

namespace LaborDigital\T3ba\Core\Util;

use Exception;
use Neunerlei\FileSystem\Fs;

class FilePermissionUtil
{
    
    /**
     * This helper works quite similar like GeneralUtility::fixPermissions() but without depending
     * on the existence of the PATH_site constant.
     *
     * This method is built to handle errors silently. The result of the method shows if there was an error (FALSE) or
     * not (TRUE)
     *
     * @param   string       $filename  The absolute path of the file to set the permissions for
     * @param   string|null  $mode      Optionally set a permission set like 0644 -> Make sure to use strings
     * @param   string|null  $group     Optionally set a group to set, otherwise the parent folder"s group will be used.
     *
     * @return bool
     * @noinspection CallableParameterUseCaseInTypeContextInspection
     */
    public static function setFilePermissions(string $filename, ?string $mode = null, ?string $group = null): bool
    {
        // No permissions in windows
        if (PHP_OS_FAMILY === 'Windows') {
            return true;
        }
        
        // Check if we can access the given file
        if (! file_exists($filename) || ! is_writable($filename)) {
            return false;
        }
        
        // Make sure we have values
        $inheritMode = ! empty($mode);
        if ($mode === null) {
            if (is_file($filename)) {
                $mode = $GLOBALS['TYPO3_CONF_VARS']['SYS']['fileCreateMask'] ?? '0664';
            } else {
                $mode = $GLOBALS['TYPO3_CONF_VARS']['SYS']['folderCreateMask'] ?? '0775';
            }
        }
        $inheritGroup = ! empty($group);
        if ($group === null) {
            if (isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['createGroup'])) {
                $group = $GLOBALS['TYPO3_CONF_VARS']['SYS']['createGroup'];
            }
            
            // Try to get the group from the parent directory
            try {
                if (empty($group)) {
                    $group = Fs::getGroup(dirname($filename));
                }
            } catch (Exception $exception) {
            }
        }
        
        // Make sure the mode has the correct integer value
        if (is_string($mode)) {
            if (strlen($mode) === 4 && $mode[0] !== '0') {
                $mode = '0' . substr($mode, 1);
            }
            if (strlen($mode) === 3) {
                $mode = (int)$mode;
            } else {
                $mode = octdec($mode);
            }
        }
        
        // Check if this is a directory
        if (is_dir($filename)) {
            // Update the directory recursively
            try {
                foreach (Fs::getDirectoryIterator($filename, false, ['folderFirst']) as $file) {
                    static::setFilePermissions(
                        $file->getPathname(),
                        $inheritMode ? $mode : null,
                        $inheritGroup ? $group : null
                    );
                }
            } catch (Exception $e) {
                return false;
            }
        }
        
        // Update a file
        try {
            Fs::setPermissions($filename, $mode);
            if (! empty($group)) {
                Fs::setGroup($filename, $group);
            }
        } catch (Exception $e) {
            return false;
        }
        
        return true;
    }
}
