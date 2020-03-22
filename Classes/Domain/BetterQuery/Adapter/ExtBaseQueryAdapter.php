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
 * Last modified: 2020.03.20 at 16:16
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter;


use LaborDigital\Typo3BetterApi\NotImplementedException;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class ExtBaseQueryAdapter extends AbstractQueryAdapter {
	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	protected $query;
	
	/**
	 * ExtBaseQueryAdapter constructor.
	 *
	 * @param string                                        $tableName
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 */
	public function __construct(string $tableName, QueryInterface $query) {
		parent::__construct($tableName, $query->getQuerySettings());
		$this->query = $query;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __clone() {
		parent::__clone();
		$this->query = clone $this->query;
		$this->query->setQuerySettings($this->settings);
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLimit(int $limit): void {
		if ($limit === 0) $this->query->unsetLimit();
		else $this->query->setLimit($limit);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLimit(): int {
		return $this->query->getLimit();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setOffset(int $offset): void {
		$this->query->setOffset($offset);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getOffset(): int {
		return $this->query->getOffset();
	}
	
	/**
	 * @inheritDoc
	 */
	public function setOrderings(array $orderings) {
		$this->query->setOrderings($orderings);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getQuery(): QueryInterface {
		return clone $this->query;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getQueryBuilder(): QueryBuilder {
		throw new NotImplementedException();
	}
	
	/**
	 * @inheritDoc
	 */
	public function makeOr(array $list) {
		return $this->query->logicalOr($list);
	}
	
	/**
	 * @inheritDoc
	 */
	public function makeAnd(array $list) {
		return $this->query->logicalAnd($list);
	}
	
	/**
	 * @inheritDoc
	 */
	public function makeCondition(string $operator, $key, $value, bool $negated) {
		switch ($operator) {
			case "has":
				$condition = $this->query->contains($key, $value);
				break;
			case "hasany":
			case "hasall":
				$list = [];
				foreach ($this->ensureArrayValue($value, $key) as $val) $list[] = $this->query->contains($key, $val);
				$condition = $operator === "hasall" ? $this->query->logicalAnd($list) : $this->query->logicalOr($list);
				break;
			case "like":
				$condition = $this->query->like($key, $value);
				break;
			case "in":
				$condition = $this->query->in($key, $this->ensureArrayValue($value, $key));
				break;
			case ">":
				$condition = $this->query->greaterThan($key, $value);
				break;
			case ">=":
				$condition = $this->query->greaterThanOrEqual($key, $value);
				break;
			case "<":
				$condition = $this->query->lessThan($key, $value);
				break;
			case "<=":
				$condition = $this->query->lessThanOrEqual($key, $value);
				break;
			default:
				$condition = $this->query->equals($key, $value);
				break;
		}
		
		// Handle negation
		if ($negated) $condition = $this->query->logicalNot($condition);
		
		return $condition;
	}
	
	/**
	 * @inheritDoc
	 */
	public function finalizeConstraints($constraints): void {
		$this->query->matching($constraints);
	}
}