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
 * Last modified: 2020.06.22 at 14:41
 */

declare(strict_types=1);


namespace LaborDigital\T3BA\Tool\Http\Routing\Aspect;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\QueryRestrictionInterface;

class StoragePidQueryRestriction implements QueryRestrictionInterface
{
    /**
     * @var array
     */
    protected $storagePids;
    
    /**
     * StoragePidQueryRestriction constructor.
     *
     * @param   array  $storagePids  The list of numeric pids to limit the query to
     */
    public function __construct($storagePids = [])
    {
        $this->storagePids = $storagePids;
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
        
        // Build constraint list
        $constraints = [];
        $pids = array_map('intval', $this->storagePids);
        foreach ($queriedTables as $tableAlias => $tableName) {
            $constraints[] = $expressionBuilder->in($tableAlias . '.pid', $pids);
        }
        
        return $expressionBuilder->andX(...$constraints);
    }
    
}
