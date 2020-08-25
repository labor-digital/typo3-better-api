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

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\LinkAndPid\Pids;

use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigContext;
use LaborDigital\Typo3BetterApi\ExtConfig\ExtConfigException;
use LaborDigital\Typo3BetterApi\ExtConfig\Option\CachedStackGeneratorInterface;

class PidGenerator implements CachedStackGeneratorInterface
{
    /**
     * @inheritDoc
     */
    public function generate(array $stack, ExtConfigContext $context, array $additionalData, $option)
    {
        // Skip if there is nothing to do
        if (empty($stack['main'])) {
            return [];
        }
        
        // Create the collector
        $collector = $context->getInstanceOf(PidCollector::class);
        
        // Loop through the stack
        $context->runWithCachedValueDataScope($stack['main'],
            function (string $configClass) use ($collector, $context) {
                if (! in_array(PidConfigurationInterface::class, class_implements($configClass))) {
                    throw new ExtConfigException("Invalid pid config class $configClass given. It has to implement the correct interface: "
                                                 . PidConfigurationInterface::class);
                }
                call_user_func([$configClass, 'configurePids'], $collector, $context);
            });
        
        // Done
        return $collector->getAll();
    }
}
