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

use LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceException;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;
use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

abstract class AbstractQueryAdapter
{
    /**
     * @var string
     */
    protected $tableName;
    
    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
     */
    protected $settings;
    
    public function __construct(string $tableName, QuerySettingsInterface $settings)
    {
        $this->tableName = $tableName;
        $this->settings = $settings;
        
        // Reset the settings
        $this->settings->setRespectStoragePage(false);
        $this->settings->setRespectSysLanguage(true);
    }
    
    /**
     * Clones the children of this query object to keep it immutable
     */
    public function __clone()
    {
        $this->settings = clone $this->settings;
    }
    
    /**
     * Returns the name of the table
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Returns the Query settings object
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface
     */
    public function getSettings(): QuerySettingsInterface
    {
        return $this->settings;
    }
    
    /**
     * Sets the max items in the result
     *
     * @param int $limit
     */
    abstract public function setLimit(int $limit): void;
    
    /**
     * Returns the max items in the result
     * @return int
     */
    abstract public function getLimit(): int;
    
    /**
     * Sets the offset to the first result
     *
     * @param int $offset
     */
    abstract public function setOffset(int $offset): void;
    
    /**
     * Returns the offset to the first result
     * @return int
     */
    abstract public function getOffset(): int;
    
    /**
     * Sets the order fields as $field => $direction
     *
     * @param array $orderings
     *
     * @return mixed
     */
    abstract public function setOrderings(array $orderings);
    
    /**
     * Returns the query object instance
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    abstract public function getQuery(): QueryInterface;
    
    /**
     * Returns the query builder instance
     *
     * @return \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    abstract public function getQueryBuilder(): QueryBuilder;
    
    /**
     * Returns a prepared or conditional for the current implementation
     *
     * @param array $list
     *
     * @return mixed
     */
    abstract public function makeOr(array $list);
    
    /**
     * Returns a prepared and conditional for the current implementation
     *
     * @param array $list
     *
     * @return mixed
     */
    abstract public function makeAnd(array $list);
    
    /**
     * Returns a prepared condition for the current implementation
     *
     * @param string $operator Something like ">=", "<" or "=" to define which condition should be build
     * @param string $key      The field name in the database the query should be build with
     * @param mixed  $value    The value that should be used to build the condition
     * @param bool   $negated  True if the value should be negated, false if not
     *
     * @return mixed
     */
    abstract public function makeCondition(string $operator, $key, $value, bool $negated);
    
    /**
     * Injects the build constraints into the query implementation
     *
     * @param $constraints
     *
     * @return mixed
     */
    abstract public function finalizeConstraints($constraints): void;
    
    /**
     * An internal helper which is used to make sure some fields either receive an array of numbers,
     * or at least a comma separated string or a single number. It will then make sure that the resulting
     * value is an array or throw an exceptions
     *
     * @param array|string|int $value The value to validate
     * @param string           $field The name of the validated field for the speaking exception
     *
     * @return array
     * @throws \LaborDigital\Typo3BetterApi\Domain\DbService\DbServiceException
     */
    public function ensureArrayValue($value, string $field): array
    {
        if ((!is_array($value))) {
            if ((is_string($value) || is_numeric($value))) {
                $value = Arrays::makeFromStringList($value);
            } elseif ($value instanceof ObjectStorage) {
                $value = $value->toArray();
            } elseif ($value instanceof LazyObjectStorage) {
                $value = $value->toArray();
            } else {
                throw new DbServiceException("Invalid value for \"$field\" given! Only strings, numbers or arrays are allowed!");
            }
        }
        return $value;
    }
}
