<?php
/*
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
 * Last modified: 2020.08.23 at 23:23
 */

declare(strict_types=1);

namespace LaborDigital\T3BA\Tool\Database\BetterQuery\Standalone;

use Doctrine\DBAL\Connection;
use LaborDigital\T3BA\Core\Exception\NotImplementedException;
use LaborDigital\T3BA\Tool\Database\BetterQuery\AbstractQueryAdapter;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class DoctrineQueryAdapter extends AbstractQueryAdapter
{

    /**
     * @var \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * DoctrineQueryAdapter constructor.
     *
     * @param   string                                                         $tableName
     * @param   \TYPO3\CMS\Core\Database\Query\QueryBuilder                    $queryBuilder
     * @param   \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface  $settings
     */
    public function __construct(string $tableName, QueryBuilder $queryBuilder, QuerySettingsInterface $settings)
    {
        parent::__construct($tableName, $settings);
        $this->queryBuilder = $queryBuilder;

        // Reset query builder
        $queryBuilder->select('*');
        $queryBuilder->getRestrictions()->removeAll();
    }

    /**
     * Clones the children of this query object to keep it immutable
     */
    public function __clone()
    {
        parent::__clone();
        $this->queryBuilder = clone $this->queryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function setLimit(int $limit): void
    {
        $this->queryBuilder->setMaxResults($limit);
    }

    /**
     * @inheritDoc
     */
    public function getLimit(): int
    {
        return $this->queryBuilder->getMaxResults();
    }

    /**
     * @inheritDoc
     */
    public function setOffset(int $offset): void
    {
        $this->queryBuilder->setFirstResult($offset);
    }

    /**
     * @inheritDoc
     */
    public function getOffset(): int
    {
        return $this->queryBuilder->getFirstResult();
    }

    /**
     * @inheritDoc
     */
    public function setOrderings(array $orderings): void
    {
        $this->queryBuilder->resetQueryPart('orderBy');
        foreach ($orderings as $k => $v) {
            $this->queryBuilder->addOrderBy($k, $v);
        }
    }

    /**
     * @inheritDoc
     * @throws \LaborDigital\T3BA\Core\Exception\NotImplementedException
     */
    public function getQuery(): QueryInterface
    {
        throw new NotImplementedException('There is no underlying "query" for a doctrine BetterQuery! Use getQueryBuilder() instead!');
    }

    /**
     * @inheritDoc
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return clone $this->queryBuilder;
    }

    /**
     * @inheritDoc
     */
    public function makeOr(array $list)
    {
        return $this->queryBuilder->expr()->orX(...$list);
    }

    /**
     * @inheritDoc
     */
    public function makeAnd(array $list)
    {
        return $this->queryBuilder->expr()->andX(...$list);
    }

    /**
     * @inheritDoc
     */
    public function makeCondition(string $operator, $key, $value, bool $negated)
    {
        $qb = $this->queryBuilder;
        switch ($operator) {
            case 'like':
                if ($negated) {
                    return $qb->expr()
                              ->notLike($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->like($key, $qb->createNamedParameter($value));
            case 'in':
                if ($negated) {
                    return $qb->expr()->notIn(
                        $key,
                        $qb->createNamedParameter(
                            $this->ensureArrayValue($value, $key),
                            Connection::PARAM_STR_ARRAY
                        )
                    );
                }

                return $qb->expr()->in(
                    $key,
                    $qb->createNamedParameter(
                        $this->ensureArrayValue($value, $key),
                        Connection::PARAM_STR_ARRAY
                    )
                );
            case '>':
                if ($negated) {
                    return $qb->expr()->lte($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->gt($key, $qb->createNamedParameter($value));
            case '>=':
                if ($negated) {
                    return $qb->expr()->lt($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->gte($key, $qb->createNamedParameter($value));
            case '<':
                if ($negated) {
                    return $qb->expr()->gte($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->lt($key, $qb->createNamedParameter($value));
            case '<=':
                if ($negated) {
                    return $qb->expr()->gt($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->lte($key, $qb->createNamedParameter($value));
            default:
                if ($negated) {
                    return $qb->expr()->neq($key, $qb->createNamedParameter($value));
                }

                return $qb->expr()->eq($key, $qb->createNamedParameter($value));
        }
    }

    /**
     * @inheritDoc
     */
    public function finalizeConstraints($constraints): void
    {
        $this->queryBuilder->where($constraints);
    }
}
