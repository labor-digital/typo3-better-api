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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement;


use LaborDigital\T3ba\ExtConfig\ExtConfigContext;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\FluidTemplateBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\ConfigBuilder\IconBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\AbstractConfigGenerator;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\BackendPreviewBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\ConfigurePluginBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\TsBuilder;
use LaborDigital\T3ba\ExtConfigHandler\ExtBase\Element\ConfigBuilder\TsConfigBuilder;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class ConfigGenerator extends AbstractConfigGenerator
{
    /**
     * @inheritDoc
     */
    public function generateForVariant(AbstractElementConfigurator $configurator, ExtConfigContext $context, ?string $variantName): void
    {
        /** @var \LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement\ContentElementConfigurator $configurator */
        
        $targetSignature = $configurator->getReplacementSignature() ?? $configurator->getSignature();
        $isReplacement = $targetSignature !== $configurator->getSignature();
        
        $config = $this->config;
        
        $config->typoScript[] = FluidTemplateBuilder::build('plugin', $targetSignature, $configurator);
        $config->typoScript[] = TsBuilder::build($targetSignature, $configurator);
        $config->configureArgs[] = ConfigurePluginBuilder::build($configurator, $context, ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT);
        
        $this->handleBackendPreview($configurator, $targetSignature, $isReplacement);
        $this->handleDataHooks($configurator, $targetSignature, $isReplacement);
        
        if (! $isReplacement) {
            $config->iconArgs[] = IconBuilder::buildIconRegistrationArgs($configurator, $context);
            $config->registrationArgs['ce'][] = $this->buildRegistrationArgs($configurator, $context);
            $config->tsConfig[] = TsConfigBuilder::buildNewCeWizardConfig($configurator, $context, $targetSignature, 'CType = ' . $targetSignature);
            
        } else {
            $config->typoScript[] = 'tt_content.' . $targetSignature . ' < tt_content.' . $configurator->getSignature() . PHP_EOL .
                                    'tt_content.' . $configurator->getSignature() . ' >';
        }
        
        if ($configurator->getContentObject()) {
            $config->typoScript[] = 'tt_content.' . $targetSignature . ' = ' . $configurator->getContentObject();
        }
        
        $config->variantMap[$targetSignature] = $variantName;
    }
    
    /**
     * Generates the arguments for the registration in the cType list
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement\ContentElementConfigurator  $configurator
     * @param   \LaborDigital\T3ba\ExtConfig\ExtConfigContext                                          $context
     *
     * @return array
     * @see \LaborDigital\T3ba\Core\Util\CTypeRegistrationTrait::registerCTypesForElements()
     */
    protected function buildRegistrationArgs(ContentElementConfigurator $configurator, ExtConfigContext $context): array
    {
        return array_values([
            'sectionLabel' => empty($configurator->getCTypeSection()) ?
                Inflector::toHuman($context->getExtKey()) : $configurator->getCTypeSection(),
            'title' => $configurator->getTitle(),
            'signature' => $configurator->getSignature(),
            'icon' => IconBuilder::buildIconIdentifier($configurator, $context),
        ]);
    }
    
    /**
     * Special handler for the registration of backend preview renderers, because we have to modify the renderer constraints
     * if we replace another element
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\ContentElement\ContentElementConfigurator  $configurator
     * @param   string                                                                                 $signature
     * @param   bool                                                                                   $isReplacement
     */
    protected function handleBackendPreview(ContentElementConfigurator $configurator, string $signature, bool $isReplacement): void
    {
        $renderers = BackendPreviewBuilder::buildRendererList($configurator);
        
        if ($isReplacement) {
            // When we replace another element we have to update the CType definition of the renderer constraints
            foreach ($renderers as &$rendererList) {
                foreach ($rendererList as &$def) {
                    if (isset($def[1]['CType'])) {
                        $def[1]['CType'] = $signature;
                    }
                }
            }
            unset($def, $rendererList);
        }
        
        $this->config->backendPreviewRenderers
            = BackendPreviewBuilder::mergeRendererList($this->config->backendPreviewRenderers, $renderers);
        if (! empty($renderers['preview'])) {
            $this->config->backendPreviewHooks
                = BackendPreviewBuilder::addHookToList($this->config->backendPreviewHooks, $signature);
            
            // Store the description for the backend preview renderer, so we can show it
            // even if the wizard tab is disabled
            $this->config->additionalPreviewDescriptions
                = BackendPreviewBuilder::saveDescriptionIfNeeded(
                $this->config->additionalPreviewDescriptions,
                $configurator, ['CType' => $signature]
            );
        }
    }
    
    /**
     * Similar to handleBackendPreview but handles the registration of data hooks, which have the same problem with replacing elements
     *
     * @param   \LaborDigital\T3ba\ExtConfigHandler\ExtBase\Common\AbstractElementConfigurator  $configurator
     * @param   string                                                                          $signature
     * @param   bool                                                                            $isReplacement
     */
    protected function handleDataHooks(AbstractElementConfigurator $configurator, string $signature, bool $isReplacement): void
    {
        $hooks = $configurator->getRegisteredDataHooks();
        
        if (empty($hooks)) {
            return;
        }
        
        if ($isReplacement) {
            // We have the same problem here, that we have in handleBackendPreview().
            // Overwritten elements break the data hook constraints so we have to update them here
            foreach ($hooks as &$hookTypeList) {
                foreach ($hookTypeList as &$def) {
                    if (isset($def[1]['constraints']['CType'])) {
                        $def[1]['constraints']['CType'] = $signature;
                    }
                }
            }
            unset($hookTypeList, $def);
        }
        
        $this->config->dataHooks = array_merge($this->config->dataHooks, $hooks);
    }
}
