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
 * Last modified: 2021.04.29 at 22:17
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigGenerator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ConfigGenerator extends AbstractElementConfigGenerator
{
    protected function getExtensionUtilityType(): string
    {
        return ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT;
    }
    
    protected function setRegistrationArgs(
        array &$list,
        string $extensionName,
        ExtConfigContext $context,
        AbstractElementConfigurator $configurator
    ): void
    {
        if (! $configurator instanceof ContentElementConfigurator) {
            return;
        }
        
        $list['ce'][] = array_values([
            'sectionLabel' => empty($configurator->getCTypeSection()) ?
                Inflector::toHuman($context->getExtKey()) : $configurator->getCTypeSection(),
            'title' => $configurator->getTitle(),
            'signature' => $configurator->getSignature(),
            'icon' => $this->makeIconIdentifier($configurator, $context),
        ]);
    }
    
    protected function getCeWizardValues(string $signature): string
    {
        return 'CType = ' . $signature;
    }
    
    protected function setPreviewHooks(array &$list, string $signature, string $class): void
    {
        $list[$signature]['previewRenderer'] = $class;
    }
    
    protected function getFlexFormCType(string $signature): string
    {
        return $signature;
    }
}
