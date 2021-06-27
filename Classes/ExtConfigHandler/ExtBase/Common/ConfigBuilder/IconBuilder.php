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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractConfigurator;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

class IconBuilder
{
    /**
     * Generates a new set of arguments that have to be used to register the icon in the icon registry
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                            $context
     *
     * @return array|null
     */
    public static function buildIconRegistrationArgs(
        AbstractConfigurator $configurator,
        ExtConfigContext $context
    ): ?array
    {
        if (empty($configurator->getIcon())) {
            return null;
        }
        
        $iconExtension = strtolower(pathinfo($configurator->getIcon(), PATHINFO_EXTENSION));
        
        return array_values([
            'identifier' => static::buildIconIdentifier($configurator, $context),
            'iconProviderClassName' => $iconExtension === 'svg' ? SvgIconProvider::class : BitmapIconProvider::class,
            'options' => ['source' => $configurator->getIcon()],
        ]);
    }
    
    /**
     * Create the icon identifier for this plugin
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                   $context
     *
     * @return string
     */
    public static function buildIconIdentifier(AbstractConfigurator $configurator, ExtConfigContext $context): string
    {
        return Inflector::toDashed($context->getExtKey() . '-' . $configurator->getPluginName());
    }
}