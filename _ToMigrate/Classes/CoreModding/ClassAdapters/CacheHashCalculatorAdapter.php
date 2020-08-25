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
 * Last modified: 2020.06.18 at 14:18
 */

declare(strict_types=1);


namespace LaborDigital\Typo3BetterApi\CoreModding\ClassAdapters;


use LaborDigital\Typo3BetterApi\Container\TypoContainer;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

class CacheHashCalculatorAdapter extends CacheHashCalculator
{
    /**
     * Returns the globally used cache hash calculator instance
     *
     * @return \TYPO3\CMS\Frontend\Page\CacheHashCalculator
     */
    public static function getGlobalCalculator(): CacheHashCalculator
    {
        return TypoContainer::getInstance()->get(CacheHashCalculator::class);
    }
    
    /**
     * Returns the list of excluded parameters for a cache hash calculator instance
     *
     * @param   \TYPO3\CMS\Frontend\Page\CacheHashCalculator  $calculator
     *
     * @return array
     */
    public static function getExcludedParameters(CacheHashCalculator $calculator): array
    {
        return $calculator->excludedParameters;
    }
    
    /**
     * Updates the list of excluded parameters for a given calculator instance
     *
     * @param   \TYPO3\CMS\Frontend\Page\CacheHashCalculator  $calculator
     * @param   array                                         $excluded
     */
    public static function updateExcludedParameters(CacheHashCalculator $calculator, array $excluded): void
    {
        $calculator->setExcludedParameters($excluded);
    }
}
