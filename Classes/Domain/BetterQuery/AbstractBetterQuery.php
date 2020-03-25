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
 * Last modified: 2020.03.20 at 16:10
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;


use Closure;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\AbstractQueryAdapter;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\DoctrineQueryAdapter;
use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\ExtBaseQueryAdapter;
use LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceException;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

abstract class AbstractBetterQuery {
	
	/**
	 * @var AbstractQueryAdapter
	 */
	protected $adapter;
	
	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\Generic\Session
	 */
	protected $session;
	
	/**
	 * @var \LaborDigital\Typo3BetterApi\TypoContext\TypoContext
	 */
	protected $typoContext;
	
	/**
	 * Defines, if set the number of items that are on a logical "page" when using the getPage() or getPages() methods
	 * @var int|null
	 */
	protected $itemsPerPage;
	
	/**
	 * Contains the currently configured where statement
	 * @var array
	 */
	protected $where = [];
	
	
	/**
	 * BetterQuery constructor.
	 *
	 * @param \LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\AbstractQueryAdapter $adapter
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext                         $typoContext
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Session                               $session
	 */
	public function __construct(AbstractQueryAdapter $adapter,
								TypoContext $typoContext,
								Session $session) {
		$this->adapter = $adapter;
		$this->typoContext = $typoContext;
		$this->session = $session;
	}
	
	/**
	 * Clones the children of this query object to keep it immutable
	 */
	public function __clone() {
		$this->adapter = clone $this->adapter;
	}
	
	/**
	 * Returns the configured instance of the query builder for this query
	 * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
	 */
	abstract public function getQueryBuilder(): QueryBuilder;
	
	/**
	 * Executes the currently configured query and returns the results
	 *
	 * @return array
	 */
	abstract public function getAll();
	
	/**
	 * Returns the total number of items in the result set, matching the given query parameters
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
	 * By default the results from ALL pages will be returned.
	 * If you want to limit your result set to a single, or a range of pages you can use this method.
	 *
	 * Keep in mind tho, that the pid limitations apply to all objects you are looking up, even if you
	 * are searching for child properties, the child has to match your pid constraints!
	 *
	 * @param string|array|int|bool $pids The list of pids you want to set as constraints.
	 *                                    Either a single value, or an array of values.
	 *                                    You may use pid selectors using the PidService like (at)pid.storage.something
	 *                                    If you set this to TRUE the current page id will be used.
	 *                                    Setting this to FALSE will remove the pid restrictions
	 *
	 * @return $this
	 */
	public function withPids($pids) {
		$clone = clone $this;
		$settings = $clone->adapter->getSettings();
		
		// Handle special pid values
		if (is_bool($pids)) {
			// True means use current pid
			if ($pids) $pids = $clone->typoContext->getPidAspect()->getCurrentPid();
			// False means reset storage page
			else {
				$settings->setRespectStoragePage(FALSE);
				return $clone;
			}
		}
		
		// Apply possible pid values
		$pids = array_map(function ($v) use ($clone) {
			if (!is_string($v) || !$clone->typoContext->getPidAspect()->hasPid($v)) return $v;
			return $clone->typoContext->getPidAspect()->getPid($v);
		}, $clone->adapter->ensureArrayValue($pids, "pid"));
		
		// Update the page limitations in the query settings
		$settings->setRespectStoragePage(TRUE);
		$settings->setStoragePageIds($pids);
		
		return $clone;
	}
	
	/**
	 * Either returns the list of registered storage page ids or null
	 * if there are either none or the query should not respect storage page ids
	 *
	 * @return array|null
	 */
	public function getPids(): ?array {
		return $this->adapter->getSettings()->getRespectStoragePage() ?
			$this->adapter->getSettings()->getStoragePageIds() : NULL;
	}
	
	/**
	 * Used to set the language restriction for the current query.
	 * By default only records of the currently active language will be returned.
	 *
	 * @param string|int|SiteLanguage|bool $language The language you want to limit your query to.
	 *                                               Can be either the sys_language_uid value of the language,
	 *                                               can also be a Language object returned by the LanguageService.
	 *                                               If this is set to TRUE you can reset the limitations back to the
	 *                                               current language uid If this is set to FALSE you disable all
	 *                                               language constraints
	 * @param bool                         $strict   If you specify a language, all entities for this language, as well
	 *                                               as the default language entities will be returned. If you ONLY
	 *                                               want to retrieve elements for the given language and not the
	 *                                               default language, you can set strict to TRUE.
	 *
	 * @return $this
	 * @throws \LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceException
	 */
	public function withLanguage($language, bool $strict = FALSE) {
		$clone = clone $this;
		$settings = $clone->adapter->getSettings();
		
		// Reset the ext base object cache
		$clone->session->destroy();
		
		// Set language mode
		$settings->setLanguageMode($strict ? "strict" : NULL);
		
		// Handle special language values
		if (is_bool($language)) {
			$settings->setRespectSysLanguage($language);
			if ($language) $settings->setLanguageUid(
				$clone->typoContext->getLanguageAspect()->getCurrentFrontendLanguage()->getLanguageId());
			return $clone;
		}
		
		// Convert language objects
		if (is_object($language) && $language instanceof SiteLanguage) $language = $language->getLanguageId();
		if (is_string($language)) $language = (int)$language;
		if (!is_int($language)) throw new DbServiceException("The given language is invalid! Only integers or objects of " . SiteLanguage::class . " are allowed!");
		$settings->setRespectSysLanguage(TRUE);
		$settings->setLanguageUid($language);
		
		// Done
		return $clone;
	}
	
	/**
	 * Set this to true if you want the query to retrieve hidden / disabled elements as well
	 *
	 * @param bool $state
	 *
	 * @return $this
	 */
	public function withIncludeHidden(bool $state = TRUE) {
		$clone = clone $this;
		$settings = $clone->adapter->getSettings();
		$settings->setIgnoreEnableFields($state);
		if ($state) $settings->setEnableFieldsToBeIgnored(["disabled"]);
		return $clone;
	}
	
	/**
	 * Set this to true if you want the query to retrieve deleted elements as well.
	 *
	 * Attention: If you set this to TRUE you will also receive all hidden records!
	 *
	 * @param bool $state
	 *
	 * @return $this
	 */
	public function withIncludeDeleted(bool $state = TRUE) {
		$clone = clone $this;
		$settings = $clone->adapter->getSettings();
		$settings->setIgnoreEnableFields($state);
		$settings->setIncludeDeleted($state);
		return $clone;
	}
	
	/**
	 * Set the limit of items retrieved by this demand
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function withLimit(int $limit) {
		$clone = clone $this;
		$clone->adapter->setLimit($limit);
		return $clone;
	}
	
	/**
	 * Sets the offset in the database table
	 *
	 * @param int $offset
	 *
	 * @return $this
	 */
	public function withOffset(int $offset) {
		$clone = clone $this;
		$clone->adapter->setOffset($offset);
		return $clone;
	}
	
	/**
	 * Can be used to set the order in which the results will be returned from the database.
	 * Can be either a key / direction pair or an array of key / direction pairs if you want to sort the
	 * results by multiple fields.
	 *
	 * @param string|array $field     Either the field to sort by, or an array of key direction pairs
	 * @param string|null  $direction If $field is a string, this should define the direction.
	 *                                You may use asc | desc | ASC | DESC | QueryInterface::ORDER_ASCENDING
	 *                                | QueryInterface::ORDER_DESCENDING.
	 *                                If $field is an array this parameter is ignored
	 *
	 * @return $this
	 */
	public function withOrder($field, ?string $direction = NULL) {
		$clone = clone $this;
		
		// Unify the input to an array
		if (!is_array($field)) $field = [$field => $direction];
		
		// Build the orderings
		$orderings = [];
		foreach ($field as $k => $direction) {
			// Prepare the direction
			if ($direction === NULL) $direction = "asc";
			$direction = trim(strtolower($direction)) === "asc" ? "ASC" : "DESC";
			// Build the ordering
			$orderings[$k] = $direction;
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
	 * @param array  $query
	 * @param string $groupName An optional name of a query group to set this where statement to
	 *
	 * @return $this
	 */
	public function withWhere(array $query, string $groupName = "") {
		$clone = clone $this;
		
		$orOperator = FALSE;
		if (strtolower(substr($groupName, 0, 3)) === "or ") {
			$orOperator = TRUE;
			$groupName = trim(substr($groupName, 3));
		}
		$clone->where[$groupName] = [
			"query" => $query,
			"or"    => $orOperator,
		];
		return $clone;
	}
	
	/**
	 * Returns the currently configured where query
	 *
	 * @param string $groupName
	 *
	 * @return array
	 */
	public function getWhere(string $groupName = ""): array {
		if (strtolower(substr($groupName, 0, 3)) === "or ") $groupName = trim(substr($groupName, 3));
		if (!isset($this->where[$groupName])) return [];
		return $this->where[$groupName]["query"];
	}
	
	/**
	 * Returns all currently set where groups as an array
	 * @return array
	 */
	public function getWhereGroups(): array {
		return $this->where;
	}
	
	/**
	 * Can be used to remove a single where group
	 *
	 * @param string $groupName
	 *
	 * @return $this
	 */
	public function removeWhereGroup(string $groupName) {
		unset($this->where[$groupName]);
		return $this;
	}
	
	/**
	 * Can be used to batch set the where groups of this query
	 *
	 * @param array $groups Should be an array of "yourGroupName" => [$yourWhereArrayHere]
	 *
	 * @return $this
	 */
	public function withWhereGroups(array $groups) {
		$clone = clone $this;
		$this->where = [];
		foreach ($groups as $groupName => $where)
			$clone = $clone->withWhere($where, $groupName);
		return $clone;
	}
	
	/**
	 * Is used to set the number of items per pages, that will be used in the getPage() and getPages()
	 * methods of this query object
	 *
	 * @param int $items The number of items on a single page
	 *
	 * @return $this
	 */
	public function withItemsPerPage(int $items) {
		$clone = clone $this;
		$clone->itemsPerPage = $items;
		return $clone;
	}
	
	/**
	 * Returns the results of a given, logical page of the current result list.
	 * The number of pages depends on the number of items on a page, which can be configured
	 * using the setItemsPerPage() method
	 *
	 * @param int $page           The number of the page you want to retrieve. See getPages() for the range of possible
	 *                            pages
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
	 */
	public function getPage(int $page) {
		if (empty($this->itemsPerPage)) return $this->getAll();
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
	 * @return int
	 */
	public function getPages(): int {
		if (empty($this->itemsPerPage)) return 1;
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
	 * Internal helper which is used to apply the configured where constraints to the current query object
	 * The result is the completely configured query instance
	 *
	 * @return void
	 */
	protected function applyWhere(): void {
		// Ignore if there is no where set
		if (empty($this->where)) return;
		
		/**
		 * The UID is a special kind of noodle...
		 * If we look up a UID, lets say 3 in the default language everything is fine.
		 * If we look up a UID 3 again, but this time in another language, lets say 2 we will NOT find a instance of said entity,
		 * because, well the entity with UID 3 is linked to sys_language_uid = 0.
		 *
		 * To circumvent that and to make the usage more intuitive we have this wrapper that will either look for a UID of 3
		 * or for all elements that have a transOrigPointerField column that matched our uid. This way we can also resolve all translations of a single entity.
		 *
		 * @param string|int $key
		 * @param \Closure   $constraintGenerator
		 *
		 * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression
		 */
		$uidSpecialConstraintWrapper = function ($key, Closure $constraintGenerator) {
			$c = $constraintGenerator($key);
			
			// Ignore if this is not a uid field
			if (trim($key) !== "uid") return $c;
			
			// Load TCA configuration
			$parentUidField = Arrays::getPath($GLOBALS, ["TCA", $this->adapter->getTableName(), "ctrl", "transOrigPointerField"]);
			
			// Ignore if we don't have a parent uid field configured in the TCA
			if (empty($parentUidField)) return $c;
			
			// Build the constraint
			return $this->adapter->makeOr([
				$constraintGenerator($key),
				$constraintGenerator($parentUidField),
			]);
		};
		
		/**
		 * Internal walker to handle potential recursions inside the query
		 *
		 * @param array $query
		 * @param       $constraintBuilder
		 *
		 * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression
		 */
		$constraintBuilder = function (array $query, $constraintBuilder) use ($uidSpecialConstraintWrapper) {
			$conditions = [];
			
			// Pass 1 - Traverse the list for "OR" statements and separate the chunks
			$chunks = [];
			foreach ($query as $k => $v) {
				// Store everything that is not an or...
				if (!(is_numeric($k) && is_string($v) && strtolower(trim($v)) === "or")) {
					$conditions[$k] = $v;
					continue;
				}
				// Create a new chunk
				if (!empty($conditions)) $chunks[] = $conditions;
				$conditions = [];
			}
			if (!empty($conditions)) $chunks[] = $conditions;
			$conditions = [];
			
			// Check if we have multiple chunks
			if (count($chunks) > 1) {
				// Process the chunks, put them into an or block and return that result
				foreach ($chunks as $k => $chunk)
					$chunks[$k] = $constraintBuilder($chunk, $constraintBuilder);
				return $this->adapter->makeOr($chunks);
			}
			
			$validOperators = [">", "<", "=", ">=", "<=", "in", "like"];
			$extBaseOperators = ["has", "hasany", "hasall"];
			if ($this->adapter instanceof ExtBaseQueryAdapter)
				$validOperators = Arrays::attach($validOperators, ["has", "hasany", "hasall"]);
			
			foreach ($query as $k => $v) {
				if (is_string($k)) {
					$operator = " = ";
					$negated = FALSE;
					
					// Key value pair
					$k = trim($k);
					
					// Check if there is a space in the key
					if (stripos($k, " ")) {
						$kParts = explode(" ", $k);
						$lastKPart = strtolower(trim((string)end($kParts)));
						
						// Check for negation
						if (substr($lastKPart, 0, 1) === "!") {
							$negated = TRUE;
							$lastKPart = substr($lastKPart, 1);
						}
						
						// Check if the operator is valid
						if (!in_array($lastKPart, $validOperators))
							throw new DbServiceException("Invalid operator \"$lastKPart\" for given for: \"$k\"!");
						
						// Valid operator found
						array_pop($kParts);
						$k = trim(implode(" ", $kParts));
						$operator = $lastKPart;
					}
					
					// Handle operators
					if (!in_array($operator, $extBaseOperators))
						$condition = $uidSpecialConstraintWrapper($k, function ($k) use ($operator, $v, $negated) {
							return $this->adapter->makeCondition($operator, $k, $v, $negated);
						});
					else $condition = $this->adapter->makeCondition($operator, $k, $v, $negated);
					
					// Done
					$conditions[] = $condition;
					
				} else {
					// Special value detected
					// Check if there is a closure for advanced helpers
					if (is_callable($v)) {
						$q = NULL;
						if ($this->adapter instanceof DoctrineQueryAdapter) $q = $this->adapter->getQueryBuilder();
						else $q = $this->adapter->getQuery();
						$conditions[] = call_user_func($v, $q, $k, $this);
					} // Check if there is an array -> means an "AND"
					else if (is_array($v))
						$conditions[] = $constraintBuilder($v, $constraintBuilder);
					else continue;
				}
			}
			
			// Combine the conditions
			if (empty($conditions)) throw new BetterQueryException("Failed to convert the query into a constraint! The given query was: " . json_encode($query));
			return $this->adapter->makeAnd($conditions);
		};
		// Run the constraint builder recursively
		$whereGroups = ["and" => [], "or" => []];
		foreach ($this->where as $whereGroup => $where) {
			if (!empty($where["query"]))
				$whereGroups[$where["or"] ? "or" : "and"][] = $constraintBuilder($where["query"], $constraintBuilder);
		}
		
		// Add "AND" to constraints
		$constraints = [];
		if (count($whereGroups["and"]) > 1) $constraints = $this->adapter->makeAnd($whereGroups["and"]);
		else if (!empty($whereGroups["and"])) $constraints = reset($whereGroups["and"]);
		
		// Add "OR" to constraints
		$orConstraints = [];
		if (!empty($whereGroups["or"])) $orConstraints = $whereGroups["or"];
		if (!empty($constraints)) array_unshift($orConstraints, $constraints);
		if (count($orConstraints) > 1) $constraints = $this->adapter->makeOr($orConstraints);
		
		// Finalize the query object
		$this->adapter->finalizeConstraints($constraints);
	}
}