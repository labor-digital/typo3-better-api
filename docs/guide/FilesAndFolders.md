# Files and Folders
Most of the heavy lifting for handling files in your TYPO3 installation is done by the core itself using the FAL for example.
To make working with files more convenient, BetterApi gives you some additional tools to work with files inside the FAL and 
when working with local files on the hard drive.

## FAL File Service
```LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService```

The FAL file service provides you a bunch of pre-build methods to work with the FAL programmatically. 
It handles tasks like creating folders, uploading files, retrieving files, creating new file references as well as providing 
you detailed file informations using the [FileInfo](#file-info) objects

### getFile()
This method has two modes of operation.

1. The first one is by only supplying a $uid. This uid should be a valid uid of a row in "sys_file"
    The result will be either null or an object of type "File"
2. The second mode is by supplying a $uid, $table, and $field. This will now search the ```sys_file_references``` table matching the given criteria.
The result will be either null, an array of FileReference objects or a single FileReference object
depending on the $onlyFirst parameter.

::: tip
$uid can also be given as "query," which is the case when you are using a typolink field in the TCA.
:::

::: details Arguments
- $uid Either a sys_file | uid or a uid of the record using as reference
    - NULL To select all references of with the matching $table and $field
    - The $uid field alone can handle all possible inputs like the following as well.
        - "2:myfolder/myfile.jpg" (combined identifier)
        - "23" (file UID)
        - "uploads/myfile.png" (backwards-compatibility, storage "0")
        - "file:23"
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Retrieve a sys_file by uid
$file = $falFileService->getFile(12);

// Retrieve the first sys_file_reference for a record with uid 12 on tt_content
$fileReference = $falFileService->getFile(12, "tt_content", "media");

// Retrieve ALL references for a record with uid 12 on tt_content
$fileReferences = $falFileService->getFile(12, "tt_content", "media", false);

// You can also use fal identifiers
$file = $falFileService->getFile("2:myfolder/myfile.jpg");
```

### getFileReference()
Similar to getFile() as it finds a file object in the FAL. However, this will
solely search for file references and requires a numeric id for a reference to find in the database.

::: details Arguments
- $uid The uid of the reference in the sys_file_reference table
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Retrieve a sys_file_reference by uid
$fileReference = $falFileService->getFileReference(12);
```

### addFileReference()
This method creates a new file reference. It expects to receive a FAL file instance and
some metadata to create the mapping on an external field.

::: warning
There will be no permission checks when creating the reference!
:::

::: details Arguments
- $file  The main file to create the reference for
- $uid   The uid of the record that should display the linked file
- $field The field of the record that should be linked with this file
- $table The table of the record that should be linked with this file
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Create a new reference for "media" on a page
$file = $falFileService->getFile(12);
$falFileService->addFileReference($file, 12, "media", "pages");
```

### addFile()
Adds a file on your local file system to the FAL file system.

::: warning
The file given as $fileSystemPath will be moved to the FAL directory, not copied!
:::

::: details Arguments
- $fileSystemPath The real path to the file to import. Should always be a FILE, not a FOLDER!
- $falPath Defines where to put the file in the FAL file system.
Nonexisting directories will auto-created, the default file storage is 1(fileadmin). 
If the falPath ends with a slash "/", the filename will be taken from $fileSystemPath. 
If the falPath NOT ends with a slash, the filename is extracted from it
- $onDuplication  The behaviour on file conflicts. One of DuplicationBehavior's constants
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Add a new file as /fal/dir/upload.jpg
$file = $falFileService->addFile("/var/www/html/upload.jpg", "/fal/dir/");

// Add a new file as /fal/dir/file.jpg
$file = $falFileService->addFile("/var/www/html/upload.jpg", "/fal/dir/file.jpg");
```

### addUploadedFile()
Handles the upload of files and adds them to the FAL storage.

::: details Arguments
- $uploadFieldName The name of your field in the form. You can specify the form-name/namespace by prepending it like: namespace.fieldName
- $falPath Defines the path where to put the file in the FAL file system.
Nonexisting directories will auto-created, the default file storage is 1(fileadmin). 
If the falPath ends with a slash "/", the filename will be taken from $fileSystemPath. 
If the falPath NOT ends with a slash, the filename is extracted from it
- $options An array of possible options
  - duplicationBehavior string ("replace"): Changes the way how duplicated files
  are handled. One of DuplicationBehavior's constants
  - allowedExtensions string|array: A comma separated list, or an array of allowed
  file extensions. If empty $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['allow'] 
  is used instead. Use "*" to allow all file types
  - deniedExtensions string|array: A comma separated list of denied file
  extensions. If empty $GLOBALS['TYPO3_CONF_VARS']['BE']['fileExtensions']['deny']
  is tried instead. This will always override allowedExtensions! So you can do a
  wildcard for all allowed files and specify what files you don't want if you would
  like
  - maxFileSize: An integer value of bytes which define the max
  fileSize of the uploaded file. 0 means no limit.
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Upload a file that has the name $_POST["newDocument"]
$file = $falFileService->addUploadedFile(
    "newDocument",
    '1:/your/directory/',
    [
        'allowedExtensions'   => 'doc,pdf,zip,rar,docx',
        'maxFileSize'         => 8e+6,
        'duplicationBehavior' => DuplicationBehavior::RENAME,
    ]);
```

### getFileInfo()
Returns an object containing information for a given file, like it's size, URL, mime type, and similar options.
Image and video files also contain additional metadata like dimensions, description and platform video id's

::: details Arguments
- $file Can either be the instance of a file or anything that is valid as a $uid when using getFile()
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Get file information for a file with uid 12
$info = $falFileService->getFileInfo(12);
$info->isFileReference(); // FALSE

// Get file info for a file reference on tt_content media
$ref = $falFileService->getFile(12, "tt_content", "media");
$info = $falFileService->getFileInfo($ref);
$info->isFileReference(); // TRUE
$info->isImage(); // TRUE (or FALSE, depending on your file :D)
```

### getFileUrl()
Returns the url of a given file object

::: details Arguments
- $file     Can either be the instance of a file or anything valid as a $uid when using getFile()
- $withHash By default, all URLs have a cache buster hash attached.
Set this to false if you don't want a cache buster
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

$file = $falFileService->getFile(12, "tt_content", "media");
$falFileService->getFileUrl($file); // http://example.org/fileadmin/media.jpg?hash=xxxxxxxxxxxx
```

### getResizedImage()
This method is used to apply resizing and cropping definitions to an image file.
The result will be a processed file.

::: details Arguments
- $file Can either be the instance of a file or anything that is valid as a $uid when using getFile()
- $options The resizing options to apply when the image is generated
  - width int|string: see *1
  - height int|string: see *1
  - minWidth int The minimal width of the image in pixels
  - minHeight int The minimal height of the image in pixels
  - maxWidth int The maximal width of the image in pixels
  - maxHeight int The maximal height of the image in pixels
  - crop bool|string|array: True if the image should be cropped instead of stretched
  Can also be the name of a cropVariant that should be rendered
  Can be an array with (x,y,width,height) keys to provide a custom crop mask
  - params string: Additional command line parameters for imagick
  see: https://imagemagick.org/script/command-line-options.php

*1: A numeric value, can also be a simple calculation. For further details take a look at [imageResource.width](https://docs.typo3.org/m/typo3/reference-typoscript/8.7/en-us/Functions/Imgresource/Index.html)
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Resize the given image to a width of 250px
$file = $falFileService->getFile(12, "tt_content", "media");
$resized = $falFileService->getResizedImage($file, [
    "width" => 250
]);
```

### getResizedImageUrl()
Similar to getFileUrl() but is designed to resize and crop images on the fly.
Note: If the image is not found, or the editing failed the original URL of the file is returned!

::: details Arguments
- $file Can either be the instance of a file or anything valid as a $uid when using getFile()
- $options The resizing options to apply when the image is generated
  - width int|string: see *1
  - height int|string: see *1
  - minWidth int|string: see *1
  - minHeight int|string: see *1
  - maxWidth int|string: see *1
  - maxHeight int|string: see *1
  - crop bool|string (FALSE): True if the image should be cropped instead of stretched
Can also be the name of a cropVariant that should be rendered

*1: A numeric value, can end a "c" to crop the image to the target width
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Resize the given image to a width of 250px
$file = $falFileService->getFile(12, "tt_content", "media");
$falFileService->getResizedImageUrl($file, [
    "width" => 250
]); // http://example.org/fileadmin/_processed_/xxxx.jpg?hash=xxxxxxxxxxxx
```

### hasFolder()
Checks if a certain fal folder exists or not.

::: details Arguments
- $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Check if a folder exists in mount 1 (fileadmin)
$falFileService->hasFolder("/myFolder/mySubFolder");

// Check if a folder exists in mount 2
$falFileService->hasFolder("2:/myFolder/mySubFolder");
```

### getFolder()
Retrieves a fal folder object from the storage and returns it.
Throws an exception if the folder does not exist!

::: details Arguments
- $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Gets a folder instance from mount 1 (fileadmin)
$folder = $falFileService->getFolder("/myFolder/mySubFolder");
```

### mkFolder()
Creates a new directory at the given path. This method handles the path recursively.
Folders that already exist will simply be ignored.

::: details Arguments
- $falPath Something like /myFolder/mySubFolder, 1:/myFolder, 2
:::

```php
<?php
use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use LaborDigital\Typo3BetterApi\FileAndFolder\FalFileService;
$falFileService = TypoContainer::getInstance()->get(FalFileService::class);

// Make a folder instance on mount 1 (fileadmin)
$folder = $falFileService->mkFolder("/myFolder/mySubFolder");
```

## File Info
```LaborDigital\Typo3BetterApi\FileAndFolder\FileInfo\FileInfo```

A unified file information repository for files, file references, and processed files.
You can create a new instance of this class by using [FalFileService::getFileInfo()](#getfileinfo).

### isFileReference()
Returns true if the file is handled as a "sys-file-reference" object

### isProcessed()
Returns true if the handled file is a processed file instance

### getUid()
Returns the unique id of either the file reference or the file

### getFileReferenceUid()
Returns either the uid of the handled file reference or null if the file is not a file reference

### getFileUid()
Returns the uid if the low level file object

### getHash()
Returns a cache buster string for the file

### getFileName()
Returns the base name of the current file name

### getUrl()
Returns the URL of the file handled as absolute URL

::: details Arguments
- $withHash Set this to false to disable the cache buster hash that will be added to the file URL
:::

### getOriginalUrl()
Similar to getUrl() but always returns the default URL even if the current file is a processed file instance

::: details Arguments
- $withHash Set this to false to disable the cache buster hash that will be added to the file URL
:::

### getMimeType()
Returns the mime type of the file

### getSize()
Returns the size of the handled file in bytes

### getExtension()
Returns the file extension of the handled file

### getType()
Returns the file type as they are defined in the File::FILETYPE_ constants

### isImage()
Returns true if the handled file is an image

### isVideo()
Returns the raw file instance this information object represents

### getFileReference()
Returns either the currently linked file reference or null if there is none

### getProcessedFile()
Returns either the processed file object or null if the file was not processed

### getVideoInfo()
Returns either additional information if this file is a video or null if this file is not a video

### getImageInfo()
Returns either additional information if this file is an image or null if this file is not an image

## TempFs
```LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs```

In earlier versions I used the caching framework extensively to store dynamically generated content. However it is no longer allowed to create caches while the ext_localconf and tca files are generated.
Therefore all data, which is dynamically generated by this extension is now stored in a separate temporary director

The TempFs class is part of the public API and can be used to store your own dynamically generated files. 

::: warning
It's called >**TEMP**<Fs for a reason! If you clear the system cache (red lighting) all files in the /var/tempFs/ directory will be cleared!
:::

Create a new instance by passing a unique sub directory that will be created inside /var/tempFs:

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");
```

### hasFile()
Returns true if a file exists, false if not

::: details Arguments
- $filePath The name / relative path of the file to check
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");
$fs->hasFile("test.txt"); // True if file exists, false if not
```

### getFile()
Returns the file object for the required file path

::: details Arguments
- $filePath The name / relative path of the file to retrieve
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");
$fs->getFile("test.txt"); // SplFileInfo object if the file exists on the disk
```

### getFileContent()
Returns the content of a required file.
It will automatically unpack serialized values back into their PHP values

::: details Arguments
- $filePath The name / relative path of the file to read
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");
$fs->getFileContent("test.txt");
```

### setFileContent()
Is used to dump some content into a file.
Automatically serializes non-string/numeric content before writing it as a file

::: details Arguments
- $filePath The name / relative path of the file to dump the content to
- $content  Either a string (will be dumped as string) or anything else (will be dumped as serialized value)
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");

// Writes a file with a given string content
$fs->setFileContent("test.txt", "my content");

// Writes a file with the serialized string of your array
$fs->setFileContent("test.txt", ["myKey" => "my content"]);
```

### includeFile()
Includes a file as a PHP resource

::: details Arguments
- $filePath The name of the file to include
- $once by default we include the file with include_once, if you set this to FALSE the plain include is used instead.
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\TempFs\TempFs;
$fs = new TempFs("subDir/in/var/temp/fs");

// Writes a dynamic php file to the file system and includes it
$fs->setFileContent("myPhp.php", "<?php echo \"hello world\";");
$fs->includeFile("myPhp.php"); // prints: hello world
```

### getBaseDirectoryPath()
Returns the configured base directory, either as absolute, or as relative path (relative to the typo3_better_api
root directory)

::: details Arguments
- $relative Set this to true if you want to retrieve the relative path based on the better api extension.
Useful for compiling typoscript or flexform files
:::

### flush()
Completely removes the whole directory and all files in it


## Permissions
```LaborDigital\Typo3BetterApi\FileAndFolder\Permissions```

### setFilePermissions()
This helper works quite similar like GeneralUtility::fixPermissions() but without depending
on the existence of the PATH_site constant. This method is built to handle errors silently. 
The result of the method shows if there was an error (FALSE) or not (TRUE)

::: details Arguments
- $filename The absolute path of the file to set the permissions for
- $mode     Optionally set a permission set like 0644 -> Make sure to use strings
- $group    Optionally set a group to set, otherwise the parent folder"s group will be used.
:::

```php
<?php
use LaborDigital\Typo3BetterApi\FileAndFolder\Permissions;

// Sets the UNIX permissions of a file to 777.
Permissions::setFilePermissions ("/absolute/file/path.txt", "0777");
```