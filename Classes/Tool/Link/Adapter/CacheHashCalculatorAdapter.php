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


namespace LaborDigital\T3ba\Tool\Link\Adapter;

use LaborDigital\T3ba\Core\Di\NoDiInterface;
use Neunerlei\Arrays\Arrays;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

/**
 * @deprecated this will be removed in v11, as it does not have the desired effect anymore
 */
class CacheHashCalculatorAdapter extends CacheHashCalculator implements NoDiInterface
{
    /**
     * Returns the globally used cache hash calculator instance
     *
     * @return \TYPO3\CMS\Frontend\Page\CacheHashCalculator
     */
    public static function getGlobalCalculator(): CacheHashCalculator
    {
        return GeneralUtility::makeInstance(CacheHashCalculator::class);
    }
    
    /**
     * Executes a given $callback, with a given cacheHash $configuration applied to the chash calculator
     *
     * @param   array                     $configuration
     * @param   callable                  $callback
     * @param   CacheHashCalculator|null  $calculator
     *
     * @return mixed
     */
    public static function runWithConfiguration(array $configuration, callable $callback, ?CacheHashCalculator $calculator = null)
    {
        $calculator = $calculator ?? static::getGlobalCalculator();
        $configurationBackup = $calculator->configuration;
        
        try {
            $calculator->setConfiguration(
                Arrays::merge(
                    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash'] ?? [],
                    $configuration
                )
            );
            
            return $callback();
            
        } finally {
            $calculator->configuration = $configurationBackup;
        }
    }
    
}
