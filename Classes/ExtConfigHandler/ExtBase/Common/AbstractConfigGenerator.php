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
 * Last modified: 2020.03.18 at 19:37
 */

namespace LaborDigital\T3BA\ExtConfigHandler\ExtBase\Common;

use Iterator;
use LaborDigital\T3BA\ExtConfig\ConfigStateUtilTrait;
use LaborDigital\T3BA\ExtConfig\ExtConfigContext;

abstract class AbstractConfigGenerator
{
    use ConfigStateUtilTrait;

    /**
     * Internal helper to build the typoScript, template definition for a extbase plugin/module
     *
     * @param   string                $type
     * @param   AbstractConfigurator  $configurator
     *
     * @return string
     */
    protected function registerTemplateDefinition(
        string $type,
        AbstractConfigurator $configurator,
        ExtConfigContext $context
    ): void {
        // Template path helper
        $pathHelper = static function (Iterator $stack): string {
            $paths = [];
            foreach ($stack as $k => $path) {
                $paths[$path] = (((int)$k) * 10 + 10) . ' = ' . $path;
            }

            return implode(PHP_EOL . '					', array_reverse($paths));
        };

        // Build the typoScript
        $signature = $configurator->getSignature();
        static::attachToStringValue($context->getState(),
            'typo.typoScript.dynamicTypoScript.extBaseTemplates\.setup', '
		# Register template for ' . $signature . '
		' . $type . '.tx_' . $signature . ' {
			view {
				templateRootPaths {
					' . $pathHelper($configurator->getTemplateRootPaths()) . '
				}

				partialRootPaths {
					' . $pathHelper($configurator->getPartialRootPaths()) . '
				}

				layoutRootPaths {
					' . $pathHelper($configurator->getLayoutRootPaths()) . '
				}
			}
		}
');
    }

}
