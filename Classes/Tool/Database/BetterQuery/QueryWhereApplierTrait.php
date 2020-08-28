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
 * Last modified: 2020.08.28 at 11:13
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Database\BetterQuery;

use Closure;
use LaborDigital\T3BA\Tool\Database\BetterQuery\ExtBase\ExtBaseQueryAdapter;
use LaborDigital\T3BA\Tool\Database\BetterQuery\Standalone\DoctrineQueryAdapter;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;

/**
 * Trait QueryWhereApplierTrait
 *
 * Internal helper for the better query instances to convert the array based "where" constraints
 * to doctrine / extbase constraints.
 *
 * @package LaborDigital\T3BA\Tool\Database\BetterQuery
 * @internal
 * @see     \LaborDigital\T3BA\Tool\Database\BetterQuery\AbstractBetterQuery
 */
trait QueryWhereApplierTrait
{
    /**
     * The UID is a special kind of noodle...
     * If we look up a UID, lets say 3 in the default language everything is fine.
     * If we look up a UID 3 again, but this time in another language, lets say 2 we will NOT find a instance of said
     * entity, because, well the entity with UID 3 is linked to sys_language_uid = 0.
     *
     * To circumvent that and to make the usage more intuitive we have this wrapper that will either look for a UID of
     * 3 or for all elements that have a transOrigPointerField column that matched our uid. This way we can also resolve
     * all translations of a single entity.
     *
     * @param   string|int            $key
     * @param   \Closure              $constraintGenerator
     * @param   AbstractQueryAdapter  $adapter
     *
     * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression
     */
    protected function whereUidSpecialConstraintWrapper(
        $key,
        Closure $constraintGenerator,
        AbstractQueryAdapter $adapter
    ): CompositeExpression {
        // Load TCA configuration
        $parentUidField = Arrays::getPath(
            $GLOBALS,
            ['TCA', $adapter->getTableName(), 'ctrl', 'transOrigPointerField']
        );

        // Ignore if we don't have a parent uid field configured in the TCA
        if (empty($parentUidField)) {
            return $constraintGenerator($key);
        }

        // Build the constraint
        return $adapter->makeOr([
            $constraintGenerator($key),
            $constraintGenerator($parentUidField),
        ]);
    }

    /**
     * Internal walker to handle potential recursions inside the query
     *
     * @param   array                 $query
     * @param   AbstractQueryAdapter  $adapter
     *
     * @return \TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression
     * @throws \LaborDigital\T3BA\Tool\Database\BetterQuery\BetterQueryException
     */
    protected function whereConstraintBuilder(array $query, AbstractQueryAdapter $adapter): CompositeExpression
    {
        $conditions = [];

        // Pass 1 - Traverse the list for "OR" statements and separate the chunks
        $chunks = [];
        foreach ($query as $k => $v) {
            // Store everything that is not an or...
            if (! (is_numeric($k) && is_string($v) && strtolower(trim($v)) === 'or')) {
                $conditions[$k] = $v;
                continue;
            }
            // Create a new chunk
            if (! empty($conditions)) {
                $chunks[] = $conditions;
            }
            $conditions = [];
        }
        if (! empty($conditions)) {
            $chunks[] = $conditions;
        }
        $conditions = [];

        // Check if we have multiple chunks
        if (count($chunks) > 1) {
            // Process the chunks, put them into an or block and return that result
            foreach ($chunks as $k => $chunk) {
                $chunks[$k] = $this->whereConstraintBuilder($chunk, $adapter);
            }

            return $adapter->makeOr($chunks);
        }

        $validOperators   = ['>', '<', '=', '>=', '<=', 'in', 'like'];
        $extBaseOperators = ['has', 'hasany', 'hasall'];
        if ($adapter instanceof ExtBaseQueryAdapter) {
            $validOperators = Arrays::attach($validOperators, ['has', 'hasany', 'hasall']);
        }

        foreach ($query as $k => $v) {
            if (is_string($k)) {
                $operator = ' = ';
                $negated  = false;

                // Key value pair
                $k = trim($k);

                // Check if there is a space in the key
                if (strpos($k, ' ') !== false) {
                    $kParts    = explode(' ', $k);
                    $lastKPart = strtolower(trim((string)end($kParts)));

                    // Check for negation
                    if (! empty($lastKPart) && $lastKPart[0] === '!') {
                        $negated   = true;
                        $lastKPart = substr($lastKPart, 1);
                    }

                    // Check if the operator is valid
                    if (! in_array($lastKPart, $validOperators, true)) {
                        throw new BetterQueryException(
                            'Invalid operator "' . $lastKPart . '" for given for: "' . $k . '"!');
                    }

                    // Valid operator found
                    array_pop($kParts);
                    $k        = trim(implode(' ', $kParts));
                    $operator = $lastKPart;
                }

                // Handle operators
                if ($k === 'uid' && ! in_array($operator, $extBaseOperators, true)) {
                    $condition = $this->whereUidSpecialConstraintWrapper(
                        $k,
                        static function ($realKey) use ($operator, $adapter, $v, $negated) {
                            return $adapter->makeCondition($operator, $realKey, $v, $negated);
                        },
                        $adapter
                    );
                } else {
                    $condition = $adapter->makeCondition($operator, $k, $v, $negated);
                }

                // Done
                $conditions[] = $condition;
            } else {
                // Special value detected
                // Check if there is a closure for advanced helpers
                if (is_callable($v)) {
                    $q = null;
                    if ($adapter instanceof DoctrineQueryAdapter) {
                        $q = $adapter->getQueryBuilder();
                    } else {
                        $q = $adapter->getQuery();
                    }
                    $conditions[] = call_user_func($v, $q, $k, $this);
                } // Check if there is an array -> means an "AND"
                elseif (is_array($v)) {
                    $conditions[] = $this->whereConstraintBuilder($v, $adapter);
                }
            }
        }

        // Combine the conditions
        if (empty($conditions)) {
            throw new BetterQueryException('Failed to convert the query into a constraint! The given query was: '
                                           . json_encode($query));
        }

        return $adapter->makeAnd($conditions);
    }


    /**
     * Internal helper which is used to apply the configured where constraints to the current query object
     * The result is the completely configured query instance
     *
     * @param   \LaborDigital\T3BA\Tool\Database\BetterQuery\AbstractQueryAdapter  $adapter
     *
     * @return void
     * @throws \LaborDigital\T3BA\Tool\Database\BetterQuery\BetterQueryException
     */
    protected function applyWhere(AbstractQueryAdapter $adapter): void
    {
        // Ignore if there is no where set
        if (empty($this->where)) {
            return;
        }

        // Run the constraint builder recursively
        $whereGroups = ['and' => [], 'or' => []];
        foreach ($this->where as $whereGroup => $where) {
            if (! empty($where['query'])) {
                $whereGroups[$where['or'] ? 'or' : 'and'][]
                    = $this->whereConstraintBuilder($where['query'], $adapter);
            }
        }

        // Add "AND" to constraints
        $constraints = [];
        if (count($whereGroups['and']) > 1) {
            $constraints = $adapter->makeAnd($whereGroups['and']);
        } elseif (! empty($whereGroups['and'])) {
            $constraints = reset($whereGroups['and']);
        }

        // Add "OR" to constraints
        $orConstraints = [];
        if (! empty($whereGroups['or'])) {
            $orConstraints = $whereGroups['or'];
        }
        if (! empty($constraints)) {
            array_unshift($orConstraints, $constraints);
        }
        if (count($orConstraints) > 1) {
            $constraints = $adapter->makeOr($orConstraints);
        }

        // Finalize the query object
        $adapter->finalizeConstraints($constraints);
    }
}
