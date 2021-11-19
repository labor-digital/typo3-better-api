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


namespace LaborDigital\T3ba\ExtConfigHandler\ExtBase;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Core\Util\CTypeRegistrationTrait;
use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Core\ExtTablesLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaCompletelyLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\Tool\DataHook\DataHookTypes;
use LaborDigital\T3ba\Tool\OddsAndEnds\SerializerUtil;
use Neunerlei\Arrays\Arrays;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

class Applier extends AbstractExtConfigApplier
{
    use ContainerAwareTrait;
    use CTypeRegistrationTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(TcaCompletelyLoadedEvent::class, 'onTcaCompletelyLoaded');
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtLocalConfLoaded');
        $subscription->subscribe(ExtTablesLoadedEvent::class, 'onExtTablesLoaded');
    }
    
    public function onExtTablesLoaded(): void
    {
        $this->registerModules();
        $this->registerElementIcons();
    }
    
    public function onTcaCompletelyLoaded(): void
    {
        $this->registerElements();
        $this->registerElementDataHooks();
        $this->registerElementBackendPreviewHooks();
        $this->registerElementFlexForms();
    }
    
    public function onExtLocalConfLoaded(): void
    {
        $this->configurePlugins();
    }
    
    /**
     * Registers the configured ext base modules in the backend
     */
    protected function registerModules(): void
    {
        $argDefinition = $this->state->get('typo.extBase.module.args');
        if (! empty($argDefinition)) {
            foreach (SerializerUtil::unserializeJson($argDefinition) ?? [] as $args) {
                ExtensionUtility::registerModule(...$args);
            }
        }
    }
    
    /**
     * Registers the plugin icons into the icon registry
     *
     * @deprecated will be removed in v11, the icons will now be registered through the icon option
     */
    protected function registerElementIcons(): void
    {
        $argDefinition = $this->state->get('typo.extBase.element.iconArgs');
        if (! empty($argDefinition)) {
            $iconRegistry = $this->getService(IconRegistry::class);
            foreach (SerializerUtil::unserializeJson($argDefinition) ?? [] as $args) {
                $iconRegistry->registerIcon(...$args);
            }
        }
    }
    
    /**
     * Registers the configured data hooks in the tt_content table by merging them into the table's TCA
     */
    protected function registerElementDataHooks(): void
    {
        $hookDefinition = $this->state->get('typo.extBase.element.dataHooks');
        if (! empty($hookDefinition)) {
            $dataHooks = SerializerUtil::unserializeJson($hookDefinition) ?? [];
            $GLOBALS['TCA'] = Arrays::merge($GLOBALS['TCA'], [
                'tt_content' => [
                    DataHookTypes::TCA_DATA_HOOK_KEY => $dataHooks,
                ],
            ], 'noNumericMerge');
        }
    }
    
    /**
     * Registers the backend preview rendering hooks into the tca
     */
    protected function registerElementBackendPreviewHooks(): void
    {
        $hookDefinition = $this->state->get('typo.extBase.element.backendPreviewHooks');
        if (! empty($hookDefinition)) {
            $hooks = SerializerUtil::unserializeJson($hookDefinition) ?? [];
            $GLOBALS['TCA'] = Arrays::merge($GLOBALS['TCA'], [
                'tt_content' => $hooks,
            ], 'noNumericMerge');
        }
    }
    
    /**
     * Performs the "configurePlugins" loop when the ext local conf files are loaded
     */
    protected function configurePlugins(): void
    {
        $argDefinition = $this->state->get('typo.extBase.element.configureArgs');
        if (is_array($argDefinition)) {
            foreach ($argDefinition as $args) {
                ExtensionUtility::configurePlugin(...$args);
            }
        }
    }
    
    /**
     * Registers the plugins / content elements in the TYPO3 backend
     */
    protected function registerElements(): void
    {
        // Register the plugins in the TYPO3 api
        $argDefinition = $this->state->get('typo.extBase.element.args');
        if (! empty($argDefinition)) {
            $args = SerializerUtil::unserializeJson($argDefinition) ?? [];
            
            // Plugin registration
            if (is_array($args['plugin'])) {
                foreach ($args['plugin'] as $pluginArgs) {
                    ExtensionUtility::registerPlugin(...$pluginArgs);
                }
            }
            
            // Content element registration
            if (is_array($args['ce'])) {
                $this->registerCTypesForElements($GLOBALS['TCA'], $args['ce']);
            }
        }
    }
    
    /**
     * Adds the registered flex form configuration to the TCA
     */
    protected function registerElementFlexForms(): void
    {
        $definition = $this->state->get('typo.extBase.element.flexForms');
        if (! empty($definition)) {
            foreach (SerializerUtil::unserializeJson($definition) ?? [] as $def) {
                ExtensionManagementUtility::addPiFlexFormValue(...$def['args']);
                
                $signature = $def['signature'];
                $val = $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$signature] ?? null;
                if ($val !== null) {
                    if (is_string($val) && stripos($val, 'pi_flexform') === false) {
                        // A string exists, but pi_flexform is not part of it
                        $val = rtrim($val, ', ') . ',pi_flexform';
                    } else {
                        continue;
                    }
                } else {
                    $val = 'pi_flexform';
                }
                $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$signature] = $val;
            }
        }
    }
    
}
