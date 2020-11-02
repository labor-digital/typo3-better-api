<?php
declare(strict_types=1);
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
 * Last modified: 2020.03.19 at 02:52
 */

namespace LaborDigital\Typo3BetterApi\BackendForms\Abstracts;

use LaborDigital\Typo3BetterApi\BackendForms\BackendFormException;
use Neunerlei\Arrays\Arrays;

trait DisplayConditionTrait
{

    /**
     * Sets the display condition for the current column
     *
     * Special feature: If you give an array like ["fieldName", "=" , "0"], the logic will automatically
     * convert it into the internal format like: "FIELD:fieldName:=:0"
     *
     * Auto-And: If you apply multiple arrays like [["fieldName", "=" , "0"],["fieldName", "=" , "2"]]
     * the values will be combined using the "AND" conditional
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#displaycond
     *
     * @param   string|array  $condition
     *
     * @return $this
     * @throws \LaborDigital\Typo3BetterApi\BackendForms\BackendFormException
     */
    public function setDisplayCondition($condition): self
    {
        if (empty($condition)) {
            return $this;
        }
        if (is_array($condition)) {
            if (! Arrays::isAssociative($condition)) {
                $fieldProcessor = static function ($condition) {
                    if (count($condition) === 3 && Arrays::isSequential($condition)) {
                        $condition = 'FIELD:' . $condition[0] . ':' . $condition[1] . ':' . $condition[2];
                    }

                    return $condition;
                };
                if (Arrays::isArrayList($condition) && Arrays::isSequential($condition)) {
                    $condition = [
                        'AND' => array_map($fieldProcessor, $condition),
                    ];
                } else {
                    $condition = $fieldProcessor($condition);
                }
            }
        } elseif (! is_string($condition)) {
            throw new BackendFormException('Only strings and arrays are allowed as display conditions!');
        }
        $this->config['displayCond'] = $condition;

        return $this;
    }

    /**
     * Returns the currently configured display condition, or null
     *
     * @see https://docs.typo3.org/m/typo3/reference-tca/master/en-us/Columns/Index.html#displaycond
     *
     * @return array|string
     */
    public function getDisplayCondition()
    {
        return isset($this->config['displayCond']) ? $this->config['displayCond'] : '';
    }
}
