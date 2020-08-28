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
 * Last modified: 2020.03.20 at 15:45
 */

namespace LaborDigital\T3BA\Tool\Database\BetterQuery\ExtBase;

use LaborDigital\Typo3BetterApi\Domain\BetterQuery\Adapter\ExtBaseQueryAdapter;
use LaborDigital\Typo3BetterApi\Domain\ExtendedRelation\ExtendedRelationQueryResult;
use LaborDigital\Typo3BetterApi\Domain\Repository\BetterRepository;
use LaborDigital\Typo3BetterApi\TypoContext\TypoContext;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\Session;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class BetterQuery
 *
 * NOTE: This class is immutable! If you extend it keep in mind that your extension should be immutable as well!
 *
 * @package LaborDigital\Typo3BetterApi\Domain\BetterQuery
 */
class BetterQuery extends AbstractBetterQuery
{

    /**
     * @var \LaborDigital\Typo3BetterApi\Domain\Repository\BetterRepository
     */
    protected $repository;

    /**
     * The configuration for the inclusion of hidden children using the extended relation service
     *
     * @var mixed
     */
    protected $includeHiddenChildren;

    /**
     * The configuration for the inclusion of deleted children using the extended relation service
     *
     * @var mixed
     */
    protected $includeDeletedChildren;

    /**
     * @inheritDoc
     */
    public function __construct(
        BetterRepository $repository,
        QueryInterface $query,
        TypoContext $typoContext,
        Session $session
    ) {
        parent::__construct(
            new ExtBaseQueryAdapter($repository->getTableName(), $query),
            $typoContext,
            $session
        );
        $this->repository = $repository;
    }

    /**
     * This method can be used to include hidden child-relations in the resolved query result.
     *
     * @param   bool  $settings  Defines which children to include in the result
     *                           - TRUE: Include all hidden children in all entities
     *                           - FALSE: Go back to the default behaviour
     *                           - \Entity\Class\Name: Allow hidden children for all properties of a given entity class
     *                           - [\Entity\Class\Name, \Entity\Class\AnotherName]: Allow hidden children for all
     *                           properties of multiple entity classes
     *                           - [\Entity\Class\Name => "property", \Entity\Class\AnotherName => ["property", "foo"]:
     *                           Allow hidden children for either a single property or a list of properties
     *
     * @return \LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery
     */
    public function withIncludeHiddenChildren($settings = true): BetterQuery
    {
        $clone = clone $this;
        if (empty($settings)) {
            $clone->includeHiddenChildren = null;
        } else {
            $clone->includeHiddenChildren = $settings;
        }

        return $clone;
    }

    /**
     * This method can be used to include deleted child-relations in the resolved query result.
     *
     * @param   bool  $settings  Defines which children to include in the result
     *                           - TRUE: Include all deleted children in all entities
     *                           - FALSE: Go back to the default behaviour
     *                           - \Entity\Class\Name: Allow deleted children for all properties of a given entity class
     *                           - [\Entity\Class\Name, \Entity\Class\AnotherName]: Allow deleted children for all
     *                           properties of multiple entity classes
     *                           - [\Entity\Class\Name => "property", \Entity\Class\AnotherName => ["property", "foo"]:
     *                           Allow deleted children for either a single property or a list of properties
     *
     * @return \LaborDigital\Typo3BetterApi\Domain\BetterQuery\BetterQuery
     */
    public function withIncludeDeletedChildren($settings = true): BetterQuery
    {
        $clone = clone $this;
        if (empty($settings)) {
            $clone->includeDeletedChildren = null;
        } else {
            $clone->includeDeletedChildren = $settings;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return BetterQueryTypo3DbQueryParserAdapter::getConcreteQueryParser()
                                                   ->convertQueryToDoctrineQueryBuilder($this->getQuery());
    }

    /**
     * Returns the preconfigured query object.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        $orgAdapter    = $this->adapter;
        $this->adapter = $clone = clone $orgAdapter;
        $this->applyWhere();
        $this->adapter = $orgAdapter;

        return $clone->getQuery();
    }

    /**
     * Executes the currently configured query and returns the results
     *
     * @param   bool  $returnAsArray  If set to true the method will return the raw database arrays instead of the
     *                                extBase objects. NOTE: If you set this to true there will be no relation
     *                                resolving!
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function getAll(bool $returnAsArray = false)
    {
        // Check if we have to apply an advanced relation lookup
        if (! $returnAsArray && ! empty($this->includeHiddenChildren) || ! empty($this->includeDeletedChildren)) {
            return ExtendedRelationQueryResult::makeInstance($this->getQuery()->execute($returnAsArray), [
                'hidden'  => $this->includeHiddenChildren,
                'deleted' => $this->includeDeletedChildren,
            ]);
        }

        // Perform a normal query
        return $this->getQuery()->execute($returnAsArray);
    }

    /**
     * Returns the first element from the queries result set that matches your criteria
     *
     * @param   bool  $returnAsArray
     *
     * @return mixed|\TYPO3\CMS\Extbase\DomainObject\AbstractEntity|object
     */
    public function getFirst(bool $returnAsArray = false)
    {
        return $returnAsArray ? reset($this->getAll(true)) : $this->getAll(false)->getFirst();
    }

    /**
     * @inheritDoc
     */
    public function getCount(): int
    {
        return $this->getQuery()->count();
    }
}
