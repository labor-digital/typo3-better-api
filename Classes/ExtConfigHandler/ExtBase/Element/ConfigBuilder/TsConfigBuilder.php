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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\IconBuilder;

class TsConfigBuilder
{
    /**
     * Generates the ts config definition for the registration in the new content element wizard
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     * @param   string                                                                          $signature
     * @param   string                                                                          $defValues
     *
     * @return string|null
     */
    public static function buildNewCeWizardConfig(
        AbstractElementConfigurator $configurator,
        ExtConfigContext $context,
        string $signature,
        string $defValues
    ): ?string
    {
        if ($configurator->getWizardTab() === false) {
            return null;
        }
        
        $header = ! empty($configurator->getWizardTabLabel())
            ? 'header = ' . $configurator->getWizardTabLabel() : '';
        
        return 'mod.wizards.newContentElement.wizardItems.' . $configurator->getWizardTab() . ' {
			' . $header . '
			elements {
				' . $signature . ' {
					iconIdentifier = ' . IconBuilder::buildIconIdentifier($configurator, $context) . '
					title = ' . $configurator->getTitle() . '
					description = ' . $configurator->getDescription() . '
					tt_content_defValues {
						' . $defValues . '
					}
				}
			}
			show := addToList(' . $signature . ')
		}';
    }
}