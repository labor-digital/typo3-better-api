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
 * Last modified: 2020.03.18 at 19:37
 */

namespace LaborDigital\Typo3BetterApi\ExtConfig\Option\ExtBase\Generic;

use Iterator;

abstract class AbstractConfigGenerator
{
    /**
     * Internal helper to build the typoscript, template definition for a extbase plugin/module
     *
     * @param string                      $type
     * @param AbstractElementConfigurator $configurator
     *
     * @return string
     */
    protected function makeTemplateDefinition(string $type, AbstractElementConfigurator $configurator): string
    {
        
        // Template path helper
        $pathHelper = function (Iterator $stack): string {
            $paths = [];
            foreach ($stack as $k => $path) {
                $paths[$path] = (((int)$k) * 10 + 10) . ' = ' . $path;
            }
            return implode(PHP_EOL . '					', array_reverse($paths));
        };
        
        // Build the typoscript
        return <<<TS
		# Register template for {$configurator->getSignature()}
		$type.tx_{$configurator->getSignature()} {
			view {
				templateRootPaths {
					{$pathHelper($configurator->getTemplateRootPaths())}
				}
		
				partialRootPaths {
					{$pathHelper($configurator->getPartialRootPaths())}
				}
		
				layoutRootPaths {
					{$pathHelper($configurator->getLayoutRootPaths())}
				}
			}
		}
TS;
    }
}
