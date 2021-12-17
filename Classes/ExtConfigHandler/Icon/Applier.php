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
 * Last modified: 2021.11.18 at 13:28
 */

declare(strict_types=1);


namespace LaborDigital\T3ba\ExtConfigHandler\Icon;


use LaborDigital\T3ba\Core\Di\ContainerAwareTrait;
use LaborDigital\T3ba\Event\Core\ExtLocalConfLoadedEvent;
use LaborDigital\T3ba\Event\Core\TcaWithoutOverridesLoadedEvent;
use LaborDigital\T3ba\ExtConfig\Abstracts\AbstractExtConfigApplier;
use LaborDigital\T3ba\Tool\OddsAndEnds\NamingUtil;
use Neunerlei\EventBus\Subscription\EventSubscriptionInterface;
use Neunerlei\Inflection\Inflector;
use TYPO3\CMS\Core\Imaging\IconRegistry;

class Applier extends AbstractExtConfigApplier
{
    use ContainerAwareTrait;
    
    /**
     * @inheritDoc
     */
    public static function subscribeToEvents(EventSubscriptionInterface $subscription): void
    {
        $subscription->subscribe(ExtLocalConfLoadedEvent::class, 'onExtConfigLoaded');
        $subscription->subscribe(TcaWithoutOverridesLoadedEvent::class, 'onTcaLoad', ['priority' => -500]);
    }
    
    public function onExtConfigLoaded(): void
    {
        $this->registerIcons();
        $this->registerAliases();
    }
    
    public function onTcaLoad(): void
    {
        $this->registerPagesModuleIcons();
    }
    
    protected function registerIcons(): void
    {
        $iconRegistrationArgs = $this->state->get('typo.icon.icons', []);
        if (! empty($iconRegistrationArgs) && is_array($iconRegistrationArgs)) {
            $iconRegistry = $this->getService(IconRegistry::class);
            foreach ($iconRegistrationArgs as $args) {
                $iconRegistry->registerIcon(...$args);
            }
        }
    }
    
    protected function registerAliases(): void
    {
        $iconAliasArgs = $this->state->get('typo.icon.aliases', []);
        if (! empty($iconAliasArgs) && is_array($iconAliasArgs)) {
            $iconRegistry = $this->getService(IconRegistry::class);
            foreach ($iconAliasArgs as $identifier => $args) {
                if ($iconRegistry->isRegistered($identifier)) {
                    $iconRegistry->registerAlias(...$args);
                }
            }
        }
    }
    
    protected function registerPagesModuleIcons(): void
    {
        $moduleIcons = $this->state->get('typo.icon.pages.module', []);
        if (! empty($moduleIcons) && is_array($moduleIcons)) {
            foreach ($moduleIcons as $args) {
                [$tableNameOrType, $identifier, $label] = $args;
                
                $tableNameOrType = NamingUtil::resolveTableName($tableNameOrType);
                $label = $label
                         ?? $GLOBALS['TCA'][$tableNameOrType]['ctrl']['title']
                            ?? Inflector::toHuman($tableNameOrType);
                
                $GLOBALS['TCA']['pages']['columns']['module']['config']['items'][] = [
                    $label,
                    $tableNameOrType,
                    $identifier,
                ];
                
                $GLOBALS['TCA']['pages']['ctrl']['typeicon_classes']['contains-' . $tableNameOrType] = $identifier;
            }
        }
    }
    
}