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

namespace LaborDigital\T3ba\Tool\Database\BetterQuery;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use LaborDigital\T3ba\Tool\TypoContext\TypoContext;
use Neunerlei\Options\Options;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

abstract class AbstractBetterQuery implements NoDiInterface
{
    use QueryWhereApplierTrait;
    
    /**
     * @var AbstractQueryAdapter
     */
    protected $adapter;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\Session
     */
    protected $session;
    
    /**
     * @var \LaborDigital\T3ba\Tool\TypoContext\TypoContext
     */
    protected $typoContext;
    
    /**
     * Defines, if set the number of items that are on a logical "page" when using the getPage() or getPages() methods
     *
     * @var int|null
     */
    protected $itemsPerPage;
    
    /**
     * Contains the currently configured where statement
     *
     * @var array
     */
    protected $where = [];
    
    
    /**
     * BetterQuery constructor.
     *
     * @param   \LaborDigital\T3ba\Tool\Database\BetterQuery\AbstractQueryAdapter  $adapter
     * @param   \LaborDigital\T3ba\Tool\TypoContext\TypoContext                    $typoContext
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\Session                     $session
     */
    public function __construct(
        AbstractQueryAdapter $adapter,
        TypoContext $typoContext,
        Session $session
    )
    {
        $this->adapter = $adapter;
        $this->typoContext = $typoContext;
        $this->session = $session;
    }
    
    /**
     * Clones the children of this query object to keep it immutable
     */
    public function __clone()
    {
        $this->adapter = clone $this->adapter;
    }
    
    /**
     * Returns the configured instance of the query builder for this query
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    abstract public function getQueryBuilder(): QueryBuilder;
    
    /**
     * Executes the currently configured query and returns the results
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    abstract public function getAll();
    
    /**
     * Returns the total number of items in the result set, matching the given query parameters
     *
     * @return int
     */
    abstract public function getCount(): int;
    
    /**
     * Returns the first element from the queries result set that matches your criteria
     *
     * @return mixed
     */
    abstract public function getFirst();
    
    /**
     * Returns the name of the database table this query applies to
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->adapter->getTableName();
    }
    
    /**
     * By default the results from ALL pages will be returned.
     * If you want to limit your result set to a single, or a range of pages you can use this method.
     *
     * Keep in mind tho, that the pid limitations apply to all objects you are looking up, even if you
     * are searching for child properties, the child has to match your pid constraints!
     *
     * @param   string|array|int|bool  $pids  The list of pids you want to set as constraints.
     *                                        Either a single value, or an array of values.
     *                                        You may use pid selectors using the PidService like
     *                                        (at)pid.storage.something If you set this to TRUE the current page id
     *                                        will be used. Setting this to FALSE will remove the pid restrictions
     *
     * @return $this
     */
    public function withPids($pids): self
    {
        $clone = clone $this;
        $settings = $clone->adapter->getSettings();
        
        // Handle special pid values
        if (is_bool($pids)) {
            // True means use current pid
            if ($pids) {
                $pids = $clone->typoContext->pid()->getCurrent();
            } // False means reset storage page
            else {
                $settings->setRespectStoragePage(false);
                
                return $clone;
            }
        }
        
        // Apply possible pid values
        $pids = array_map(static function ($v) use ($clone) {
            if (! is_string($v) || ! $clone->typoContext->pid()->has($v)) {
                return $v;
            }
            
            return $clone->typoContext->pid()->get($v);
        }, $clone->adapter->ensureArrayValue($pids, 'pid'));
        
        // Update the page limitations in the query settings
        $settings->setRespectStoragePage(true);
        $settings->setStoragePageIds($pids);
        
        return $clone;
    }
    
    /**
     * Either returns the list of registered storage page ids or null
     * if there are either none or the query should not respect storage page ids
     *
     * @return array|null
     */
    public function getPids(): ?array
    {
        return $this->adapter->getSettings()->getRespectStoragePage() ?
            $this->adapter->getSettings()->getStoragePageIds() : null;
    }
    
    /**
     * Used to set the language restriction for the current query.
     * By default only records of the currently active language will be returned.
     *
     * @param   string|int|SiteLanguage|bool  $language  The language you want to limit your query to.
     *                                                   Can be either the sys_language_uid value of the language,
     *                                                   can also be a Language object returned by the LanguageService.
     *                                                   If this is set to TRUE you can reset the limitations back to
     *                                                   the current language uid If this is set to FALSE you disable
     *                                                   all language constraints
     * @param   array                         $options   Additional, language specific options to apply.
     *                                                   - overlayMode string|bool (FALSE): Defines how TYPO3 should
     *                                                   handle language overlays.
     *                                                   Values: TRUE, FALSE or "hideNonTranslated"
     *                                                   {@link QuerySettingsInterface::setLanguageOverlayMode()}
     *
     * @return $this
     * @throws \LaborDigital\T3ba\Tool\Database\BetterQuery\BetterQueryException
     */
    public function withLanguage($language, array $options = [])
    {
        $clone = clone $this;
        $settings = $clone->adapter->getSettings();
        
        // Reset the ext base object cache
        $clone->session->destroy();
        
        // Handle legacy options
        $options = Options::make($options, [
            'overlayMode' => [
                'type' => ['bool', 'string'],
                'default' => false,
            ],
        ]);
        
        // Set language mode
        $settings->setLanguageOverlayMode($options['overlayMode']);
        
        // Handle special language values
        if (is_bool($language)) {
            $settings->setRespectSysLanguage($language);
            if ($language) {
                $settings->setLanguageUid(
                    $clone->typoContext->language()->getCurrentFrontendLanguage()->getLanguageId()
                );
            }
            
            return $clone;
        }
        
        // Convert language objects
        if (is_object($language) && $language instanceof SiteLanguage) {
            $language = $language->getLanguageId();
        }
        if (is_string($language)) {
            $language = (int)$language;
        }
        if (! is_int($language)) {
            throw new BetterQueryException(
                'The given language is invalid! Only integers or objects of ' . SiteLanguage::class . ' are allowed!');
        }
        $settings->setRespectSysLanguage(true);
        $settings->setLanguageUid($language);
        
        // Done
        return $clone;
    }
    
    /**
     * Set this to true if you want the query to retrieve hidden / disabled elements as well
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function withIncludeHidden(bool $state = true): self
    {
        $clone = clone $this;
        $settings = $clone->adapter->getSettings();
        // @todo This is crap, because it will disable "deleted", if it was set previously
        $settings->setIgnoreEnableFields($state);
        if ($state) {
            $settings->setEnableFieldsToBeIgnored(['disabled']);
        }
        
        return $clone;
    }
    
    /**
     * Set this to true if you want the query to retrieve deleted elements as well.
     *
     * Attention: If you set this to TRUE you will also receive all hidden records!
     *
     * @param   bool  $state
     *
     * @return $this
     */
    public function withIncludeDeleted(bool $state = true): self
    {
        $clone = clone $this;
        $settings = $clone->adapter->getSettings();
        // @todo This is crap, because it will disable "hidden", if it was set previously
        $settings->setIgnoreEnableFields($state);
        $settings->setIncludeDeleted($state);
        
        return $clone;
    }
    
    /**
     * Set the limit of items retrieved by this demand
     *
     * @param   int  $limit
     *
     * @return $this
     */
    public function withLimit(int $limit): self
    {
        $clone = clone $this;
        $clone->adapter->setLimit($limit);
        
        return $clone;
    }
    
    /**
     * Sets the offset in the database table
     *
     * @param   int  $offset
     *
     * @return $this
     */
    public function withOffset(int $offset): self
    {
        $clone = clone $this;
        $clone->adapter->setOffset($offset);
        
        return $clone;
    }
    
    /**
     * Can be used to set the order in which the results will be returned from the database.
     * Can be either a key / direction pair or an array of key / direction pairs if you want to sort the
     * results by multiple fields.
     *
     * @param   string|array  $field      Either the field to sort by, or an array of key direction pairs
     * @param   string|null   $direction  If $field is a string, this should define the direction.
     *                                    You may use asc | desc | ASC | DESC | QueryInterface::ORDER_ASCENDING
     *                                    | QueryInterface::ORDER_DESCENDING.
     *                                    If $field is an array this parameter is ignored
     *
     * @return $this
     */
    public function withOrder($field, ?string $direction = null): self
    {
        $clone = clone $this;
        
        // Unify the input to an array
        if (! is_array($field)) {
            $field = [$field => $direction];
        }
        
        // Build the orderings
        $orderings = [];
        foreach ($field as $k => $_dir) {
            $orderings[$k] = strtolower(trim($_dir ?? 'asc')) === 'asc' ? 'ASC' : 'DESC';
        }
        $clone->adapter->setOrderings($orderings);
        
        return $clone;
    }
    
    /**
     * This method is the central element of the query. I tried to make the syntax as easy to read and write as
     * possible
     * without introducing to much overhead or additional language constructs.
     *
     * I tried to copy a lot of CakePhp's query builder syntax, as it is really intuitive.
     *
     * This method defines the WHERE conditions of your SQL query that is underlying extBase's repositories.
     * In any case you have to supply an array of conditions, where the "key" specifies the field (NOTE: the extBase
     * object property name, not the database column name! So DB: my_field, EXTBASE: myField <- This one).
     *
     * It is also possible to create "WHERE groups". Groups are basically named subparts of the same query.
     * They are used to provide additional structuring when the query object is passed around through helper methods
     * that should apply additional query constraints. By default all groups are chained using the AND operator. If you
     * want to use an OR operator prefix your group key with an "OR $yourGroupName".
     *
     * EQUALS:
     * ----------------------
     * The simplest query is to define a key value pair:
     * $query->where(["uid" => 12])->getAll()
     * This will return the entity with uid 12
     *
     * Note: No operator means that "equals" is used. You can see the syntax above as shorthand for:
     * $query->where(["uid =" => 12])->getAll()
     *
     * NEGATION | NOT EQUALS:
     * ----------------------
     * To negate any operator (in, <, >=...) you can prefix an exclamation mark before it.
     * So if you want to find all entities that don't have the uid of 12 you can do:
     * $query->where(["uid !=" => 12])->getAll()
     * This will return all entities, except the entity with uid 12.
     *
     * ATTENTION: It is important that there is NO SPACE between the exclamation mark and the operator.
     * So stuff like: ["uid ! =" => 12] or ["uid ! in" => 12] will NOT WORK!
     *
     * VALUE IN:
     * ----------------------
     * To find multiple entities by their uids do:
     * $query->where(["uid in" => [12,13,25]])->getAll()
     * This will return all entities with a uid of either 12, 13 or 25
     *
     * VALUE GREATER THAN:
     * ----------------------
     * To find all entities with a uid greater than 12 do:
     * $query->where(["uid >" => 12])->getAll()
     *
     * And to find all entities with a uid 12 AND greater do:
     * $query->where(["uid >=" => 12])->getAll()
     *
     * VALUE LESS THAN:
     * ----------------------
     * To find all entities with a uid less than 12 do:
     * $query->where(["uid <" => 12])->getAll()
     *
     * And to find all entities with a uid 12 AND less do:
     * $query->where(["uid <=" => 12])->getAll()
     *
     * VALUE LIKE:
     * ----------------------
     * To find partial values of a string you can use "like".
     * Like allows you to use "%" as placeholder for any other string.
     * $query->where(["myString like" => "%search value%"])->getAll()
     * This will return all entities where myString contains "search value"
     *
     * VALUE HAS:
     * ----------------------
     * When you have multiple entities which are related to each other, you
     * can use "has" to filter on foreign entity fields. So lets say you have a company and a branch office.
     * You are using the branchOffice Repository and the company is related on a "company" field.
     * You want to find all branch offices where the company id is 12; do the following:
     * $query->where(["branch.uid has" => 12])->getAll()
     * This will return all branches that belong to the company with uid 12.
     *
     * @param   array   $query
     * @param   string  $groupName  An optional name of a query group to set this where statement to
     *
     * @return $this
     */
    public function withWhere(array $query, string $groupName = ''): self
    {
        $clone = clone $this;
        
        $orOperator = false;
        if (stripos($groupName, 'or ') === 0) {
            $orOperator = true;
            $groupName = trim(substr($groupName, 3));
        }
        $clone->where[$groupName] = [
            'query' => $query,
            'or' => $orOperator,
        ];
        
        return $clone;
    }
    
    /**
     * Returns the currently configured where query
     *
     * @param   string  $groupName
     *
     * @return array
     */
    public function getWhere(string $groupName = ''): array
    {
        if (stripos($groupName, 'or ') === 0) {
            $groupName = trim(substr($groupName, 3));
        }
        if (! isset($this->where[$groupName])) {
            return [];
        }
        
        return $this->where[$groupName]['query'];
    }
    
    /**
     * Returns all currently set where groups as an array
     *
     * @return array
     */
    public function getWhereGroups(): array
    {
        return $this->where;
    }
    
    /**
     * Can be used to remove a single where group
     *
     * @param   string  $groupName
     *
     * @return $this
     */
    public function removeWhereGroup(string $groupName): self
    {
        unset($this->where[$groupName]);
        
        return $this;
    }
    
    /**
     * Can be used to batch set the where groups of this query
     *
     * @param   array  $groups  Should be an array of "yourGroupName" => [$yourWhereArrayHere]
     *
     * @return $this
     */
    public function withWhereGroups(array $groups): self
    {
        $clone = clone $this;
        $this->where = [];
        foreach ($groups as $groupName => $where) {
            $clone = $clone->withWhere($where, $groupName);
        }
        
        return $clone;
    }
    
    /**
     * Is used to set the number of items per pages, that will be used in the getPage() and getPages()
     * methods of this query object
     *
     * @param   int  $items  The number of items on a single page
     *
     * @return $this
     */
    public function withItemsPerPage(int $items): self
    {
        $clone = clone $this;
        $clone->itemsPerPage = $items;
        
        return $clone;
    }
    
    /**
     * Returns the results of a given, logical page of the current result list.
     * The number of pages depends on the number of items on a page, which can be configured
     * using the setItemsPerPage() method
     *
     * @param   int  $page        The number of the page you want to retrieve. See getPages() for the range of possible
     *                            pages
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getPage(int $page)
    {
        if (empty($this->itemsPerPage)) {
            return $this->getAll();
        }
        $offset = $this->adapter->getOffset();
        $limit = $this->adapter->getLimit();
        $this->adapter->setOffset(max(0, $page * $this->itemsPerPage));
        $this->adapter->setLimit($this->itemsPerPage);
        $result = $this->getAll();
        $this->adapter->setOffset($offset);
        $this->adapter->setLimit($limit);
        
        return $result;
    }
    
    /**
     * Returns the number of logical pages that are in the current result list.
     * The number of pages depends on the number of items on a page, which can be configured
     * using the setItemsPerPage() method
     *
     * @return int
     */
    public function getPages(): int
    {
        if (empty($this->itemsPerPage)) {
            return 1;
        }
        $offset = $this->adapter->getOffset();
        $limit = $this->adapter->getLimit();
        $this->adapter->setOffset(0);
        $this->adapter->setLimit(0);
        $pages = (int)ceil($this->getCount() / $this->itemsPerPage);
        $this->adapter->setOffset($offset);
        $this->adapter->setLimit($limit);
        
        return $pages;
    }
    
    /**
     * Returns the currently used query settings.
     * WARNING: Modifying this object will break things!
     *
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
     */
    public function getSettings(): QuerySettingsInterface
    {
        return $this->adapter->getSettings();
    }
}
