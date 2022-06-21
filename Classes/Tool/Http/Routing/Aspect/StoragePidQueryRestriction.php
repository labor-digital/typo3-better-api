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


namespace LaborDigital\T3ba\Tool\Http\Routing\Aspect;

use LaborDigital\T3ba\Tool\Database\BetterQuery\Util\RecursivePidResolver;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class StoragePidQueryRestriction implements QueryRestrictionInterface
{
    /**
     * @var array
     */
    protected $storagePids;
    protected $recursion;
    
    /**
     * StoragePidQueryRestriction constructor.
     *
     * @param   array  $storagePids  The list of numeric pids to limit the query to
     * @param   int    $recursion    Defines if, and how many nested pids should be used recursively
     */
    public function __construct($storagePids = [], int $recursion = 0)
    {
        $this->storagePids = $storagePids;
        $this->recursion = $recursion;
    }
    
    /**
     * @inheritDoc
     */
    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        // Ignore if there are no configured storage pids
        if (empty($this->storagePids)) {
            return $expressionBuilder->andX();
        }
        
        $pids = array_map('intval', $this->storagePids);
        
        if ($this->recursion > 0) {
            $pids = GeneralUtility::makeInstance(RecursivePidResolver::class)->resolve($pids, $this->recursion);
        }
        
        $constraints = [];
        foreach ($queriedTables as $tableAlias => $tableName) {
            $constraints[] = $expressionBuilder->in($tableAlias . '.pid', $pids);
        }
        
        return $expressionBuilder->andX(...$constraints);
    }
    
}
