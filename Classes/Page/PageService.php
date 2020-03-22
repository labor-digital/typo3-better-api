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
 * Last modified: 2020.03.20 at 00:37
 */

namespace LaborDigital\Typo3BetterApi\Page;

use LaborDigital\Typo3BetterApi\Container\CommonServiceLocatorTrait;
use LaborDigital\Typo3BetterApi\Event\Events\PageContentsGridConfigFilterEvent;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Class PageService
 * @package LaborDigital\Typo3BetterApi\Pages
 *
 * @property DataHandler $DataHandler
 */
class PageService implements SingletonInterface {
	use CommonServiceLocatorTrait;
	
	/**
	 * Holds the list of cached root lines by their cache key
	 * @var array
	 */
	protected $rootLineCache = [];
	
	/**
	 * Creates a new, empty page below the given $parentPid with the given title and returns the new
	 * page's pid for further processing
	 *
	 * ATTENTION: By default this method tries to create the new page using the current backend user.
	 * If there is none, or the user has insufficient permissions this method will fail!
	 * If you however set $force to true, the action will be executed as admin, even if there is currently no user
	 * logged in
	 *
	 * @param int   $parentPid  The parent page id where to create the new page
	 * @param array $options    Additional options for the new created page
	 *                          - title string (Unnamed Page): The title for the new page to create
	 *                          - force bool (FALSE): If set to true, the new page is created as forced admin user,
	 *                          ignoring all permissions or access rights!
	 *                          - pageRow array ([]): If set, can contain additional page fields that will be set for
	 *                          the newly created page
	 *
	 * @return int
	 */
	public function createNewPage(int $parentPid, array $options = []): int {
		// Prepare the options
		$options = Options::make($options, [
			"title"   => [
				"type"    => "string",
				"default" => "Unnamed Page",
			],
			"force"   => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"pageRow" => [
				"type"    => "array",
				"default" => [],
			],
		]);
		
		// Handle the creation
		return $this->forceWrapper(function () use ($parentPid, $options) {
			
			// Create the new row
			$row = Arrays::merge([
				"title" => $options["title"],
				"pid"   => $parentPid,
			], $options["pageRow"]);
			
			// Create a new page, programmatically
			$dataHandler = $this->DataHandler;
			$dataHandler->start(["pages" => ["NEW_1" => $row]], []);
			$dataHandler->process_datamap();
			
			// Handle errors
			if (!empty($dataHandler->errorLog)) throw new PageServiceException(reset($dataHandler->errorLog));
			
			// Return the new page's id
			return reset($dataHandler->substNEWwithIDs);
		}, $options["force"]);
	}
	
	/**
	 * Creates a copy of a certain page. If the $targetPageId is empty, the copy will be created right below the
	 * current page Otherwise it will be copied as a child of said target id.
	 *
	 * ATTENTION: By default this method tries to copy the using the current backend user.
	 * If there is none, or the user has insufficient permissions this method will fail!
	 * If you however set $force to true, the action will be executed as admin, even if there is currently no user
	 * logged in
	 *
	 * @param int   $pageId          The page id to copy
	 * @param array $options         Additional options
	 *                               - targetPid int: The page id to copy the page to. If left empty the new page will
	 *                               be copied right below the origin page
	 *                               - force bool (FALSE): If set to true, the new page is copied as forced admin user,
	 *                               ignoring all permissions or access rights!
	 *
	 * @return int
	 */
	public function copyPage(int $pageId, array $options = []): int {
		// Prepare the options
		$options = Options::make($options, [
			"targetPid" => [
				"type"    => "int",
				"default" => -1,
			],
			"force"     => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		return $this->forceWrapper(function () use ($pageId, $options) {
			// Copy the page
			$dataHandler = $this->DataHandler;
			$dataHandler->errorLog = [];
			$dataHandler->start([], [
				"pages" => [
					$pageId => [
						"copy" => $options["targetPid"] === -1 ? -$pageId : $options["targetPid"],
					],
				],
			]);
			$dataHandler->process_cmdmap();
			
			// Handle errors
			if (!empty($dataHandler->errorLog)) throw new PageServiceException(reset($dataHandler->errorLog));
			
			// Return the page id of the copied page
			return $dataHandler->copyMappingArray["pages"][$pageId];
		}, $options["force"]);
	}
	
	/**
	 * Moves a page with the given page id to another page
	 *
	 * @param int  $pageId    The page id to move
	 * @param int  $targetPid The page id to move the page to
	 * @param bool $force     If set to true, the new page is moved as forced admin user,
	 *                        ignoring all permissions or access rights!
	 *
	 * @return void
	 */
	public function movePage(int $pageId, int $targetPid, bool $force = FALSE): void {
		$this->forceWrapper(function () use ($pageId, $targetPid) {
			// Move the page
			$dataHandler = $this->DataHandler;
			$dataHandler->start([], [
				"pages" => [
					$pageId => [
						"move" => $targetPid,
					],
				],
			]);
			$dataHandler->process_cmdmap();
			
			// Handle errors
			if (!empty($dataHandler->errorLog)) throw new PageServiceException(reset($dataHandler->errorLog));
		}, $force);
	}
	
	/**
	 * Marks this page as "deleted". It still can be restored using the "restorePage" method.
	 *
	 * @param int  $pageId    The page to delete
	 * @param bool $force     If set to true, the new page is deleted as forced admin user,
	 *                        ignoring all permissions or access rights!
	 */
	public function deletePage(int $pageId, bool $force = FALSE): void {
		$this->forceWrapper(function () use ($pageId) {
			// Move the page
			$dataHandler = $this->DataHandler;
			$dataHandler->start([], [
				"pages" => [
					$pageId => [
						"delete" => 1,
					],
				],
			]);
			$dataHandler->process_cmdmap();
			
			// Handle errors
			if (!empty($dataHandler->errorLog)) throw new PageServiceException(reset($dataHandler->errorLog));
		}, $force);
	}
	
	/**
	 * Restores a page by removing the marker that defines it as "deleted".
	 *
	 * @param int  $pageId    The page to restore
	 * @param bool $force     If set to true, the new page is restored as forced admin user,
	 *                        ignoring all permissions or access rights!
	 */
	public function restorePage(int $pageId, bool $force = FALSE): void {
		$this->forceWrapper(function () use ($pageId) {
			// Move the page
			$dataHandler = $this->DataHandler;
			$dataHandler->start([], [
				"pages" => [
					$pageId => [
						"delete" => 1,
					],
				],
			]);
			$dataHandler->process_cmdmap();
			
			// Handle errors
			if (!empty($dataHandler->errorLog)) throw new PageServiceException(reset($dataHandler->errorLog));
		}, $force);
	}
	
	/**
	 * Returns true if a page exists, false if not.
	 *
	 * @param int  $pageId
	 * @param bool $includeDeleted If true, deleted pages will also be checked for
	 *
	 * @return bool
	 */
	public function pageExists(int $pageId, bool $includeDeleted = FALSE): bool {
		if ($pageId <= 0) return FALSE;
		if ($includeDeleted) return $this->Db->getQuery("pages", TRUE)->withWhere(["uid" => $pageId])->getCount() > 0;
		return !empty($this->getPageRepository()->getPage($pageId));
	}
	
	/**
	 * This method can be used to render the contents of a given page id as html.
	 *
	 * This method uses the TypoScriptFrontendController to render the required output.
	 * If you are in the backend or in a CLI context this method WILL FORCE the creation of the TSFE.
	 * Make sure that it will not break in your context!
	 *
	 * @param int   $pageId
	 * @param array $options    Additional options
	 *                          - includeHidden bool (FALSE) If set to true, hidden pages will be rendered as well.
	 *                          - language string|int|SiteLanguage: Can be used to render the page contents in a
	 *                          specific language context
	 *                          - includeHiddenPages bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden pages.
	 *                          - includeHiddenContent bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden content elements on when retrieving tt_content data
	 *                          - includeDeletedRecords bool (FALSE): If this is set to true the requests
	 *                          made in the closure will include deleted records
	 *
	 * @return string
	 */
	public function renderPageContents(int $pageId, array $options = []) {
		
		// Prepare options
		$options = Options::make($options, [
			"language"              => [
				"type"    => ["int", "string", "null", SiteLanguage::class],
				"default" => NULL,
			],
			"includeHiddenPages"    => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeHiddenContent"  => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeDeletedRecords" => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		return $this->Simulator->runWithEnvironment([
			"pid"                   => $pageId,
			"language"              => $options["language"],
			"includeHiddenPages"    => $options["includeHiddenPages"],
			"includeHiddenContent"  => $options["includeHiddenContent"],
			"includeDeletedRecords" => $options["includeDeletedRecords"],
		], function () use ($pageId, $options) {
			
			// Render the page
			return $this->TypoScript->renderContentObject("CONTENT", [
				"table"   => "tt_content",
				"select." => [
					"pidInList"     => $this->resolveContentPid($pageId),
					"languageField" => "sys_language_uid",
					"orderBy"       => "sorting",
					"where"         => "{#colPos} = 0",
				],
			]);
			
		});
	}
	
	/**
	 * Can be used to return the list of all content elements of a given page.
	 * The contents will be sorted into their matching layout columns in order of their "sorting".
	 *
	 * This method will make an educated guess on your content elements and if you are running a modular griding
	 * extension like gridelements. If you do, the elements will be hierarchically sorted by their parents.
	 *
	 * @param int   $pageId     The id of the page to load the contents for
	 * @param array $options    Additional options for this method
	 *                          - where string: Can be used to add an additional where clause to limit the type of
	 *                          content elements that are returned on the given page
	 *                          - language int (current sys language) Can be used to specify the language to render the
	 *                          contents in
	 *                          - includeHiddenPages bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden pages.
	 *                          - includeHiddenContent bool (FALSE): If this is set to true the closure will
	 *                          have access to all hidden content elements on when retrieving tt_content data
	 *                          - includeDeletedRecords bool (FALSE): If this is set to true the requests
	 *                          made in the closure will include deleted records
	 *
	 * @return mixed
	 */
	public function getPageContents(int $pageId, array $options = []) {
		// Prepare options
		$options = Options::make($options, [
			"where"                 => [
				"type"    => "string",
				"default" => "",
			],
			"language"              => [
				"type"    => ["int", "string", "null", SiteLanguage::class],
				"default" => NULL,
			],
			"includeHiddenPages"    => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeHiddenContent"  => [
				"type"    => "bool",
				"default" => FALSE,
			],
			"includeDeletedRecords" => [
				"type"    => "bool",
				"default" => FALSE,
			],
		]);
		
		// Collect the records
		$records = $this->Simulator->runWithEnvironment([
			"language"              => $options["language"],
			"includeHiddenPages"    => $options["includeHiddenPages"],
			"includeHiddenContent"  => $options["includeHiddenContent"],
			"includeDeletedRecords" => $options["includeDeletedRecords"],
		], function () use ($pageId, $options) {
			return $this->Tsfe->getContentObjectRenderer()->getRecords("tt_content", [
				"pidInList" => $this->resolveContentPid($pageId),
				"where"     => $options["where"],
			]);
		});
		if (!is_array($records)) $records = [];
		
		// Default configuration for extensions that provide custom grids
		$customGrids = [
			[
				"parentField"    => "tx_gridelements_container",
				"parentColField" => "tx_gridelements_columns",
			],
		];
		
		// Let the outside world add it's own grids or filter the records if required...
		$this->EventBus->dispatch(($e = new PageContentsGridConfigFilterEvent($pageId, $records, $customGrids)));
		$records = $e->getRecords();
		$customGrids = $e->getCustomGrids();
		
		// Loop 1: Map the records into an element list
		$elements = [];
		foreach ($records as $record) {
			$uid = $record["uid"];
			$row = [
				"parent"   => NULL,
				"colPos"   => $record["colPos"],
				"uid"      => $uid,
				"record"   => $record,
				"children" => [],
				"sorting"  => $record["sorting"],
			];
			$elements[$uid] = $row;
		}
		
		// Loop 2: Map potential stacked grids to their parents
		foreach ($elements as &$element) {
			$parent = NULL;
			$record = $element["record"];
			$colPos = $record["colPos"];
			
			foreach ($customGrids as $customGridConfig) {
				// Ignore if the custom grid has no parent field -> misconfiguration
				if (!isset($customGridConfig["parentField"])) continue;
				// Ignore if the records does not have the required parent field
				if (empty($record[$customGridConfig["parentField"]])) continue;
				// Map The parent
				$parent = $record[$customGridConfig["parentField"]];
				$colPos = 0;
				// Check if the parent col field exists
				if (isset($customGridConfig["parentColField"]) && !empty($record[$customGridConfig["parentColField"]]))
					$colPos = $record[$customGridConfig["parentColField"]];
				break;
			}
			
			// Check if we can map the record as a child
			if (empty($parent)) continue;
			
			// Strip out element's that define a parent which is not in our element list -> broken relation?
			if (!isset($elements[$parent])) {
				$element["parent"] = FALSE;
				continue;
			}
			
			// Map the element into a tree
			$element["parent"] = $parent;
			$element["colPos"] = $colPos;
			$elements[$parent]["children"][$colPos][$element["uid"]] = &$element;
		}
		
		// Loop 3: Sort the children and clean up the output
		$output = [];
		foreach ($elements as &$element) {
			// Sort the element in the child array
			if (!empty($element["children"]))
				foreach ($element["children"] as &$childCol)
					$childCol = Arrays::sortBy($childCol, "sorting");
			
			// Build the output
			if ($element["parent"] === NULL) $output[$element["colPos"]][$element["uid"]] = $element;
		}
		
		// Sort the elements inside the cols
		foreach ($output as &$col)
			$col = Arrays::sortBy($col, "sorting");
		
		// Done (make sure we break the references)
		return json_decode(json_encode($output), TRUE);
	}
	
	/**
	 * Returns an array with fields of the pages from here ($uid) and back to the root
	 *
	 * NOTICE: This function only takes deleted pages into account! So hidden,
	 * starttime and endtime restricted pages are included no matter what.
	 *
	 * Further: If any "recycler" page is found (doktype=255) then it will also block
	 * for the rootline)
	 *
	 * If you want more fields in the rootline records than default such can be added
	 * by listing them in $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields']
	 *
	 * @param int  $pageId
	 * @param bool $ignorePermissions If set to true this will generate the rootline without caring for permissions
	 *
	 * @return array|mixed
	 */
	public function getRootLine(int $pageId, bool $ignorePermissions = FALSE) {
		
		// Try to load the root line from our cache
		$cacheKey = $pageId . "" . $ignorePermissions;
		if (isset($this->rootLineCache[$cacheKey])) return $this->rootLineCache[$cacheKey];
		
		// Prepare the repository
		$repo = $this->getPageRepository();
		$backupPermission = $repo->where_groupAccess;
		if ($ignorePermissions) $repo->where_groupAccess = "";
		
		// Get the root line
		$rootLineUtility = $this->getInstanceOf(RootlineUtility::class, [$pageId]);
		$rootLine = $rootLineUtility->get();
		$this->rootLineCache[$cacheKey] = $rootLine;
		
		// Restore the repository
		$repo->where_groupAccess = $backupPermission;
		
		// Done
		return $rootLine;
		
	}
	
	/**
	 * Can be used to retrieve the database record for a certain page based on the given page id.
	 * The translation is done according to the current frontend language.
	 *
	 * @param int $pageId
	 *
	 * @return null|array
	 */
	public function getPageInfo(int $pageId): ?array {
		$row = $this->getPageRepository()->getPage($pageId, TRUE);
		if (!is_array($row)) return NULL;
		return $row;
	}
	
	/**
	 * Returns the instance of the page repository.
	 * Either of the frontend or a new instance if the frontend did not help us...
	 *
	 * @return \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	public function getPageRepository(): PageRepository {
		// Try to load the page repository from the frontend
		if ($this->Tsfe->hasTsfe()) return clone $this->Tsfe->getTsfe()->sys_page;
		
		// Fallback to creating a new instance when the frontend did not serve us
		return $this->getInstanceOf(PageRepository::class);
	}
	
	/**
	 * Internal helper to run the given callback either as forced user or as the current user
	 *
	 * @param callable $callback The callback to execute
	 * @param bool     $force    True to run as a forced admin user
	 *
	 * @return mixed
	 */
	protected function forceWrapper(callable $callback, bool $force) {
		if (!$force) return call_user_func($callback);
		return $this->Simulator->runAsAdmin($callback);
	}
	
	/**
	 * Internal helper to check the "content_from_pid" field of the given page id.
	 * If it has another pid as a reference we will rewrite the page id to retrieve the contents from
	 *
	 * @param int $pageId
	 *
	 * @return int
	 */
	protected function resolveContentPid(int $pageId): int {
		$pageInfo = $this->getPageInfo($pageId);
		if (isset($pageInfo["content_from_pid"]) && !empty($pageInfo["content_from_pid"]))
			$pageId = reset(Arrays::makeFromStringList($pageInfo["content_from_pid"]));
		return $pageId;
	}
}