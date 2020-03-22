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
 * Last modified: 2020.03.20 at 18:03
 */

declare(strict_types=1);

namespace LaborDigital\Typo3BetterApi\Domain\BetterQuery;

use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\DoctrineQueryAdapter;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;

class StandaloneBetterQuery extends AbstractBetterQuery {
	/**
	 *
	 * @param string                                                        $tableName
	 * @param \TYPO3\CMS\Core\Database\Query\QueryBuilder                   $queryBuilder
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $settings
	 * @param \LaborDigital\Typo3BetterApi\TypoContext\TypoContext          $typoContext
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Session                $session
	 */
	public function __construct(string $tableName, QueryBuilder $queryBuilder, QuerySettingsInterface $settings,
								TypoContext $typoContext, Session $session) {
		parent::__construct(new DoctrineQueryAdapter($tableName, $queryBuilder, $settings), $typoContext, $session);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getQueryBuilder(): QueryBuilder {
		$this->applyWhere();
		$qb = $this->adapter->getQueryBuilder();
		if ($qb->getType() === \Doctrine\DBAL\Query\QueryBuilder::SELECT)
			BetterQueryTypo3DbQueryParserAdapter::addConstraintsOfSettings(
				$this->adapter->getTableName(), $qb, $this->adapter->getSettings());
		return $qb;
	}
	
	/**
	 * Executes the currently configured query and returns the results
	 *
	 * @return array
	 */
	public function getAll() {
		return $this->getQueryBuilder()->execute()->fetchAll();
	}
	
	/**
	 * Returns the total number of items in the result set, matching the given query parameters
	 * @return int
	 */
	public function getCount(): int {
		return $this->getQueryBuilder()->execute()->rowCount();
	}
	
	/**
	 * Returns the first element from the queries result set that matches your criteria
	 *
	 * @param bool
	 *
	 * @return mixed
	 */
	public function getFirst() {
		return $this->getQueryBuilder()->execute()->fetch();
	}
	
	/**
	 * Executes the query as delete statement
	 * @return \Doctrine\DBAL\Driver\Statement|int
	 */
	public function delete() {
		return $this->getQueryBuilder()->delete($this->adapter->getTableName())->execute();
	}
	
	/**
	 * Executes the query as insert statement
	 *
	 * @param array $values The values to specify for the insert query indexed by column names
	 *
	 * @return \Doctrine\DBAL\Driver\Statement|int
	 */
	public function insert(array $values) {
		return $this->getQueryBuilder()->insert($this->adapter->getTableName())->values($values, TRUE)->execute();
	}
	
	/**
	 * Executes the query as update statement
	 *
	 * @param array $values
	 *
	 * @return \Doctrine\DBAL\Driver\Statement|int
	 */
	public function update(array $values) {
		return $this->getQueryBuilder()->update($this->adapter->getTableName())->values($values, TRUE)->execute();
	}
}



