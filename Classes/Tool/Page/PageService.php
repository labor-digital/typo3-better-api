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
 * Last modified: 2021.07.26 at 14:41
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
 * Last modified: 2020.03.20 at 00:37
 */

namespace LaborDigital\T3ba\Tool\Page;

use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\PageContentsGridConfigFilterEvent;
use LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Class PageService
 *
 * @package LaborDigital\T3ba\Tool\Page
 */
class PageService implements SingletonInterface
{
    use ContainerAwareTrait;
    
    /**
     * The record data handler instance after it was created at least once
     *
     * @var \LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler;
     */
    protected $recordHandler;
    
    /**
     * Allows you to set the current page title in the PageTitle API
     *
     * @param   string  $pageTitle
     *
     * @return $this
     * @see https://docs.typo3.org/m/typo3/reference-coreapi/master/en-us/ApiOverview/PageTitleApi/Index.html
     */
    public function setPageTitle(string $pageTitle): self
    {
        $this->makeInstance(T3baPageTitleProvider::class)->setTitle($pageTitle);
        
        return $this;
    }
    
    /**
     * Returns the record data handler for the pages table.
     * Which allows you root level access to the TYPO3 data handler.
     *
     * @return \LaborDigital\T3ba\Tool\DataHandler\Record\RecordDataHandler
     */
    public function getPageDataHandler(): RecordDataHandler
    {
        return $this->recordHandler ??
               $this->recordHandler
                   = $this->cs()->dataHandler->getRecordDataHandler('pages');
    }
    
    /**
     * Creates a new, empty page below the given $parentPid with the given title and returns the new
     * page's pid for further processing
     *
     * ATTENTION: By default this method tries to create the new page using the current backend user.
     * If there is none, or the user has insufficient permissions this method will fail!
     * If you however set $force to true, the action will be executed as admin, even if there is currently no user
     * logged in
     *
     * @param   int    $parentPid  The parent page id where to create the new page
     * @param   array  $options    Additional options for the new created page
     *                             - title string (Unnamed Page): The title for the new page to create
     *                             - force bool (FALSE): If set to true, the new page is created as forced admin user,
     *                             ignoring all permissions or access rights!
     *                             - pageRow array ([]): If set, can contain additional page fields that will be set for
     *                             the newly created page
     *
     * @return int
     */
    public function createNewPage(int $parentPid, array $options = []): int
    {
        // Prepare the options
        $options = Options::make($options, [
            'title' => [
                'type' => 'string',
                'default' => '',
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
            'pageRow' => [
                'type' => 'array',
                'default' => [],
            ],
        ]);
        
        $row = $options['pageRow'];
        if ($options['title'] !== '' && ! isset($row['title'])) {
            $row['title'] = $options['title'];
        }
        
        return $this->getPageDataHandler()->save($row, $parentPid, $options['force']);
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
     * @param   int    $pageId       The page id to copy
     * @param   array  $options      Additional options
     *                               - targetPid int: The page id to copy the page to. If left empty the new page will
     *                               be copied right below the origin page
     *                               - force bool (FALSE): If set to true, the new page is copied as forced admin user,
     *                               ignoring all permissions or access rights!
     *
     * @return int
     */
    public function copyPage(int $pageId, array $options = []): int
    {
        // Prepare the options
        $options = Options::make($options, [
            'targetPid' => [
                'type' => ['null', 'int'],
                'default' => null,
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
        ]);
        
        return $this->getPageDataHandler()->copy($pageId, $options['targetPid'], $options['force']);
    }
    
    /**
     * Moves a page with the given page id to another page
     *
     * @param   int   $pageId     The page id to move
     * @param   int   $targetPid  The page id to move the page to
     * @param   bool  $force      If set to true, the new page is moved as forced admin user,
     *                            ignoring all permissions or access rights!
     *
     * @return void
     */
    public function movePage(int $pageId, int $targetPid, bool $force = false): void
    {
        $this->getPageDataHandler()->move($pageId, $targetPid, $force);
    }
    
    /**
     * Marks this page as "deleted". It still can be restored using the "restorePage" method.
     *
     * @param   int   $pageId  The page to delete
     * @param   bool  $force   If set to true, the new page is deleted as forced admin user,
     *                         ignoring all permissions or access rights!
     */
    public function deletePage(int $pageId, bool $force = false): void
    {
        $this->getPageDataHandler()->delete($pageId, $force);
    }
    
    /**
     * Restores a page by removing the marker that defines it as "deleted".
     *
     * @param   int   $pageId  The page to restore
     * @param   bool  $force   If set to true, the new page is restored as forced admin user,
     *                         ignoring all permissions or access rights!
     */
    public function restorePage(int $pageId, bool $force = false): void
    {
        $this->getPageDataHandler()->restore($pageId, $force);
    }
    
    /**
     * Returns true if a page exists, false if not.
     *
     * @param   int   $pageId
     * @param   bool  $includeAllNotDeleted  If set to true, the method will check for
     *                                       all pages no matter of their access restrictions or doktype
     *
     * @return bool
     */
    public function pageExists(int $pageId, bool $includeAllNotDeleted = false): bool
    {
        if ($pageId <= 0) {
            return false;
        }
        
        if ($includeAllNotDeleted) {
            return ! empty($this->getPageRepository()->getPage_noCheck($pageId));
        }
        
        return ! empty($this->getPageRepository()->getPage($pageId));
    }
    
    /**
     * This method can be used to render the contents of a given page id as html.
     *
     * This method uses the TypoScriptFrontendController to render the required output.
     * If you are in the backend or in a CLI context this method WILL FORCE the creation of the TSFE.
     * Make sure that it will not break in your context!
     *
     * @param   int    $pageId
     * @param   array  $options      Additional options
     *                               - includeHidden bool (FALSE) If set to true, hidden pages will be rendered as well.
     *                               - language string|int|SiteLanguage: Can be used to render the page contents in a
     *                               specific language context
     *                               - includeHiddenPages bool (FALSE): If this is set to true the closure will
     *                               have access to all hidden pages.
     *                               - includeHiddenContent bool (FALSE): If this is set to true the closure will
     *                               have access to all hidden content elements on when retrieving tt_content data
     *                               - includeDeletedRecords bool (FALSE): If this is set to true the requests
     *                               made in the closure will include deleted records
     *                               - force bool (FALSE): If set to true, the new page is copied as forced admin user,
     *                               ignoring all permissions or access rights!
     *                               - colPos int (0): The column id you want to render the contents for
     *                               - site string: Can be set to a valid site identifier to simulate the request
     *                               on a specific TYPO3 site.
     *
     * @return string
     */
    public function renderPageContents(int $pageId, array $options = []): string
    {
        $options = Options::make($options, [
            'language' => [
                'type' => ['int', 'string', 'null', SiteLanguage::class],
                'default' => null,
            ],
            'includeHiddenPages' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeHiddenContent' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeDeletedRecords' => [
                'type' => 'bool',
                'default' => false,
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
            'colPos' => [
                'type' => 'int',
                'default' => 0,
            ],
            'site' => [
                'type' => ['string', 'null'],
                'default' => null,
            ],
        ]);
        
        return $this->cs()->simulator->runWithEnvironment([
            'site' => $options['site'],
            'asAdmin' => $options['force'],
            'pid' => $pageId,
            'language' => $options['language'],
            'includeHiddenPages' => $options['includeHiddenPages'],
            'includeHiddenContent' => $options['includeHiddenContent'],
            'includeDeletedRecords' => $options['includeDeletedRecords'],
        ], function () use ($pageId, $options) {
            return $this->cs()->ts->renderContentObject('CONTENT', [
                'table' => 'tt_content',
                'select.' => [
                    'pidInList' => $this->resolveContentPid($pageId),
                    'languageField' => $GLOBALS['TCA']['tt_content']['ctrl']['languageField'] ?? 'sys_language_uid',
                    'orderBy' => 'sorting',
                    'where' => '{#colPos}=' . $options['colPos'],
                ],
            ]);
        });
    }
    
    /**
     * Can be used to return the list of all content elements of a given page.
     * The contents will be sorted into their matching layout columns in order of their "sorting".
     *
     * This method will make an educated guess on your content elements and if you are running a modular griding
     * extension like grid elements. If you do, the elements will be hierarchically sorted by their parents.
     *
     * @param   int    $pageId       The id of the page to load the contents for
     * @param   array  $options      Additional options for this method
     *                               - where string: Can be used to add an additional where clause to limit the type of
     *                               content elements that are returned on the given page
     *                               - language int (current sys language) Can be used to specify the language to render the
     *                               contents in
     *                               - includeHiddenPages bool (FALSE): If this is set to true the closure will
     *                               have access to all hidden pages.
     *                               - includeHiddenContent bool (FALSE): If this is set to true the closure will
     *                               have access to all hidden content elements on when retrieving tt_content data
     *                               - includeDeletedRecords bool (FALSE): If this is set to true the requests
     *                               made in the closure will include deleted records
     *                               - returnRaw bool (FALSE): If set to true the method will return the
     *                               raw list of records instead of the sorted list of elements
     *                               - force bool (FALSE): If set to true, the new page is copied as forced admin user,
     *                               ignoring all permissions or access rights!
     *
     * @return mixed
     */
    public function getPageContents(int $pageId, array $options = [])
    {
        $options = Options::make($options, [
            'where' => [
                'type' => 'string',
                'default' => '',
            ],
            'language' => [
                'type' => ['int', 'string', 'null', SiteLanguage::class],
                'default' => null,
            ],
            'includeHiddenPages' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeHiddenContent' => [
                'type' => 'bool',
                'default' => false,
            ],
            'includeDeletedRecords' => [
                'type' => 'bool',
                'default' => false,
            ],
            'returnRaw' => [
                'type' => 'bool',
                'default' => false,
            ],
            'force' => [
                'type' => 'bool',
                'default' => false,
            ],
            'site' => [
                'type' => ['string', 'null'],
                'default' => null,
            ],
        ]);
        
        $records = $this->cs()->simulator->runWithEnvironment([
            'site' => $options['site'],
            'asAdmin' => $options['force'],
            'language' => $options['language'],
            'includeHiddenPages' => $options['includeHiddenPages'],
            'includeHiddenContent' => $options['includeHiddenContent'],
            'includeDeletedRecords' => $options['includeDeletedRecords'],
        ], function () use ($pageId, $options) {
            return $this->cs()->tsfe->getContentObjectRenderer()->getRecords('tt_content', [
                'pidInList' => $this->resolveContentPid($pageId),
                'where' => $options['where'],
            ]);
        });
        if (! is_array($records)) {
            $records = [];
        }
        if ($options['returnRaw']) {
            return $records;
        }
        
        // Default configuration for extensions that provide custom grids
        $customGrids = [
            [
                'parentField' => 'tx_gridelements_container',
                'parentColField' => 'tx_gridelements_columns',
            ],
        ];
        
        // Let the outside world add it's own grids or filter the records if required...
        $this->cs()->eventBus
            ->dispatch(($e = new PageContentsGridConfigFilterEvent($pageId, $records, $customGrids)));
        $records = $e->getRecords();
        $customGrids = $e->getCustomGrids();
        
        // Loop 1: Map the records into an element list
        $elements = [];
        foreach ($records as $record) {
            $uid = $record['uid'];
            $row = [
                'parent' => null,
                'colPos' => $record['colPos'],
                'uid' => $uid,
                'record' => $record,
                'children' => [],
                'sorting' => $record['sorting'],
            ];
            $elements[$uid] = $row;
        }
        
        // Loop 2: Map potential stacked grids to their parents
        foreach ($elements as &$element) {
            $parent = null;
            $record = $element['record'];
            $colPos = $record['colPos'];
            
            foreach ($customGrids as $customGridConfig) {
                // Ignore if the custom grid has no parent field -> misconfiguration
                if (! isset($customGridConfig['parentField'])) {
                    continue;
                }
                // Ignore if the records does not have the required parent field
                if (empty($record[$customGridConfig['parentField']])) {
                    continue;
                }
                // Map The parent
                $parent = $record[$customGridConfig['parentField']];
                $colPos = 0;
                // Check if the parent col field exists
                if (isset($customGridConfig['parentColField'])
                    && ! empty($record[$customGridConfig['parentColField']])) {
                    $colPos = $record[$customGridConfig['parentColField']];
                }
                break;
            }
            
            // Check if we can map the record as a child
            if (empty($parent)) {
                continue;
            }
            
            // Strip out element's that define a parent which is not in our element list -> broken relation?
            if (! isset($elements[$parent])) {
                $element['parent'] = false;
                continue;
            }
            
            // Map the element into a tree
            $element['parent'] = $parent;
            $element['colPos'] = $colPos;
            $elements[$parent]['children'][$colPos][$element['uid']] = &$element;
        }
        unset($element);
        
        // Loop 3: Sort the children and clean up the output
        $output = [];
        foreach ($elements as &$element) {
            // Sort the element in the child array
            if (! empty($element['children'])) {
                foreach ($element['children'] as &$childCol) {
                    $childCol = Arrays::sortBy($childCol, 'sorting');
                }
                unset($childCol);
            }
            
            // Build the output
            if ($element['parent'] === null) {
                $output[$element['colPos']][$element['uid']] = $element;
            }
        }
        unset($element);
        
        // Sort the elements inside the cols
        foreach ($output as &$col) {
            $col = Arrays::sortBy($col, 'sorting');
        }
        
        // Done (make sure we break the references)
        return SerializerUtil::unserializeJson(
            SerializerUtil::serializeJson($output)
        );
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
     * @param   int    $pageId
     * @param   array  $options  Additional options for the root line renderer
     *                           - includeAllNotDeleted bool (FALSE): If set to true this will generate the
     *                           rootline without caring for permissions
     *                           - additionalFields array: A list of additional fields to fetch for the
     *                           generated root line
     *
     * @return array|mixed
     */
    public function getRootLine(int $pageId, array $options = [])
    {
        return ExtendedRootLineUtility::getWith($pageId, $options);
    }
    
    /**
     * Can be used to retrieve the database record for a certain page based on the given page id.
     * The translation is done according to the current frontend language.
     *
     * @param   int   $pageId                The id of the page to find the information for
     * @param   bool  $includeAllNotDeleted  Set to true to include hidden pages or doktypes > 200
     *
     * @return null|array
     */
    public function getPageInfo(int $pageId, bool $includeAllNotDeleted = false): ?array
    {
        if ($includeAllNotDeleted) {
            $row = $this->getPageRepository()->getPage_noCheck($pageId);
        } else {
            $row = $this->getPageRepository()->getPage($pageId, true);
        }
        
        if (! is_array($row)) {
            return null;
        }
        
        return $row;
    }
    
    /**
     * Returns the instance of the page repository.
     * Either of the frontend or a new instance if the frontend did not help us...
     *
     * @return \TYPO3\CMS\Frontend\Page\PageRepository
     */
    public function getPageRepository(): PageRepository
    {
        // Try to load the page repository from the frontend
        $tsfe = $this->cs()->tsfe;
        if ($tsfe->hasTsfe()) {
            $sysPage = $tsfe->getTsfe()->sys_page;
            if ($sysPage instanceof PageRepository) {
                return $sysPage;
            }
        }
        
        // Fallback to creating a new instance when the frontend did not serve us
        return $this->makeInstance(PageRepository::class);
    }
    
    /**
     * Internal helper to check the "content_from_pid" field of the given page id.
     * If it has another pid as a reference we will rewrite the page id to retrieve the contents from
     *
     * @param   int  $pageId
     *
     * @return int
     */
    protected function resolveContentPid(int $pageId): int
    {
        $pageInfo = $this->getPageInfo($pageId);
        if (! empty($pageInfo['content_from_pid'])) {
            $pidList = Arrays::makeFromStringList($pageInfo['content_from_pid']);
            if (empty($pidList)) {
                return $pageId;
            }
            /** @noinspection CallableParameterUseCaseInTypeContextInspection */
            $pageId = reset($pidList);
        }
        
        return $pageId;
    }
}
