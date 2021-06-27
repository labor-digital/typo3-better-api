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
 * Last modified: 2020.03.23 at 20:58
 */

namespace LaborDigital\T3ba\TypoContext;

use Composer\Autoload\ClassLoader;
use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Exception\T3baException;
use LaborDigital\T3ba\Tool\Database\DbService;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use LaborDigital\T3ba\Tool\TypoContext\FacetInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\FileSystem\Fs;
use Neunerlei\Inflection\Inflector;
use Neunerlei\PathUtil\Path;
use ReflectionClass;
use RuntimeException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\DataHandling\SlugHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Repository of path information and  path resolving functions
 */
class PathFacet implements FacetInterface
{
    use ContainerAwareTrait;
    
    /**
     * This property stores the vendor path after it was resolved in getVendorPath
     *
     * @var string
     */
    protected $vendorPath;
    
    /**
     * @var TypoContext
     */
    protected $context;
    
    /**
     * PathFacet constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext  $context
     */
    public function __construct(TypoContext $context)
    {
        $this->context = $context;
    }
    
    /**
     * @inheritDoc
     */
    public static function getIdentifier(): string
    {
        return 'path';
    }
    
    /**
     * Returns the absolute filepath of the "vendor" directory of typo3 was installed using composer.
     * Will return an empty string if the composer classloader is not yet loaded / not installed
     *
     * @return string
     */
    public function getVendorPath(): string
    {
        // Resolve the path using our constant
        if (defined('BETTER_API_TYPO3_VENDOR_PATH')) {
            return Path::unifyPath(BETTER_API_TYPO3_VENDOR_PATH);
        }
        
        // Check if we know the path already
        if (isset($this->vendorPath)) {
            return $this->vendorPath;
        }
        
        // Check if we have the autoloader
        if (! class_exists(ClassLoader::class)) {
            return '';
        }
        
        // Read the directory of the classloader
        $file = (new ReflectionClass(ClassLoader::class))->getFileName();
        
        return $this->vendorPath = Path::unifyPath(dirname($file, 2));
    }
    
    /**
     * Helper to retrieve the filepath of an extension.
     *
     * @param   string       $extensionKey  The extension key
     * @param   string|null  $script        Will be appended to the created path
     *
     * @return string
     */
    public function getExtensionPath(string $extensionKey, ?string $script = null): string
    {
        return Path::unifyPath(ExtensionManagementUtility::extPath($extensionKey, $script ?? ''));
    }
    
    /**
     * The public web folder where index.php (= the frontend application) is put. This is equal to the legacy constant
     * PATH_site, without the trailing slash. For non-composer installations, the project path = the public path.
     *
     * @return string
     */
    public function getPublicPath(): string
    {
        return Path::unifyPath(Environment::getPublicPath());
    }
    
    /**
     * The folder where all global (= installation-wide) configuration like
     * - LocalConfiguration.php,
     * - AdditionalConfiguration.php, and
     * - PackageStates.php
     * is put.
     * This folder usually has to be writable for TYPO3 in order to work.
     *
     * When project path = public path, then this folder is usually typo3conf/, otherwise it's set to
     * $project_path/config.
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        return Path::unifyPath(Environment::getConfigPath());
    }
    
    /**
     * Returns the default template path for a given extension, optionally including a plugin namespace.
     * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
     * real filesystem path
     *
     * @param   string       $extKey      The extkey for the extension to find the path for
     * @param   string|null  $pluginName  Optional plugin name to append to the built path
     *
     * @return string
     */
    public function getTemplatePath(string $extKey, ?string $pluginName = null): string
    {
        return 'EXT:' . strtolower($extKey) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR .
               'Private' . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR .
               (empty($pluginName) ? '' : Inflector::toCamelCase($pluginName) . DIRECTORY_SEPARATOR);
    }
    
    /**
     * Returns the default partial path for a given extension, optionally including a plugin namespace.
     * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
     * real filesystem path
     *
     * @param   string       $extKey      The extkey for the extension to find the path for
     * @param   string|null  $pluginName  Optional plugin name to append to the built path
     *
     * @return string
     */
    public function getPartialPath(string $extKey, ?string $pluginName = null): string
    {
        return 'EXT:' . strtolower($extKey) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR .
               'Private' . DIRECTORY_SEPARATOR . 'Partials' . DIRECTORY_SEPARATOR .
               (empty($pluginName) ? '' : Inflector::toCamelCase($pluginName) . DIRECTORY_SEPARATOR);
    }
    
    /**
     * Returns the default layout path for a given extension, optionally including a plugin namespace.
     * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
     * real filesystem path
     *
     * @param   string       $extKey      The extkey for the extension to find the path for
     * @param   string|null  $pluginName  Optional plugin name to append to the built path
     *
     * @return string
     */
    public function getLayoutPath(string $extKey, ?string $pluginName = null): string
    {
        return 'EXT:' . strtolower($extKey) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR .
               'Private' . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR .
               (empty($pluginName) ? '' : Inflector::toCamelCase($pluginName) . DIRECTORY_SEPARATOR);
    }
    
    /**
     * Tries to find the extension icon for the extension with the given ext key.
     * It will either return the path to the icon or an empty string
     *
     * @param   string  $extKey    The extension key to find the icon path for
     * @param   bool    $absolute  By default the path will start with EXT:$extKey/...
     *                             If you set this to true the absolute, real path will be returned instead
     *
     * @return string
     */
    public function getExtensionIconPath(string $extKey, bool $absolute = false): string
    {
        $extensionPath = $this->getExtensionPath($extKey);
        $iconPath = ExtensionManagementUtility::getExtensionIcon($extensionPath);
        
        if (empty($iconPath)) {
            return '';
        }
        if ($absolute) {
            return Path::join($extensionPath, $iconPath);
        }
        
        return Path::join('EXT:' . $extKey, $iconPath);
    }
    
    /**
     * Returns the path to the directory where dynamic data may be stored
     *
     * @return string
     */
    public function getVarPath(): string
    {
        return Environment::getVarPath() . DIRECTORY_SEPARATOR;
    }
    
    /**
     * Receives a typo3 formatted string like: LLL:EXT:ext_key/something...
     * And converts it into an absolute path. Of course you may use paths that
     * start only like EXT:ext_key without the language selector
     *
     * @param   string  $typoPath  The path to parse
     *
     * @return string
     */
    public function typoPathToRealPath(string $typoPath): string
    {
        $file = Path::unifySlashes($typoPath);
        
        // Prepare input string
        if (stripos($file, 'file:') === 0) {
            $file = substr($file, 5);
        }
        $prefix = substr($file, 0, 4);
        $isLangFile = strtolower($prefix) === 'lll:';
        if ($isLangFile) {
            $prefix = substr($file, 4, 4);
        }
        $isExtFile = strtolower($prefix) === 'ext:';
        
        // Nothing to do
        if (! $isLangFile && ! $isExtFile) {
            return $file;
        }
        
        // Remove primary prefix -> EXT: or LLL:
        $file = substr($file, 4);
        
        // Return non ext langFile
        if (! $isExtFile && $isLangFile) {
            return $file;
        }
        
        // Remove secondary prefix -> EXT:
        if ($isExtFile && $isLangFile) {
            $file = substr($file, 4);
        }
        
        // Get extKey
        $pos = strpos($file, DIRECTORY_SEPARATOR);
        $extKey = substr($file, 0, $pos);
        $file = substr($file, $pos + 1);
        
        // Resolve directory
        $dir = $this->getExtensionPath($extKey);
        
        return Path::unifyPath($dir) . $file;
    }
    
    /**
     * This method does the exact opposite of typoPathToRealPath().
     * It takes a fully qualified filename and converts it into an relative path starting with EXT:...
     *
     * Note: Your path should be inside a loaded extension's root directory. Otherwise the method will fail.
     *
     * @param   string  $path
     *
     * @return string
     * @throws \JsonException
     * @throws \LaborDigital\T3ba\Core\Exception\T3baException
     */
    public function realPathToTypoExt(string $path): string
    {
        $path = Path::unifySlashes(trim($path));
        
        // Ignore if we have already an ext: prefix
        if (stripos($path, 'ext:') === 0) {
            return $path;
        }
        $p = PathUtility::stripPathSitePrefix($path);
        
        // Could we resolve the path inside of ext?
        if (stripos($p, 'typo3conf' . DIRECTORY_SEPARATOR . 'ext' . DIRECTORY_SEPARATOR) !== 0) {
            // Try to find find a part inside the ext directory by looking for every chain member
            $stripPath = [];
            foreach (explode(DIRECTORY_SEPARATOR, $path) as $extKey) {
                $stripPath[] = $extKey;
                if (empty($extKey)) {
                    continue;
                }
                
                // Check current chain member
                if (ExtensionManagementUtility::isLoaded($extKey)) {
                    $path = 'EXT:' . $extKey . str_replace(implode(DIRECTORY_SEPARATOR, $stripPath), '', $path);
                    $realPath = $this->typoPathToRealPath($path);
                    if (! file_exists($realPath)) {
                        continue;
                    }
                    
                    return $path;
                }
                
                // Check if the path has a composer file we can use to find the ext key
                $composerJsonPath = implode(DIRECTORY_SEPARATOR, $stripPath) . DIRECTORY_SEPARATOR . 'composer.json';
                if (file_exists($composerJsonPath)) {
                    $compJson = json_decode(Fs::readFile($composerJsonPath), true, 512, JSON_THROW_ON_ERROR);
                    if (! isset($compJson['name'])) {
                        continue;
                    }
                    $extKey = Inflector::toUnderscore(preg_replace("/^.*?\//", '', $compJson['name']));
                    if (ExtensionManagementUtility::isLoaded($extKey)) {
                        return 'EXT:' . $extKey . str_replace(implode(DIRECTORY_SEPARATOR, $stripPath), '', $path);
                    }
                }
            }
            
            // Try to find the path by looking
            throw new T3baException('Could not resolve path: ' . $path . ' to a relative EXT: path!');
        }
        
        // Looking inside the ext directory
        $path = substr($p, 14);
        
        return 'EXT:' . $path;
    }
    
    /**
     * This helper allows you to generate a slug programmatically like an author would in the TYPO3
     * backend. There are two different options when you generate a slug. Either you can generate a
     * slug for an existing record or you can generate a slug for a record that is not currently stored in the
     * database.
     *
     * Please note, that the slug generation is tightly coupled with the TCA configuration of a certain table field.
     * Because of that you have to specify the the name of the table and the field you want to generate the slug for.
     *
     * To generate the slug for an existing record on the given table you can simply pass the UID of the record
     * as first argument to this method.
     *
     * If you want to generate a slug for a not-existing record you can pass an array as first parameter.
     * The given array should hold a mapping of all fields that are referenced in your slug's TCA generator
     * configuration. The resulting string will match the rules you specified in the TCA
     *
     * @param   string|int|array  $recordOrUid  Either the uid of an existing record or a mapping of fields that are
     *                                          used to generate a slug when the record is not yet in the database
     * @param   string            $tableName    The full name of the database table that holds the slug field
     * @param   string            $fieldName    The name of the slug field to read the configuration from
     *
     * @return string
     */
    public function getSlugFor($recordOrUid, string $tableName, string $fieldName): string
    {
        $tableName = NamingUtil::resolveTableName($tableName);
        
        // Try to read the field configuration from the TCA
        $languageField = $GLOBALS['TCA'][$tableName]['ctrl']['languageField'] ?? null;
        $fieldConfig = $GLOBALS['TCA'][$tableName]['columns'][$fieldName]['config'] ?? [];
        if (empty($fieldConfig)) {
            throw new RuntimeException(
                'No valid field configuration for table ' . $tableName . ' field name ' . $fieldName . ' found.',
                1535379534
            );
        }
        
        // Resolve the record
        $record = [];
        if (is_numeric($recordOrUid)) {
            $record = $this->getService(DbService::class)->getQuery($tableName)
                           ->withWhere(['uid' => $recordOrUid])->getFirst();
        } elseif (is_array($record)) {
            $record = $recordOrUid;
        }
        
        // Inject language if required
        if (! empty($languageField) && ! isset($record[$languageField])) {
            $record[$languageField] = $this->context->language()->getCurrentFrontendLanguage()->getLanguageId();
        }
        
        // Create the slug using the slug helper
        $slugHelper = $this->makeInstance(SlugHelper::class, [$tableName, $fieldName, $fieldConfig]);
        
        return $slugHelper->generate($record, empty($record['pid']) ? -1 : $record['pid']);
    }
}
