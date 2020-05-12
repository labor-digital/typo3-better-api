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
 * Last modified: 2020.03.19 at 01:15
 */

namespace LaborDigital\Typo3BetterApi\TypoContext\Aspect;


use LaborDigital\Typo3BetterApi\TypoContext\Facet\PathFacet;
use TYPO3\CMS\Core\Context\AspectInterface;

/**
 * Class PathAspect
 * @package    LaborDigital\Typo3BetterApi\TypoContext\Aspect
 * @deprecated will be removed in v10 -> Use PathFacet instead
 */
class PathAspect implements AspectInterface {
	use AutomaticAspectGetTrait;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\Facet\PathFacet
	 */
	protected $facet;
	
	/**
	 * PathAspect constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\Facet\PathFacet $facet
	 */
	public function __construct(PathFacet $facet) {
		$this->facet = $facet;
	}
	
	/**
	 * @inheritDoc
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function get(string $name) {
		if ($name === "FACET") return $this->facet;
		return $this->handleGet($name);
	}
	
	/**
	 * Returns the absolute filepath of the "vendor" directory of typo3 was installed using composer.
	 * Will return an empty string if the composer classloader is not yet loaded / not installed
	 * @return string
	 * @throws \ReflectionException
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getVendorPath(): string {
		return $this->facet->getVendorPath();
	}
	
	/**
	 * Helper to retrieve the filepath of an extension.
	 *
	 * @param string $extensionKey The extension key
	 * @param string $script       Will be appended to the created path
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getExtensionPath($extensionKey, $script = ""): string {
		return $this->facet->getExtensionPath($extensionKey, $script);
	}
	
	/**
	 * The public web folder where index.php (= the frontend application) is put. This is equal to the legacy constant
	 * PATH_site, without the trailing slash. For non-composer installations, the project path = the public path.
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getPublicPath(): string {
		return $this->facet->getPublicPath();
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
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getConfigPath() {
		return $this->facet->getConfigPath();
	}
	
	/**
	 * Returns the default template path for a given extension, optionally including a plugin namespace.
	 * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
	 * real filesystem path
	 *
	 * @param string      $extKey     The extkey for the extension to find the path for
	 * @param string|null $pluginName Optional plugin name to append to the built path
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getTemplatePath(string $extKey, ?string $pluginName = NULL): string {
		return $this->facet->getTemplatePath($extKey, $pluginName);
	}
	
	/**
	 * Returns the default partial path for a given extension, optionally including a plugin namespace.
	 * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
	 * real filesystem path
	 *
	 * @param string      $extKey     The extkey for the extension to find the path for
	 * @param string|null $pluginName Optional plugin name to append to the built path
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getPartialPath(string $extKey, ?string $pluginName = NULL): string {
		return $this->facet->getPartialPath($extKey, $pluginName);
	}
	
	/**
	 * Returns the default layout path for a given extension, optionally including a plugin namespace.
	 * The returned path is a typo3 path, beginning with EXT: use typoPathToRealPath() to convert it to a
	 * real filesystem path
	 *
	 * @param string      $extKey     The extkey for the extension to find the path for
	 * @param string|null $pluginName Optional plugin name to append to the built path
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getLayoutPath(string $extKey, ?string $pluginName = NULL): string {
		return $this->facet->getLayoutPath($extKey, $pluginName);
	}
	
	/**
	 * Returns the path to the directory where dynamic data may be stored
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getVarPath(): string {
		return $this->facet->getVarPath();
	}
	
	/**
	 * Receives a typo3 formatted string like: LLL:EXT:ext_key/something...
	 * And converts it into an absolute path. Of course you may use paths that
	 * start only like EXT:ext_key without the language selector
	 *
	 * @param string $typoPath The path to parse
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function typoPathToRealPath(string $typoPath): string {
		return $this->facet->typoPathToRealPath($typoPath);
	}
	
	/**
	 * This method does the exact opposite of typoPathToRealPath().
	 * It takes a fully qualified filename and converts it into an relative path starting with EXT:...
	 *
	 * Note: Your path should be inside a loaded extension's root directory. Otherwise the method will fail.
	 *
	 * @param string $path
	 *
	 * @return string
	 * @throws \LaborDigital\Typo3BetterApi\BetterApiException
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function realPathToTypoExt(string $path): string {
		return $this->facet->realPathToTypoExt($path);
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
	 * @param string|int|array $recordOrUid Either the uid of an existing record or a mapping of fields that are used
	 *                                      to generate a slug when the record is not yet in the database
	 * @param string           $table       The full name of the database table that holds the slug field
	 * @param string           $field       The name of the slug field to read the configuration from
	 *
	 * @return string
	 * @deprecated will be removed in v10 -> Use PathFacet instead
	 */
	public function getSlugFor($recordOrUid, string $table, string $field): string {
		return $this->facet->getSlugFor($recordOrUid, $table, $field);
	}
}