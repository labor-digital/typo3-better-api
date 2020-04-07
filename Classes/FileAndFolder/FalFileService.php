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
 * Last modified: 2020.03.19 at 01:47
 */

namespace LaborDigital\Typo3BetterApi\FileAndFolder;

use Exception;
use InvalidArgumentException;
use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters\ProcessedFileAdapter;
use LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\FileInfo;
use LaborDigital\Typo3BetterApi\LazyLoading\LazyLoadingTrait;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use Neunerlei\PathUtil\Path;
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

/**
 * Class FalFileService
 * @package LaborDigital\Typo3BetterApi\FileAndFolder
 *
 * @property ImageService    $ImageService
 * @property ResourceFactory $ResourceFactory
 * @property FileRepository  $FileRepository
 */
class FalFileService implements SingletonInterface {
	use CommonServiceLocatorTrait;
	use LazyLoadingTrait;
	
	/**
	 * FalFileService constructor.
	 */
	public function __construct() {
		$this->addToServiceMap([
			"ResourceFactory" => ResourceFactory::class,
			"FileRepository"  => FileRepository::class,
			"ImageService"    => ImageService::class,
		]);
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
	 * @param int|string|null $uid       Either a sys_file | uid or a uid of the record using as reference
	 *                                   NULL To select all references of with the matching $table and $field
	 *                                   The $uid field alone can handle all strange inputs like the following as well.
	 *                                   - "2:myfolder/myfile.jpg" (combined identifier)
	 *                                   - "23" (file UID)
	 *                                   - "uploads/myfile.png" (backwards-compatibility, storage "0")
	 *                                   - "file:23"
	 *
	 * @param string          $table     The table to use as reference
	 * @param string          $field     The field to use as reference
	 * @param bool            $onlyFirst If true only the first result in an array of FileReferences will be returned
	 *
	 * @return null|array|\TYPO3\CMS\Core\Resource\File|\TYPO3\CMS\Core\Resource\FileReference|\TYPO3\CMS\Core\Resource\FileReference[]
	 */
	public function getFile($uid, string $table = "", string $field = "", bool $onlyFirst = TRUE) {
		// Check how we select
		$useUidOnly = empty($table) || empty($field);
		if ($useUidOnly && $uid === NULL) {
			throw new InvalidArgumentException(
				"\$uid can\"t be null if neither a \$table nor a \$field are defined");
		}
		
		try {
			// Read the strange string identifiers
			if (is_string($uid) && !is_numeric($uid) || is_numeric($uid) && $useUidOnly) {
				// Prepare identifier
				$identifier = $uid;
				
				// Check if we got a Pseudo Link|Label combination...
				// Oh gosh typo3 is so weired...
				if (strpos($identifier, "%") !== FALSE && strpos($identifier, "|") !== FALSE) {
					$identifier = explode("|", $identifier);
					$identifier = reset($identifier);
					// Crack strange multi-encodings
					for ($i = 0; $i < 25; $i++) {
						$identifier = rawurldecode($identifier);
						if (strpos($identifier, "%") === FALSE) break;
					}
				} // Read query like uid
				else if (strpos($identifier, "=") !== FALSE) {
					$params = parse_url($uid);
					if (!isset($params["query"])) throw new Exception("");
					parse_str($params["query"], $params);
					if (!isset($params["uid"])) throw new Exception("");
					$identifier = $params["uid"];
				} // Parse path
				else if (stripos($identifier, "/") !== FALSE) {
					$path = $this->getFalPathArray($identifier);
					$identifier = $path["identifier"];
				}
				
				// Check if we got a file
				$file = $this->ResourceFactory->retrieveFileOrFolderObject($identifier);
				if ($file instanceof File) return $file;
				return NULL;
			}
			
			$file = $this->FileRepository->findByRelation($table, $field, $uid);
			
			if (!empty($file)) $file = $onlyFirst ? reset($file) : $file;
			else $file = NULL;
			
			// Done
			return $file;
		} catch (Exception $e) {
			return NULL;
		}
	}
	
	/**
	 * Similar to getFile() as it finds a file object in the FAL. However this will
	 * solely search for file references and requires a numeric id for a reference to find in the database.
	 *
	 * @param int $uid The uid of the reference in the sys_file_reference table
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileReference
	 */
	public function getFileReference(int $uid): FileReference {
		return $this->ResourceFactory->getFileReferenceObject($uid);
	}
	
	/**
	 * This method creates a new file reference. It expects to receive a FAL file instance and
	 * some metadata to create the mapping on an external field.
	 *
	 * IMPORTANT: There will be no permission checks when creating the reference!
	 *
	 * @param FileInterface $file  The main file to create the reference for
	 * @param int           $uid   The uid of the record that should display the linked file
	 * @param string        $field The field of the record that should be linked with this file
	 * @param string        $table The table of the record that should be linked with this file
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileReference
	 */
	public function addFileReference(FileInterface $file, int $uid, string $field = "image", string $table = "tt_content"): FileReference {
		
		// Ignore the access checks
		$referenceUid = $this->Simulator->runAsAdmin(function () use ($file, $uid, $field, $table) {
			// Get the record from the database
			$record = $this->Db->getQuery($table)->withWhere(["uid" => $uid])->getFirst();
			if (empty($record))
				throw new FalFileServiceException("Invalid table: " . $table . " or uid: " . $uid . " to create a file reference for");
			
			// Create the data to be added for the reference
			$data = [
				"sys_file_reference" => [
					"NEW1" => [
						"table_local" => "sys_file",
						"uid_local"   => $file->getProperty("uid"),
						"tablenames"  => $table,
						"uid_foreign" => $uid,
						"fieldname"   => $field,
						"pid"         => $record["pid"],
					],
				],
				$table               => [
					$uid => [
						"pid"  => $record["pid"],
						$field => "NEW1",
					],
				],
			];
			
			// Make sure we can add sys_file_references everywhere
			$allowedTablesBackup = $GLOBALS['PAGES_TYPES']['default']['allowedTables'];
			ExtensionManagementUtility::allowTableOnStandardPages("sys_file_reference");
			
			// Execute the data handler
			$dataHandler = $this->DataHandler;
			$dataHandler->start($data, []);
			$dataHandler->process_datamap();
			
			// Restore the backup
			$GLOBALS['PAGES_TYPES']['default']['allowedTables'] = $allowedTablesBackup;
			
			// Check for errors
			if (count($dataHandler->errorLog) !== 0)
				throw new FalFileServiceException(
					"Error while creating file reference in table: " . $table . " with uid: " . $uid . " Errors were: " .
					implode(PHP_EOL, $dataHandler->errorLog));
			
			// Get the new id
			return reset($dataHandler->newRelatedIDs["sys_file_reference"]);
		});
		
		// Done
		return $this->getFileReference($referenceUid);
	}
	
	/**
	 * Adds a file on your local file system to the FAL file system.
	 * IMPORTANT: The file given as $fileSystemPath will be moved to the FAL directory, not copied!
	 *
	 * @param string $fileSystemPath The real path to the file to import. Should always be a FILE not a FOLDER!
	 * @param string $falPath        Defines the path where to put the file in the FAL file system.
	 *                               Non existing directories will auto-created, the default file storage is
	 *                               1(fileadmin). If the falPath ends with a slash "/" the filename will be taken from
	 *                               $fileSystemPath. If the falPath NOT ends with a slash, the filename is extracted
	 *                               from it
	 * @param string $onDuplication  The behaviour on file conflicts. One of DuplicationBehavior's constants
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileInterface
	 * @see DuplicationBehavior
	 */
	public function addFile(string $fileSystemPath, string $falPath, string $onDuplication = DuplicationBehavior::REPLACE): FileInterface {
		// Fetch the filename
		$falPath = trim(Path::unifySlashes($falPath, "/"));
		if (substr($falPath, -1) === "/") {
			// Got a folder name as fal path -> Use basename of system path as file name
			$filename = basename($fileSystemPath);
		} else {
			// File name was set in fal path
			$filename = basename($falPath);
			$falPath = dirname($falPath);
		}
		
		// Make sure the folder exists
		$folder = $this->mkFolder($falPath);
		
		// Add the file
		return $folder->addFile($fileSystemPath, $filename, $onDuplication);
	}
	
	/**
	 * Handles the upload of files and adds them to the FAL storage.
	 *
	 * @param string $uploadFieldName The name of your field in the form. You can specify the
	 *                                form-name/namespace by prepending it like: namespace.fieldName
	 * @param string $falPath         Defines the path where to put the file in the FAL file system.
	 *                                Non existing directories will auto-created, the default file storage is
	 *                                1(fileadmin). If the falPath ends with a slash "/" the filename will be taken
	 *                                from
	 *                                $fileSystemPath. If the falPath NOT ends with a slash, the filename is extracted
	 *                                from it
	 * @param array  $options         An array of possible options
	 *                                - duplicationBehavior string ("replace"): Changes the way how duplicated files
	 *                                are handled. One of DuplicationBehavior's constants
	 *                                - allowedExtensions string|array: A comma separated list, or an array of allowed
	 *                                file extensions. If empty
	 *                                $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['allow'] is used instead. Use
	 *                                "*" to allow all file types
	 *                                - deniedExtensions string|array: A comma separated list of denied file
	 *                                extensions. If empty $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['deny']
	 *                                is tried instead. This will always override allowedExtensions! So you can do a
	 *                                wildcard for all allowed files and specify what files you don't want if you would
	 *                                like
	 *                                - maxFileSize: An integer value of bytes which define the max
	 *                                fileSize of the uploaded file. 0 means no limit.
	 *
	 * @return \TYPO3\CMS\Core\Resource\FileInterface|null
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileUploadException
	 * @throws \TYPO3\CMS\Core\Resource\Exception\UploadSizeException
	 */
	public function addUploadedFile(string $uploadFieldName, string $falPath, array $options = []): ?FileInterface {
		// Prepare options
		$options = Options::make($options, [
			'duplicationBehavior' => [
				"type"    => "string",
				"values"  => [DuplicationBehavior::REPLACE, DuplicationBehavior::CANCEL, DuplicationBehavior::RENAME],
				"default" => DuplicationBehavior::REPLACE,
			],
			'allowedExtensions'   => [
				"type"      => ["string", "array"],
				"default"   => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['allow'],
				"preFilter" => function ($v) {
					return empty($v) ? [] : $v;
				},
				"filter"    => function ($v) {
					if (is_string($v)) $v = Arrays::makeFromStringList($v);
					return array_unique(array_map("strtolower", $v));
				},
			],
			'deniedExtensions'    => [
				"type"      => ["string", "array"],
				"default"   => $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['deny'],
				"preFilter" => function ($v) {
					return empty($v) ? [] : $v;
				},
				"filter"    => function ($v) {
					if (is_string($v)) $v = Arrays::makeFromStringList($v);
					return array_unique(array_map("strtolower", $v));
				},
			],
			'maxFileSize'         => [
				"type"    => "number",
				"default" => 0,
			],
		]);
		
		// Check if fieldName contains namespace
		if (strpos($uploadFieldName, '.') !== FALSE) {
			$uploadFieldName = GeneralUtility::trimExplode('.', $uploadFieldName);
			$namespace = array_shift($uploadFieldName);
			$uploadFieldName = implode('.', $uploadFieldName);
			if (!isset($_FILES[$namespace])) return NULL;
		} else {
			$namespace = key($_FILES);
		}
		
		// Convert the upload array to an easier to process format
		$field = [];
		foreach ($_FILES[$namespace] as $k => $v)
			$field[$k] = $v[$uploadFieldName];
		
		// Check if an error occurred
		if (!empty($field['error']))
			throw new FalFileUploadException('An error occurred while uploading', $field['error'], 0);
		
		// Validate fileSize
		if ($options['maxFileSize'] > 0 && $field['size'] > $options['maxFileSize'])
			throw new UploadSizeException('The given file exceeds the max size of ' . $options['maxFileSize']);
		
		// Validate extension
		$ext = strtolower(pathinfo($field['name'], PATHINFO_EXTENSION));
		if (!in_array("*", $options["allowedExtensions"]))
			if (!in_array($ext, $options["allowedExtensions"]))
				throw new FalFileUploadException("The file extension: $ext is not allowed!", 1);
		if (in_array($ext, $options["deniedExtensions"]))
			throw new FalFileUploadException("The file extension: $ext is not allowed!", 1);
		
		// Add the file
		return $this->addFile($field["tmp_name"], $falPath . "/" . $field["name"], $options["duplicationBehavior"]);
	}
	
	/**
	 * Returns an array containing information for a given file, like it's size, url, mime type and similar options.
	 * Image files also contain additional metadata like dimensions or alt attributes
	 *
	 * @param string|int|FileReference|File|mixed $file Can either be the instance of a file or anything that is
	 *                                                  valid as a $uid when using getFile()
	 *
	 * @return array
	 *
	 * @throws \LaborDigital\Typo3BetterApi\FileAndFolder\FalFileServiceException
	 * @deprecated use getFileInfo() instead!
	 */
	public function getFileInformation($file) {
		
		// Build legacy array
		$fileInfo = $this->getFileInfo($file);
		$info = [
			"isReference" => $fileInfo->isFileReference(),
			"id"          => $fileInfo->getUid(),
			"referenceId" => $fileInfo->getFileReferenceUid(),
			"isProcessed" => $fileInfo->isProcessed(),
			"fileId"      => $fileInfo->getFileUid(),
			"hash"        => $fileInfo->getHash(),
			"url"         => $fileInfo->getUrl(),
			"originalUrl" => $fileInfo->getOriginalUrl(),
			"mime"        => $fileInfo->getMimeType(),
			"size"        => $fileInfo->getSize(),
			"ext"         => $fileInfo->getExtension(),
			"image"       => NULL,
		];
		
		// Handle video information
		if ($fileInfo->isVideo() && $fileInfo->getVideoInfo()->isYouTube())
			$info["youtubeId"] = $fileInfo->getVideoInfo()->getVideoId();
		if (!$fileInfo->isImage()) return $info;
		
		// Build legacy image info
		$imageInfo = $fileInfo->getImageInfo();
		$image = [
			"alt"       => $imageInfo->getAlt(),
			"title"     => $imageInfo->getTitle(),
			"desc"      => $imageInfo->getDescription(),
			"width"     => $imageInfo->getWidth(),
			"height"    => $imageInfo->getHeight(),
			"alignment" => $imageInfo->getImageAlignment(),
			"variants"  => [],
		];
		
		// Build legacy image variants
		foreach ($imageInfo->getCropVariants() as $k => $conf)
			$image["variants"][$k] = $imageInfo->getCroppedUrl($k);
		
		// Done
		$info["image"] = $image;
		return $info;
	}
	
	/**
	 * Returns an object containing information for a given file, like it's size, url, mime type and similar options.
	 * Image and video files also contain additional metadata like dimensions, description and platform video id's
	 *
	 * @param string|int|FileReference|File|mixed $file Can either be the instance of a file or anything that is
	 *                                                  valid as a $uid when using getFile()
	 *
	 * @return \LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\FileInfo
	 */
	public function getFileInfo($file): FileInfo {
		return $this->getInstanceOf(FileInfo::class, [$file, $this, $this->lazyLoading]);
	}
	
	/**
	 * Returns the url of a given file object
	 *
	 * @param string|int|FileReference|File|mixed $file     Can either be the instance of a file or anything that is
	 *                                                      valid as a $uid when using getFile()
	 * @param bool                                $withHash By default all urls have a cache buster hash attached.
	 *                                                      Set this to false if you don't want a cache buster
	 *
	 * @return  string
	 */
	public function getFileUrl($file, bool $withHash = TRUE): string {
		return $this->getFileInfo($file)->getUrl($withHash);
	}
	
	/**
	 * This method is used to apply resizing and cropping definitions to a image file.
	 * The result will be a processed file
	 *
	 * @param mixed $file     Can either be the instance of a file or anything that is valid as a $uid when using
	 *                        getFile()
	 * @param array $options  The resizing options to apply when the image is generated
	 *                        - width int|string: see *1
	 *                        - height int|string: see *1
	 *                        - minWidth int|string: see *1
	 *                        - minHeight int|string: see *1
	 *                        - maxWidth int|string: see *1
	 *                        - maxHeight int|string: see *1
	 *                        - crop bool|string (FALSE): True if the image should be cropped instead of stretched
	 *                        Can also be the name of a cropVariant that should be rendered
	 *
	 * *1: A numeric value, can end a "c" to crop the image to the target width
	 *
	 * @return ProcessedFile
	 */
	public function getResizedImage($file, array $options = []): ProcessedFile {
		$fileInfo = $this->getFileInfo($file);
		if ($fileInfo->isFileReference()) $file = $fileInfo->getFileReference();
		else $file = $fileInfo->getFile();
		
		// Prepare image processing options
		$def = [
			"type"    => ["number", "null", "string"],
			"default" => NULL,
			"filter"  => function ($v) {
				if (!is_null($v)) return (string)$v;
				return NULL;
			},
		];
		$options = Options::make($options, [
			"width"     => $def,
			"minWidth"  => $def,
			"maxWidth"  => $def,
			"height"    => $def,
			"minHeight" => $def,
			"maxHeight" => $def,
			"crop"      => [
				"type"    => ["bool", "string"],
				"default" => FALSE,
			],
		]);
		$options = array_filter($options, function ($v) {
			return !is_null($v);
		});
		
		// Build crop definition if a crop
		if (is_string($options["crop"])) {
			$cropString = ($file->hasProperty('crop') && $file->getProperty('crop')) ? $file->getProperty('crop') : "";
			$cropVariantCollection = CropVariantCollection::create((string)$cropString);
			$cropArea = $cropVariantCollection->getCropArea($options["crop"]);
			$crop = $cropArea->isEmpty() ? FALSE : $cropArea->makeAbsoluteBasedOnFile($file);
			$options["crop"] = $crop;
		}
		
		// Apply the processing
		$processed = $this->ImageService->applyProcessingInstructions($file, $options);
		
		// Inject the file reference as property to use it in later processing steps
		if ($file instanceof FileReference) ProcessedFileAdapter::injectProperty($processed, "@fileReference", $file);
		
		// Done
		return $processed;
	}
	
	/**
	 * Similar to getFileUrl() but is designed to resize and crop images on the fly.
	 * Note: If the image is not found, or the editing failed the original url of the file is returned!
	 *
	 * @param mixed $file     Can either be the instance of a file or anything that is valid as a $uid when using
	 *                        getFile()
	 * @param array $options  The resizing options to apply when the image is generated
	 *                        - width int|string: see *1
	 *                        - height int|string: see *1
	 *                        - minWidth int|string: see *1
	 *                        - minHeight int|string: see *1
	 *                        - maxWidth int|string: see *1
	 *                        - maxHeight int|string: see *1
	 *                        - crop bool|string (FALSE): True if the image should be cropped instead of stretched
	 *                        Can also be the name of a cropVariant that should be rendered
	 *
	 * *1: A numeric value, can end a "c" to crop the image to the target width
	 *
	 * @return string
	 */
	public function getResizedImageUrl($file, array $options = []): string {
		$processed = $this->getResizedImage($file, $options);
		return $this->Links->getHost() . "/" . $processed->getPublicUrl(FALSE) . "?hash=" . md5($processed->getSha1());
	}
	
	/**
	 * Checks if a certain fal folder exists or not.
	 *
	 * @param string $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
	 *
	 * @return bool
	 */
	public function hasFolder(string $falPath): bool {
		try {
			$this->getFolder($falPath);
			return TRUE;
		} catch (FolderDoesNotExistException $e) {
			return FALSE;
		}
	}
	
	/**
	 * Retrieves a fal folder object from the storage and returns it.
	 * Throws an exception if the folder does not exist!
	 *
	 * @param string $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
	 *
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 * @throws \TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException
	 */
	public function getFolder(string $falPath): Folder {
		$path = $this->getFalPathArray($falPath);
		return $this->ResourceFactory->getFolderObjectFromCombinedIdentifier($path["identifier"]);
	}
	
	/**
	 * Creates a new directory at the given path. This method handles the path recursively.
	 * Folders that already exist will simply be ignored.
	 *
	 * @param string $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
	 *
	 * @return \TYPO3\CMS\Core\Resource\Folder
	 */
	public function mkFolder(string $falPath): Folder {
		$path = $this->getFalPathArray($falPath);
		
		// Load the root folder
		$folder = $this->ResourceFactory->getFolderObjectFromCombinedIdentifier($path["storage"] . ":/");
		
		// Traverse the path and create the directory recursively
		foreach ($path["path"] as $part) {
			if (!$folder->hasFolder($part)) $folder->createFolder($part);
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
	 * @param string $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
	 *
	 * @return array
	 */
	protected function getFalPathArray(string $falPath): array {
		$falPath = trim(trim($falPath, "\\/ "));
		$falPath = Path::unifySlashes($falPath, "/");
		$parts = explode(":", $falPath);
		if (count($parts) === 1 && is_numeric($parts[0])) $parts[] = "";
		$storageId = count($parts) > 1 && is_numeric($parts[0]) ? (int)array_shift($parts) : 1;
		$remainingPathParts = array_filter(explode("/", implode(":", $parts)));
		if (empty($remainingPathParts)) $remainingPathParts[] = "";
		
		// Make sure to remove fileadmin from path when using the storage with id 1
		if ($storageId === 1 && $remainingPathParts[0] === "fileadmin") array_shift($remainingPathParts);
		
		// Done
		return [
			"storage"    => $storageId,
			"path"       => $remainingPathParts,
			"identifier" => $storageId . ":/" . implode("/", $remainingPathParts),
		];
	}
}