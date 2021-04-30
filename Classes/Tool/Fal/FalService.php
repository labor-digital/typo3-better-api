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

namespace LaborDigital\T3BA\Tool\Fal;

use InvalidArgumentException;
use LaborDigital\T3BA\Core\Di\ContainerAwareTrait;
use LaborDigital\T3BA\Tool\Fal\FileInfo\FileInfo;
use LaborDigital\T3BA\Tool\Fal\FileInfo\ProcessedFileAdapter;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
use RuntimeException;
use Throwable;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\UploadSizeException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

class FalService implements SingletonInterface
{
    use ContainerAwareTrait;
    use ResizedImageOptionsTrait;
    
    /**
     * Returns the instance of the core file repository to find file references using the root level api
     *
     * @return \TYPO3\CMS\Core\Resource\FileRepository
     */
    public function getFileRepository(): FileRepository
    {
        return $this->getService(FileRepository::class);
    }
    
    /**
     * Returns the resource factory instance to interact with the root level FAL api
     *
     * @return \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    public function getResourceFactory(): ResourceFactory
    {
        return $this->getService(ResourceFactory::class);
    }
    
    /**
     * This method has two modes to operate in.
     * The first one is by only supplying a $uid. This uid should be a valid uid of a row in "sys_file"
     * The result will be either null or an object of type "File"
     *
     * The second mode is by supplying a $uid, $table and $field.
     * This will now search for sys_file_references matching the given criteria.
     * The result will be either null, an array of FileReference objects or a single FileReference object
     * depending on the $onlyFirst parameter.
     * As an example ($uid_of_tt_content, "tt_content", "image") will result in an array of FileReferences
     * for that content element.
     *
     * $uid can also be given as "query" which is the case when you using a typolink field in the tca(?)
     *
     * @param   int|string|null  $uid        Either a sys_file | uid or a uid of the record using as reference
     *                                       NULL To select all references of with the matching $table and $field
     *                                       The $uid field alone can handle all strange inputs like the following as
     *                                       well.
     *                                       - "2:myfolder/myfile.jpg" (combined identifier)
     *                                       - "23" (file UID)
     *                                       - "uploads/myfile.png" (backwards-compatibility, storage "0")
     *                                       - "file:23"
     *
     * @param   string|null      $table      The table to use as reference
     * @param   string|null      $field      The field to use as reference
     * @param   bool             $onlyFirst  If true only the first result in an array of FileReferences will be
     *                                       returned
     *
     * @return null|array|\TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileReference|\TYPO3\CMS\Core\Resource\FileReference[]
     */
    public function getFile($uid, ?string $table = null, ?string $field = null, bool $onlyFirst = true)
    {
        // Check how we select
        $useUidOnly = empty($table) || empty($field);
        if ($useUidOnly && $uid === null) {
            throw new InvalidArgumentException(
                '$uid can"t be null if neither a $table nor a $field are defined'
            );
        }
        
        try {
            if (is_int($uid)) {
                $uid = (string)$uid;
            }
            
            // Read the strange string identifiers
            if (
                (is_string($uid) && ! is_numeric($uid))
                || (is_numeric($uid) && $useUidOnly)
            ) {
                // Prepare identifier
                $identifier = $uid;
                
                // Check if we got a Pseudo Link|Label combination...
                // Oh gosh typo3 is so weired...
                if (strpos($identifier, '%') !== false && strpos($identifier, '|') !== false) {
                    $identifier = explode('|', $identifier);
                    $identifier = reset($identifier);
                    // Crack strange multi-encodings
                    for ($i = 0; $i < 25; $i++) {
                        $identifier = rawurldecode($identifier);
                        if (strpos($identifier, '%') === false) {
                            break;
                        }
                    }
                } // Read query like uid
                elseif (strpos($identifier, '=') !== false) {
                    $params = parse_url($uid);
                    if (! isset($params['query'])) {
                        throw new RuntimeException('');
                    }
                    parse_str($params['query'], $params);
                    if (! isset($params['uid'])) {
                        throw new RuntimeException('');
                    }
                    $identifier = $params['uid'];
                } // Parse path
                elseif (strpos($identifier, '/') !== false) {
                    $path = $this->getFalPathArray($identifier);
                    $identifier = $path['identifier'];
                }
                
                // Check if we got a file
                $file = $this->getResourceFactory()->retrieveFileOrFolderObject($identifier);
                if ($file instanceof File) {
                    return $file;
                }
                
                return null;
            }
            
            $file = $this->getFileRepository()->findByRelation($table, $field, $uid);
            
            if (! empty($file)) {
                return $onlyFirst ? reset($file) : $file;
            }
            
            return $onlyFirst ? null : [];
        } catch (Throwable $e) {
            return null;
        }
    }
    
    /**
     * Similar to getFile() as it finds a file object in the FAL. However this will
     * solely search for file references and requires a numeric id for a reference to find in the database.
     *
     * @param   int  $uid  The uid of the reference in the sys_file_reference table
     *
     * @return \TYPO3\CMS\Core\Resource\FileReference
     */
    public function getFileReference(int $uid): FileReference
    {
        return $this->getResourceFactory()->getFileReferenceObject($uid);
    }
    
    /**
     * This method creates a new file reference. It expects to receive a FAL file instance and
     * some metadata to create the mapping on an external field.
     *
     * IMPORTANT: There will be no permission checks when creating the reference!
     *
     * @param   FileInterface  $file   The main file to create the reference for
     * @param   int            $uid    The uid of the record that should display the linked file
     * @param   string         $field  The field of the record that should be linked with this file
     * @param   string         $table  The table of the record that should be linked with this file
     *
     * @return \TYPO3\CMS\Core\Resource\FileReference
     * @throws \LaborDigital\T3BA\Tool\Fal\FalException
     */
    public function addFileReference(
        FileInterface $file,
        int $uid,
        string $field = 'image',
        string $table = 'tt_content'
    ): FileReference
    {
        // Ignore the access checks
        $referenceUid = $this->cs()->simulator->runWithEnvironment(['asAdmin'],
            function () use ($file, $uid, $field, $table) {
                // Get the record from the database
                $record = $this->cs()->db->getQuery($table)->withWhere(['uid' => $uid])->getFirst();
                if (empty($record)) {
                    throw new FalException(
                        'Invalid table: ' . $table . ' or uid: ' . $uid . ' to create a file reference for');
                }
                
                // Make sure we can add sys_file_references everywhere
                $allowedTablesBackup = $GLOBALS['PAGES_TYPES']['default']['allowedTables'];
                ExtensionManagementUtility::allowTableOnStandardPages('sys_file_reference');
                
                try {
                    $handler = $this->cs()->dataHandler->processData([
                        'sys_file_reference' => [
                            'NEW1' => [
                                'table_local' => 'sys_file',
                                'uid_local' => $file->getProperty('uid'),
                                'tablenames' => $table,
                                'uid_foreign' => $uid,
                                'fieldname' => $field,
                                'pid' => $record['pid'],
                            ],
                        ],
                        $table => [
                            $uid => [
                                'pid' => $record['pid'],
                                $field => 'NEW1',
                            ],
                        ],
                    ]);
                } finally {
                    $GLOBALS['PAGES_TYPES']['default']['allowedTables'] = $allowedTablesBackup;
                }
                
                // Get the new id
                return reset($handler->newRelatedIDs['sys_file_reference']);
            });
        
        // Done
        return $this->getFileReference($referenceUid);
    }
    
    /**
     * Adds a file on your local file system to the FAL file system.
     * IMPORTANT: The file given as $fileSystemPath will be moved to the FAL directory, not copied!
     *
     * @param   string  $fileSystemPath  The real path to the file to import. Should always be a FILE not a FOLDER!
     * @param   string  $falPath         Defines the path where to put the file in the FAL file system.
     *                                   Non existing directories will auto-created, the default file storage is
     *                                   1(fileadmin). If the falPath ends with a slash "/" the filename will be taken
     *                                   from
     *                                   $fileSystemPath. If the falPath NOT ends with a slash, the filename is
     *                                   extracted from it
     * @param   string  $onDuplication   The behaviour on file conflicts. One of DuplicationBehavior's constants
     *
     * @return \TYPO3\CMS\Core\Resource\FileInterface
     * @see DuplicationBehavior
     */
    public function addFile(
        string $fileSystemPath,
        string $falPath,
        string $onDuplication = DuplicationBehavior::REPLACE
    ): FileInterface
    {
        // Fetch the filename
        $falPath = trim(Path::unifySlashes($falPath, '/'));
        if (substr($falPath, -1) === '/') {
            // Got a folder name as fal path -> Use basename of system path as file name
            $filename = basename($fileSystemPath);
        } else {
            // File name was set in fal path
            $filename = basename($falPath);
            $falPath = dirname($falPath);
        }
        
        return $this->mkFolder($falPath)
                    ->addFile($fileSystemPath, $filename, $onDuplication);
    }
    
    /**
     * Handles the upload of files and adds them to the FAL storage.
     *
     * @param   string  $uploadFieldName  The name of your field in the form. You can specify the
     *                                    form-name/namespace by prepending it like: namespace.fieldName
     * @param   string  $falPath          Defines the path where to put the file in the FAL file system.
     *                                    Non existing directories will auto-created, the default file storage is
     *                                    1(fileadmin). If the falPath ends with a slash "/" the filename will be taken
     *                                    from
     *                                    $fileSystemPath. If the falPath NOT ends with a slash, the filename is
     *                                    extracted from it
     * @param   array   $options          An array of possible options
     *                                    - duplicationBehavior string ("replace"): Changes the way how duplicated
     *                                    files
     *                                    are handled. One of DuplicationBehavior's constants
     *                                    - allowedExtensions string|array: A comma separated list, or an array of
     *                                    allowed file extensions. If empty
     *                                    $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['allow'] is used instead.
     *                                    Use
     *                                    "*" to allow all file types
     *                                    - deniedExtensions string|array: A comma separated list of denied file
     *                                    extensions. If empty
     *                                    $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['deny'] is tried instead.
     *                                    This will always override allowedExtensions! So you can do a wildcard for all
     *                                    allowed files and specify what files you don't want if you would like
     *                                    - maxFileSize: An integer value of bytes which define the max
     *                                    fileSize of the uploaded file. 0 means no limit.
     *
     * @return \TYPO3\CMS\Core\Resource\FileInterface|null
     * @throws \LaborDigital\T3BA\Tool\Fal\FalUploadException
     * @throws \TYPO3\CMS\Core\Resource\Exception\UploadSizeException
     */
    public function addUploadedFile(string $uploadFieldName, string $falPath, array $options = []): ?FileInterface
    {
        // Prepare options
        $options = Options::make($options, [
            'duplicationBehavior' => [
                'type' => 'string',
                'values' => [DuplicationBehavior::REPLACE, DuplicationBehavior::CANCEL, DuplicationBehavior::RENAME],
                'default' => DuplicationBehavior::REPLACE,
            ],
            'allowedExtensions' => [
                'type' => ['string', 'array'],
                'default' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['allow'],
                'preFilter' => static function ($v) {
                    return empty($v) ? [] : $v;
                },
                'filter' => static function ($v) {
                    if (is_string($v)) {
                        $v = Arrays::makeFromStringList($v);
                    }
                    
                    return array_unique(array_map('strtolower', $v));
                },
            ],
            'deniedExtensions' => [
                'type' => ['string', 'array'],
                'default' => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['deny'],
                'preFilter' => static function ($v) {
                    return empty($v) ? [] : $v;
                },
                'filter' => static function ($v) {
                    if (is_string($v)) {
                        $v = Arrays::makeFromStringList($v);
                    }
                    
                    return array_unique(array_map('strtolower', $v));
                },
            ],
            'maxFileSize' => [
                'type' => 'number',
                'default' => 0,
            ],
        ]);
        
        // Check if fieldName contains namespace
        if (strpos($uploadFieldName, '.') !== false) {
            $uploadFieldNameList = GeneralUtility::trimExplode('.', $uploadFieldName);
            $namespace = array_shift($uploadFieldNameList);
            $uploadFieldName = implode('.', $uploadFieldNameList);
            if (! isset($_FILES[$namespace])) {
                return null;
            }
        } else {
            $namespace = key($_FILES);
        }
        
        // Convert the upload array to an easier to process format
        $field = [];
        foreach ($_FILES[$namespace] as $k => $v) {
            $field[$k] = $v[$uploadFieldName];
        }
        
        // Check if an error occurred
        if (! empty($field['error'])) {
            throw new FalUploadException('An error occurred while uploading', $field['error'], 0);
        }
        
        // Validate fileSize
        if ($options['maxFileSize'] > 0 && $field['size'] > $options['maxFileSize']) {
            throw new UploadSizeException('The given file exceeds the max size of ' . $options['maxFileSize']);
        }
        
        // Validate extension
        $ext = strtolower(pathinfo($field['name'], PATHINFO_EXTENSION));
        if (
            ! in_array('*', $options['allowedExtensions'], true)
            && ! in_array($ext, $options['allowedExtensions'], true)
        ) {
            throw new FalUploadException("The file extension: $ext is not allowed!", 1);
        }
        if (in_array($ext, $options['deniedExtensions'], true)) {
            throw new FalUploadException("The file extension: $ext is not allowed!", 1);
        }
        
        // Add the file
        return $this->addFile($field['tmp_name'], $falPath . '/' . $field['name'], $options['duplicationBehavior']);
    }
    
    /**
     * Returns an object containing information for a given file, like it's size, url, mime type and similar options.
     * Image and video files also contain additional metadata like dimensions, description and platform video id's
     *
     * @param   string|int|FileReference|File|mixed  $file  Can either be the instance of a file or anything that is
     *                                                      valid as a $uid when using getFile()
     *
     * @return \LaborDigital\T3BA\Tool\Fal\FileInfo\FileInfo
     */
    public function getFileInfo($file): FileInfo
    {
        if ($file instanceof FileInfo) {
            return $file;
        }
        
        return GeneralUtility::makeInstance(FileInfo::class, $file, $this);
    }
    
    /**
     * Returns the url of a given file object
     *
     * @param   string|int|FileReference|File|mixed  $file      Can either be the instance of a file or anything that is
     *                                                          valid as a $uid when using getFile()
     * @param   bool                                 $withHash  By default all urls have a cache buster hash attached.
     *                                                          Set this to false if you don't want a cache buster
     *
     * @return  string
     */
    public function getFileUrl($file, bool $withHash = true): string
    {
        return $this->getFileInfo($file)->getUrl($withHash);
    }
    
    /**
     * This method is used to apply resizing and cropping definitions to a image file.
     * The result will be a processed file
     *
     * @param   mixed  $file     Can either be the instance of a file or anything that is valid as a $uid when using
     *                           getFile()
     * @param   array  $options  The resizing options to apply when the image is generated
     *                           - width int|string: see *1
     *                           - height int|string: see *1
     *                           - minWidth int The minimal width of the image in pixels
     *                           - minHeight int The minimal height of the image in pixels
     *                           - maxWidth int The maximal width of the image in pixels
     *                           - maxHeight int The maximal height of the image in pixels
     *                           - crop bool|string|array: True if the image should be cropped instead of stretched
     *                           Can also be the name of a cropVariant that should be rendered
     *                           Can be an array with (x,y,width,height) keys to provide a custom crop mask
     *                           - params string: Additional command line parameters for imagick
     *                           see: https://imagemagick.org/script/command-line-options.php
     *
     * *1: A numeric value, can also be a simple calculation. For further details take a look at imageResource.width:
     * https://docs.typo3.org/m/typo3/reference-typoscript/8.7/en-us/Functions/Imgresource/Index.html
     *
     * @return ProcessedFile
     */
    public function getResizedImage($file, array $options = []): ProcessedFile
    {
        $fileInfo = $this->getFileInfo($file);
        if ($fileInfo->isFileReference()) {
            $file = $fileInfo->getFileReference();
        } else {
            $file = $fileInfo->getFile();
        }
        
        // Build options
        $options = $this->applyResizedImageOptions($options);
        
        // Build crop definition if a crop
        if (is_string($options['crop'])) {
            $cropString = ($file->hasProperty('crop') && $file->getProperty('crop'))
                ? $file->getProperty('crop') : '';
            $cropArea = CropVariantCollection::create((string)$cropString)
                                             ->getCropArea($options['crop']);
            $crop = $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($file);
            $options['crop'] = $crop;
        } elseif (is_array($options['crop'])) {
            $options['crop'] = Area::createFromConfiguration($options['crop']);
        }
        
        // Apply the processing
        $processed = $this->getService(ImageService::class)->applyProcessingInstructions($file, $options);
        
        // Inject the file reference as property to use it in later processing steps
        if ($file instanceof FileReference) {
            ProcessedFileAdapter::injectProperty($processed, '@fileReference', $file);
        }
        
        // Done
        return $processed;
    }
    
    /**
     * Similar to getFileUrl() but is designed to resize and crop images on the fly.
     * Note: If the image is not found, or the editing failed the original url of the file is returned!
     *
     * @param   mixed  $file     Can either be the instance of a file or anything that is valid as a $uid when using
     *                           getFile()
     * @param   array  $options  The resizing options to apply when the image is generated
     *                           - width int|string: see *1
     *                           - height int|string: see *1
     *                           - minWidth int|string: see *1
     *                           - minHeight int|string: see *1
     *                           - maxWidth int|string: see *1
     *                           - maxHeight int|string: see *1
     *                           - crop bool|string (FALSE): True if the image should be cropped instead of stretched
     *                           Can also be the name of a cropVariant that should be rendered
     *
     * *1: A numeric value, can end a "c" to crop the image to the target width
     *
     * @return string
     */
    public function getResizedImageUrl($file, array $options = []): string
    {
        $processed = $this->getResizedImage($file, $options);
        
        $url = $processed->getPublicUrl(false);
        $url .= strpos($url, '?') === false ? '?' : '&';
        $url .= 'hash=' . md5($processed->getSha1());
        
        return FalFileUrlUtil::makeAbsoluteUrl(ltrim($url, '/'));
    }
    
    /**
     * Checks if a certain fal folder exists or not.
     *
     * @param   string  $falPath  Something like /myFolder/mySubFolder, 1:/myFolder, 2
     *
     * @return bool
     */
    public function hasFolder(string $falPath): bool
    {
        try {
            $this->getFolder($falPath);
            
            return true;
        } catch (FolderDoesNotExistException $e) {
            return false;
        }
    }
    
    /**
     * Retrieves a fal folder object from the storage and returns it.
     * Throws an exception if the folder does not exist!
     *
     * @param   string  $falPath  Something like /myFolder/mySubFolder, 1:/myFolder, 2
     *
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    public function getFolder(string $falPath): Folder
    {
        $path = $this->getFalPathArray($falPath);
        
        return $this->getResourceFactory()->getFolderObjectFromCombinedIdentifier($path['identifier']);
    }
    
    /**
     * Creates a new directory at the given path. This method handles the path recursively.
     * Folders that already exist will simply be ignored.
     *
     * @param   string  $falPath  Something like /myFolder/mySubFolder, 1:/myFolder, 2
     *
     * @return \TYPO3\CMS\Core\Resource\Folder
     */
    public function mkFolder(string $falPath): Folder
    {
        $path = $this->getFalPathArray($falPath);
        
        // Load the root folder
        $folder = $this->getResourceFactory()->getFolderObjectFromCombinedIdentifier($path['storage'] . ':/');
        
        // Traverse the path and create the directory recursively
        foreach ($path['path'] as $part) {
            if (! $folder->hasFolder($part)) {
                $folder->createFolder($part);
            }
            $folder = $folder->getSubfolder($part);
        }
        
        // Done
        return $folder;
    }
    
    /**
     * Internal helper to receive a fal (currently only folder)path and parses it to avoid common errors.
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
    protected function getFalPathArray(string $falPath): array
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
