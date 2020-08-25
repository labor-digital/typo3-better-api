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

interface CachedStackGeneratorInterface
{
    
    /**
     * Should generate the element to be cached out of the given configuration classes
     *
     * The result of this method MUST be serializable!
     *
     * @param   array                    $stack                The collected registration/override stack to iterate
     *                                                         over
     * @param   ExtConfigContext         $context              The current ext config context object
     * @param   array                    $additionalArguments  Additional arguments that may have been passed by the
     *                                                         outside world when the cached value was requested
     * @param   AbstractExtConfigOption  $option               The ext config option object that required this
     *                                                         generator to run
     *
     * @return mixed
     */
    public function generate(array $stack, ExtConfigContext $context, array $additionalArguments, $option);
}
