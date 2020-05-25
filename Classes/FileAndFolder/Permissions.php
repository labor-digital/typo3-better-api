<?php
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

namespace LaborDigital\Typo3BetterApi\FileAndFolder;

use Exception;
use Neunerlei\FileSystem\Fs;

class Permissions
{
    
    /**
     * This helper works quite similar like GeneralUtility::fixPermissions() but without depending
     * on the existence of the PATH_site constant.
     *
     * This method is built to handle errors silently. The result of the method shows if there was an error (FALSE) or
     * not (TRUE)
     *
     * @param string      $filename The absolute path of the file to set the permissions for
     * @param string|null $mode     Optionally set a permission set like 0644 -> Make sure to use strings
     * @param string|null $group    Optionally set a group to set, otherwise the parent folder"s group will be used.
     *
     * @return bool
     * @throws \LaborDigital\Typo3BetterApi\BetterApiException
     */
    public static function setFilePermissions(string $filename, ?string $mode = null, ?string $group = null): bool
    {
        // No permissions in windows
        if (stripos(PHP_OS, 'win') === 0) {
            return true;
        }
        
        // Check if we can access the given file
        if (!file_exists($filename) || !is_writable($filename)) {
            return false;
        }
        
        // Make sure we have values
        $inheritMode = !empty($mode);
        if ($mode === null) {
            if (is_file($filename)) {
                $mode = isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['fileCreateMask'])
                    ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['fileCreateMask']
                    : '0664';
            } else {
                $mode = isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['folderCreateMask'])
                    ? $GLOBALS['TYPO3_CONF_VARS']['SYS']['folderCreateMask']
                    : '0775';
            }
        }
        $inheritGroup = !empty($group);
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
            if (!empty($group)) {
                Fs::setGroup($filename, $group);
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
