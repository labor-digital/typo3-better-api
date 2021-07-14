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
 * Last modified: 2021.07.13 at 19:17
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\CoreModding\ClassOverrides;

use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\BetterApiClassOverrideCopy__HiddenRestriction;

class ExtendedHiddenRestriction extends BetterApiClassOverrideCopy__HiddenRestriction
{
    /**
     * For the sake of performance I don't use the event system here.
     * This is the "API" to inject additional hooks that allow the outside world to filter the
     * queried tables or expression builder before the actual restriction.
     * If the callback returns a CompositeExpression object, it is returned immediately
     *
     * @var callable[]
     */
    public static $hooks = [];

    /**
     * @inheritDoc
     */
    public function buildExpression(array $queriedTables, ExpressionBuilder $expressionBuilder): CompositeExpression
    {
        foreach (static::$hooks as $hook) {
            if (($r = $hook($queriedTables, $expressionBuilder)) instanceof CompositeExpression) {
                return $r;
            }
        }

        return parent::buildExpression($queriedTables, $expressionBuilder);
    }

}
