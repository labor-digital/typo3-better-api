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
 * Last modified: 2021.06.13 at 19:59
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder;


use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractConfigurator;

class FluidTemplateBuilder
{
    /**
     * Renders the fluid template paths definition for the given configurator object
     *
     * @param   string                $type
     * @param   string                $signature
     * @param   AbstractConfigurator  $configurator
     *
     * @return string
     */
    public static function build(string $type, string $signature, AbstractConfigurator $configurator): string
    {
        return '# Register template for ' . $signature . '
		' . $type . '.tx_' . $signature . ' {
			view {
				templateRootPaths {
					' . static::renderPathStack($configurator->getTemplateRootPaths()) . '
				}

				partialRootPaths {
					' . static::renderPathStack($configurator->getPartialRootPaths()) . '
				}

				layoutRootPaths {
					' . static::renderPathStack($configurator->getLayoutRootPaths()) . '
				}
			}
		}';
    }
    
    /**
     * Internal helper to render the list of template paths in the given stack
     *
     * @param   iterable  $stack
     *
     * @return string
     */
    protected static function renderPathStack(iterable $stack): string
    {
        $paths = [];
        foreach ($stack as $k => $path) {
            $paths[$path] = (((int)$k) * 10 + 10) . ' = ' . $path;
        }
        
        return implode(PHP_EOL . '					', array_reverse($paths));
    }
}