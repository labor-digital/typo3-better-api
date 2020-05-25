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
 * Last modified: 2020.03.16 at 18:42
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;

interface CachedValueGeneratorInterface
{
    
    /**
     * Should generate the value to be cached out of the given data array
     *
     * The result of this method MUST be serializable!
     *
     * @param array                   $data           The collected data that should be transformed into a cached
     *                                                value.
     * @param ExtConfigContext        $context        The current ext config context object
     * @param AbstractExtConfigOption $option         The ext config option object that required this generator to run
     * @param array                   $additionalData Optional data data may have been passed when
     *                                                getCachedValueOrRun() was called
     *
     * @return mixed
     */
    public function generate(array $data, ExtConfigContext $context, array $additionalData, $option);
}
